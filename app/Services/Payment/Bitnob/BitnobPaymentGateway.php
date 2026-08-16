<?php

declare(strict_types=1);

namespace App\Services\Payment\Bitnob;

use App\Http\Controllers\Webhook\BitnobWebhookController;
use App\Models\PaymentGateway;
use App\Models\Transaction as TransactionModel;
use App\Services\Bitnob\BitnobPayoutService;
use App\Services\Payment\PaymentGateway as PaymentGatewayInterface;
use App\Services\TransactionService;
use App\Support\WithdrawFieldNormalizer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Bitnob deposit / IPN handler.
 *
 * Used by the IPN controller (`POST /ipn/bitnob`) to receive Bitnob's
 * deposit / payout webhook callbacks. Card-flow webhooks are handled
 * separately by `BitnobWebhookController` — this class only deals with
 * the generic transaction lifecycle (deposit, payout, etc.).
 *
 * Mirrors the working voaray reference verbatim: webhook signature is
 * tried as both HMAC-SHA256 and HMAC-SHA512, in hex and base64, against
 * any of the documented Bitnob secret keys.
 */
class BitnobPaymentGateway implements PaymentGatewayInterface
{
    private const SUCCESS_STATUSES = [
        'success',
        'successful',
        'completed',
        'complete',
        'approved',
        'paid',
    ];

    private const FAILED_STATUSES = [
        'failed',
        'fail',
        'declined',
        'canceled',
        'cancelled',
        'reversed',
    ];

    protected array $credentials;

    public function __construct()
    {
        $this->credentials = PaymentGateway::getCredentials('bitnob');
    }

    public function deposit($amount, $currency, $trxId)
    {
        return route('status.pending', ['trx_id' => $trxId]);
    }

