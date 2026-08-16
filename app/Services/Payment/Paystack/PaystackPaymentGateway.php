<?php

namespace App\Services\Payment\Paystack;

use App\Enums\TrxStatus;
use App\Models\PaymentGateway;
use App\Models\Transaction as TransactionModel;
use App\Services\Payment\PaymentGateway as PaymentGatewayInterface;
use App\Services\TransactionService;
use App\Support\WithdrawFieldNormalizer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;
use Transaction;

class PaystackPaymentGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api.paystack.co';

    /**
     * @var array<string, mixed>
     */
    protected array $credentials;

    private string $secretKey;

    private ?string $merchantEmail;

    public function __construct()
    {
        $this->credentials   = PaymentGateway::getCredentials('paystack');
        $this->secretKey     = $this->credential('secret_key');
        $this->merchantEmail = $this->nullableCredential('merchant_email');

        if ($this->secretKey === '') {
            throw new Exception('Paystack secret key is not configured.');
        }
    }

    public function deposit($amount, $currency, $trxId)
    {
        if ($this->merchantEmail === null) {
            throw new Exception('Paystack merchant email is not configured.');
        }

        $reference = $this->reference($trxId, 'dep');
        $payload   = [
            'email'        => $this->merchantEmail,
            'amount'       => $this->amountToSubunit((float) $amount),
            'currency'     => strtoupper((string) $currency),
            'reference'    => $reference,
            'callback_url' => route('status.callback', ['gateway' => 'paystack', 'trx' => $trxId]),
            'metadata'     => [
                'order_id' => $trxId,
            ],
        ];

        $payment = $this->post('/transaction/initialize', $payload);
        $url     = data_get($payment, 'data.authorization_url');

        if (! is_string($url) || $url === '') {
            Log::error('Paystack: invalid initialize response', [
                'transaction_id' => $trxId,
                'response'       => $this->redactForLog($payment),
            ]);

            throw new Exception('Failed to initiate Paystack payment.');
        }

        $this->mergeTransactionData($trxId, [
            'paystack_initialize' => data_get($payment, 'data', []),
        ], $reference);

        return $url;
    }

    /**
     * @param  array<string, mixed>|string|null $withdrawCredential
     * @return array<string, mixed>
     */
    public function withdraw($amount, $currency, $trxId, $withdrawCredential): array
    {
        $currency    = strtoupper((string) $currency);
        $credentials = is_array($withdrawCredential)
            ? $withdrawCredential
            : WithdrawFieldNormalizer::values($withdrawCredential);

        $accountName   = $this->firstCredentialValue($credentials, ['account_name', 'name', 'recipient_name']);
        $accountNumber = $this->firstCredentialValue($credentials, ['account_number', 'account', 'phone_number', 'mobile_number']);
        $bankCode      = $this->firstCredentialValue($credentials, ['bank_code', 'bank']);
        $recipientType = $this->firstCredentialValue($credentials, ['recipient_type', 'type']) ?: $this->defaultRecipientType($currency);

        $this->assertWithdrawCredential($accountName, 'account_name');
        $this->assertWithdrawCredential($accountNumber, 'account_number');
        $this->assertWithdrawCredential($bankCode, 'bank_code');

        $recipient = $this->post('/transferrecipient', [
            'type'           => $recipientType,
            'name'           => $accountName,
            'account_number' => $accountNumber,
            'bank_code'      => $bankCode,
            'currency'       => $currency,
            'metadata'       => [
                'digikash_trx_id' => $trxId,
            ],
        ]);

        $recipientCode = data_get($recipient, 'data.recipient_code');

        if (! is_string($recipientCode) || $recipientCode === '') {
            Log::error('Paystack: transfer recipient response missing recipient code', [
                'transaction_id' => $trxId,
                'response'       => $this->redactForLog($recipient),
            ]);

            throw new Exception('Paystack transfer recipient could not be created.');
        }

        $reference = $this->reference($trxId, 'wd');
        $transfer  = $this->post('/transfer', [
            'source'    => 'balance',
            'amount'    => $this->amountToSubunit((float) $amount),
            'recipient' => $recipientCode,
            'reference' => $reference,
            'reason'    => 'DigiKash withdrawal '.$trxId,
            'currency'  => $currency,
        ]);

        $status = strtolower((string) data_get($transfer, 'data.status', 'pending'));

        return [
            'reference' => $reference,
            'status'    => $status,
            'recipient' => data_get($recipient, 'data', []),
            'transfer'  => data_get($transfer, 'data', []),
            'message'   => data_get($transfer, 'message'),
        ];
    }

    public function handleIPN(Request $request): JsonResponse
    {
        try {
            if (! $this->verifyWebhookSignature($request)) {
                Log::warning('Paystack webhook signature verification failed');

                return response()->json(['error' => 'Invalid signature'], 400);
            }

            $payload = $this->payloadFromRequest($request);
            $event   = (string) data_get($payload, 'event', '');

            return match ($event) {
                'charge.success' => $this->handleChargeSuccess($payload),
                'transfer.success',
                'transfer.failed',
                'transfer.reversed' => $this->handleTransferEvent($event, $payload),
                default             => response()->json(['status' => 'ignored']),
            };
        } catch (Throwable $e) {
            Log::error('Paystack webhook processing failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleChargeSuccess(array $payload): JsonResponse
    {
        $reference = (string) data_get($payload, 'data.reference', '');

        if ($reference === '') {
            Log::info('Paystack charge webhook ignored because reference is missing');

            return response()->json(['status' => 'ignored']);
        }

        $payment = $this->get('/transaction/verify/'.$reference);
        $trxId   = data_get($payment, 'data.metadata.order_id') ?: data_get($payload, 'data.metadata.order_id');

        if (! is_string($trxId) || $trxId === '') {
            Log::info('Paystack charge webhook ignored because transaction id is missing', [
                'reference' => $reference,
            ]);

            return response()->json(['status' => 'ignored']);
        }

        $transaction = app(TransactionService::class)->findTransaction($trxId);

        if (! $transaction || $transaction->status !== TrxStatus::PENDING) {
            return response()->json(['status' => 'success']);
        }

        $this->mergeTransactionData($trxId, [
            'paystack_verify' => data_get($payment, 'data', []),
        ], $reference);

        if (data_get($payment, 'data.status') === 'success') {
            Transaction::completeTransaction($trxId);
        } else {
            Transaction::failTransaction($trxId, (string) data_get($payment, 'data.gateway_response', 'Paystack payment failed'));
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function handleTransferEvent(string $event, array $payload): JsonResponse
    {
        $reference = (string) data_get($payload, 'data.reference', '');

        if ($reference === '') {
            Log::info('Paystack transfer webhook ignored because reference is missing', ['event' => $event]);

            return response()->json(['status' => 'ignored']);
        }

        $transaction = $this->findTransferTransaction($reference);

        if (! $transaction) {
            Log::info('Paystack transfer webhook ignored because transaction was not found', [
                'event'     => $event,
                'reference' => $reference,
            ]);

            return response()->json(['status' => 'success']);
        }

        $this->mergeTransactionData($transaction->trx_id, [
            'paystack_webhook' => $payload,
        ], $reference);

        $transaction->refresh();

        if ($event === 'transfer.success') {
            if ($transaction->status === TrxStatus::PENDING) {
                Transaction::completeTransaction($transaction->trx_id, 'Paystack transfer completed.');
            }

            return response()->json(['status' => 'success']);
        }

        if ($transaction->status !== TrxStatus::CANCELED && $transaction->status !== TrxStatus::FAILED) {
            Transaction::cancelTransaction(
                $transaction->trx_id,
                'Paystack transfer failed or was reversed.',
                true
            );
        }

        return response()->json(['status' => 'success']);
    }

    private function verifyWebhookSignature(Request $request): bool
    {
        $signature = (string) $request->header('x-paystack-signature', '');

        if ($signature === '') {
            return false;
        }

        $expected = hash_hmac('sha512', (string) $request->getContent(), $this->secretKey);

        return hash_equals($expected, $signature);
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromRequest(Request $request): array
    {
        $payload = $request->json()->all();

        if (is_array($payload) && $payload !== []) {
            return $payload;
        }

        $decoded = json_decode((string) $request->getContent(), true);

        return is_array($decoded) ? $decoded : $request->all();
    }

    private function findTransferTransaction(string $reference): ?TransactionModel
    {
        return TransactionModel::query()
            ->where(function ($query) use ($reference) {
                $query->where('trx_reference', $reference)
                    ->orWhere('trx_data->paystack_withdraw->reference', $reference)
                    ->orWhere('trx_data->paystack_withdraw->transfer->reference', $reference);
            })
            ->latest('id')
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mergeTransactionData(string $trxId, array $data, ?string $reference = null): void
    {
        $transaction = TransactionModel::where('trx_id', $trxId)->first();

        if (! $transaction) {
            return;
        }

        $updates = [
            'trx_data' => array_merge($transaction->trx_data ?? [], $data),
        ];

        if ($reference !== null && $reference !== '') {
            $updates['trx_reference'] = $reference;
        }

        $transaction->update($updates);
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function post(string $path, array $payload = []): array
    {
        return $this->request('POST', $path, $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $payload = []): array
    {
        $url = self::BASE_URL.$path;

        try {
            $http = Http::withToken($this->secretKey)
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->connectTimeout(10);

            $response = $method === 'GET'
                ? $http->get($url)
                : $http->post($url, $payload);
        } catch (Throwable $e) {
            Log::error('Paystack transport error', [
                'method' => $method,
                'path'   => $path,
                'error'  => $e->getMessage(),
            ]);

            throw new Exception('Paystack request failed: '.$e->getMessage(), previous: $e);
        }

        $json = $response->json();

        if (! is_array($json)) {
            $json = ['raw' => $response->body()];
        }

        if ($response->failed() || ($json['status'] ?? true) === false) {
            $message = $this->extractErrorMessage($json) ?? 'Paystack API error';

            Log::warning('Paystack API failure', [
                'method'  => $method,
                'path'    => $path,
                'status'  => $response->status(),
                'message' => $message,
                'body'    => $this->redactForLog($json),
            ]);

            throw new Exception($message);
        }

        return $json;
    }

    private function amountToSubunit(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function reference(string $trxId, string $prefix): string
    {
        $reference = strtolower($prefix.'_'.$trxId);
        $reference = preg_replace('/[^a-z0-9_-]/', '_', $reference) ?: strtolower($prefix.'_'.Str::random(16));

        if (strlen($reference) < 16) {
            $reference .= '_'.strtolower(Str::random(16 - strlen($reference)));
        }

        return substr($reference, 0, 50);
    }

    /**
     * @param array<string, mixed> $credentials
     * @param array<int, string>   $keys
     */
    private function firstCredentialValue(array $credentials, array $keys): ?string
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

    private function assertWithdrawCredential(?string $value, string $field): void
    {
        if ($value === null || $value === '') {
            throw new Exception('Paystack withdrawal credential is missing: '.$field);
        }
    }

    private function defaultRecipientType(string $currency): string
    {
        return match ($currency) {
            'GHS'   => 'ghipss',
            'ZAR'   => 'basa',
            default => 'nuban',
        };
    }

    private function credential(string $key): string
    {
        $value = $this->nullableCredential($key);

        if ($value === null || $this->isPlaceholderValue($value)) {
            return '';
        }

        return $value;
    }

    private function nullableCredential(string $key): ?string
    {
        if (! isset($this->credentials[$key]) || ! is_scalar($this->credentials[$key])) {
            return null;
        }

        $value = trim((string) $this->credentials[$key]);

        return $value !== '' ? $value : null;
    }

    private function isPlaceholderValue(string $value): bool
    {
        $normalized = strtolower(trim($value));

        return in_array($normalized, [
            'secret_key',
            'your_secret_key',
            'paystack_secret_key',
            'sk_test_xxx',
        ], true);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractErrorMessage(array $payload): ?string
    {
        foreach (['message', 'error', 'reason', 'detail'] as $key) {
            $value = $payload[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        $data = $payload['data'] ?? null;

        return is_array($data) ? $this->extractErrorMessage($data) : null;
    }

    private function redactForLog(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $redacted = [];

        foreach ($value as $key => $item) {
            $keyString      = is_string($key) ? strtolower($key) : (string) $key;
            $redacted[$key] = str_contains($keyString, 'secret')
                || str_contains($keyString, 'token')
                || str_contains($keyString, 'authorization')
                || str_contains($keyString, 'account_number')
                    ? '[redacted]'
                    : $this->redactForLog($item);
        }

        return $redacted;
    }
}