    /**
     * @param  array<string, mixed>|string|null $withdrawCredential
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function withdraw($amount, $currency, $trxId, $withdrawCredential): array
    {
        $credentials = is_array($withdrawCredential)
            ? $withdrawCredential
            : WithdrawFieldNormalizer::values($withdrawCredential);

        $destinationType = $this->credentialValue($credentials, ['destination_type']) ?? 'bank';
        $country         = strtoupper((string) ($this->credentialValue($credentials, ['country']) ?? ''));
        $bankCode        = $this->credentialValue($credentials, ['bank_code', 'bank']);
        $accountNumber   = $this->credentialValue($credentials, ['account_number', 'phone_number', 'mobile_number']);
        $accountName     = $this->credentialValue($credentials, ['account_name', 'name', 'recipient_name']);

        $this->assertWithdrawCredential($country, 'country');
        $this->assertWithdrawCredential($bankCode, 'bank_code');
        $this->assertWithdrawCredential($accountNumber, 'account_number');
        $this->assertWithdrawCredential($accountName, 'account_name');

        $beneficiary = array_filter([
            'destination_type' => $destinationType,
            'country'          => $country,
            'account_name'     => $accountName,
            'account_number'   => $accountNumber,
            'bank_code'        => $bankCode,
            'swift_code'       => $this->credentialValue($credentials, ['swift_code']),
        ], fn (mixed $value): bool => $value !== null && $value !== '');

        $response = app(BitnobPayoutService::class)->payout(
            [
                'settlement_amount' => (float) $amount,
                'country'           => $country,
                'to_currency'       => strtoupper((string) $currency),
                'reference'         => $trxId,
            ],
            $beneficiary,
            route('ipn.handle', ['gateway' => 'bitnob']),
            'vendor_payment',
            $trxId
        );

        $status = data_get($response, 'data.status') ?? data_get($response, 'status') ?? 'pending';

        return [
            'reference'   => $trxId,
            'status'      => strtolower((string) $status),
            'beneficiary' => $beneficiary,
            'data'        => data_get($response, 'data', []),
            'raw'         => $response,
        ];
    }

    public function handleIPN(Request $request): JsonResponse
    {
        try {
            if (! $this->verifyWebhookSignature($request)) {
                Log::warning('Bitnob webhook signature verification failed');

                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $payload = $request->json()->all();
            if (! is_array($payload) || $payload === []) {
                $raw     = (string) $request->getContent();
                $decoded = $raw !== '' ? json_decode($raw, true) : null;
                $payload = is_array($decoded) ? $decoded : $request->all();
            }

            // Card events (virtualcard.creation.success, virtualcard.topup.failed, etc.)
            // belong to the dedicated card-webhook handler — forwarding
            // them here means a single Bitnob dashboard webhook URL works
            // for both deposits AND cards. The card handler's event map
            // lives in config('bitnob.webhook_events').
            $event = (string) ($payload['event'] ?? '');
            if (str_starts_with($event, 'virtualcard.') || str_starts_with($event, 'stablecoin.') || str_starts_with($event, 'payout.')) {
                Log::info('Bitnob IPN forwarding card/payout event to BitnobWebhookController', [
                    'event' => $event,
                ]);

                $controller = app(BitnobWebhookController::class);

                return $controller->__invoke($request);
            }

            $reference = $this->extractReference($payload);
            if (! $reference) {
                Log::warning('Bitnob webhook missing reference', [
                    'payload_keys' => is_array($payload) ? array_keys($payload) : null,
                ]);

                return response()->json(['status' => 'ignored']);
            }

            $status = strtolower((string) ($this->extractStatus($payload) ?? ''));
            if ($status === '') {
                Log::info('Bitnob webhook missing status (acknowledged)', ['reference' => $reference]);

                return response()->json(['status' => 'success']);
            }

            $trx = $this->findPendingTransactionByReference($reference);

            if (! $trx) {
                Log::info('Bitnob webhook: no pending transaction found for reference', ['reference' => $reference]);

                return response()->json(['status' => 'success']);
            }

            // Always attach webhook payload for audit/debug.
            $trx->update([
                'trx_data' => array_merge($trx->trx_data ?? [], [
                    'bitnob_webhook' => $payload,
                ]),
            ]);

            if (in_array($status, self::SUCCESS_STATUSES, true)) {
                app(TransactionService::class)->completeTransaction(
                    $trx->trx_id,
                    'Bitnob: '.$status,
                    $trx->description
                );

                return response()->json(['status' => 'success']);
            }

            if (in_array($status, self::FAILED_STATUSES, true)) {
                app(TransactionService::class)->failTransaction(
                    $trx->trx_id,
                    'Bitnob: '.$status,
                    $trx->description
                );

                return response()->json(['status' => 'success']);
            }

            Log::info('Bitnob webhook: unhandled status (acknowledged)', [
                'reference' => $reference,
                'status'    => $status,
            ]);

            return response()->json(['status' => 'success']);
        } catch (\Throwable $e) {
            Log::error('Bitnob webhook error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    protected function findPendingTransactionByReference(string $reference): ?TransactionModel
    {
        return TransactionModel::query()
            ->where('status', 'pending')
            ->where(function ($q) use ($reference) {
                $q->where('trx_reference', $reference)
                    ->orWhere('trx_id', $reference)
                    ->orWhere('trx_data->reference', $reference)
                    ->orWhere('trx_data->data->reference', $reference)
                    ->orWhere('trx_data->bitnob_withdraw->reference', $reference)
                    ->orWhere('trx_data->bitnob_withdraw->data->reference', $reference)
                    ->orWhere('trx_data->bitnob_withdraw->raw->data->reference', $reference)
                    ->orWhere('trx_data->transaction->reference', $reference)
                    ->orWhere('trx_data->data->paymentReference', $reference)
                    ->orWhere('trx_data->data->transactionReference', $reference);
            })
            ->latest('id')
            ->first();
    }

    protected function verifyWebhookSignature(Request $request): bool
    {
        $secret = (string) ($this->credentials['webhook_secret']
            ?? $this->credentials['webhook_signing_secret']
            ?? $this->credentials['signing_secret']
            ?? $this->credentials['webhook_key']
            ?? $this->credentials['webhook_token']
            ?? $this->credentials['secret']
            ?? '');

        // Empty secret means the user hasn't configured webhook signing
        // — accept the call so the IPN flow doesn't break gateway setup.
        if ($this->isBlankSecret($secret)) {
            return true;
        }

        $signature = (string) ($request->header('X-Bitnob-Signature')
            ?? $request->header('x-bitnob-signature')
            ?? $request->header('X-Bitnob-Signature-256')
            ?? $request->header('x-bitnob-signature-256')
            ?? $request->header('X-Bitnob-Signature-512')
            ?? $request->header('x-bitnob-signature-512')
            ?? $request->header('X-Signature')
            ?? $request->header('x-signature')
            ?? '');

        if ($signature === '') {
            return false;
        }

        $payload = (string) $request->getContent();
        $sig     = $this->normalizeSignature(trim($signature));

        // Bitnob may send the HMAC as hex or base64, sha256 or sha512.
        $hashes = [
            hash_hmac('sha256', $payload, $secret),
            hash_hmac('sha512', $payload, $secret),
        ];

        foreach ($hashes as $hex) {
            if (hash_equals($hex, $sig)) {
                return true;
            }

            $binary = hex2bin($hex);
            if ($binary !== false && hash_equals(base64_encode($binary), $sig)) {
                return true;
            }
        }

        Log::warning('Bitnob webhook signature mismatch', [
            'signature_length' => strlen($sig),
            'payload_length'   => strlen($payload),
        ]);

        return false;
    }

    protected function isBlankSecret(string $secret): bool
    {
        $normalized = strtolower(trim($secret));

        return $normalized === ''
            || in_array($normalized, [
                'webhook_secret',
                'webhook_signing_secret',
                'signing_secret',
                'webhook_key',
                'webhook_token',
                'secret',
            ], true);
    }

    protected function normalizeSignature(string $signature): string
    {
        foreach (['sha256=', 'sha512=', 'hmac-sha256=', 'hmac-sha512='] as $prefix) {
            if (str_starts_with(strtolower($signature), $prefix)) {
                return substr($signature, strlen($prefix));
            }
        }

        return $signature;
    }

    protected function extractReference(array $payload): ?string
    {
        $candidates = [
            $payload['data']['reference']                ?? null,
            $payload['data']['paymentReference']         ?? null,
            $payload['data']['transactionReference']     ?? null,
            $payload['data']['customerReference']        ?? null,
            $payload['data']['data']['reference']        ?? null,
            $payload['data']['transaction']['reference'] ?? null,
            $payload['data']['transaction']['id']        ?? null,
            $payload['reference']                        ?? null,
            $payload['paymentReference']                 ?? null,
            $payload['transactionReference']             ?? null,
            $payload['customerReference']                ?? null,
            $payload['id']                               ?? null,
        ];

        foreach ($candidates as $candidate) {
            $reference = $this->stringValue($candidate);
            if ($reference !== null) {
                return $reference;
            }
        }

        return null;
    }

    protected function extractStatus(array $payload): ?string
    {
        $candidates = [
            $payload['data']['status']                ?? null,
            $payload['data']['state']                 ?? null,
            $payload['data']['data']['status']        ?? null,
            $payload['data']['transaction']['status'] ?? null,
            $payload['data']['transaction']['state']  ?? null,
            $payload['status']                        ?? null,
            $payload['state']                         ?? null,
            $payload['event']                         ?? null,
            $payload['type']                          ?? null,
        ];

        foreach ($candidates as $candidate) {
            $status = $this->stringValue($candidate);
            if ($status !== null) {
                return $status;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $credentials
     * @param array<int, string>   $keys
     */
    protected function credentialValue(array $credentials, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($credentials[$key]) && is_scalar($credentials[$key])) {
                $value = trim((string) $credentials[$key]);

                if ($value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @throws Exception
     */
    protected function assertWithdrawCredential(?string $value, string $field): void
    {
        if ($value === null || $value === '') {
            throw new Exception('Bitnob withdrawal credential is missing: '.$field);
        }
    }

    protected function stringValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }
}
