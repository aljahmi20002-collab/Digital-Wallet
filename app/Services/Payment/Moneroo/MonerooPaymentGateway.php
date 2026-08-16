<?php

namespace App\Services\Payment\Moneroo;

use App\Services\Payment\PaymentGateway;
use App\Support\WithdrawFieldNormalizer;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class MonerooPaymentGateway implements PaymentGateway
{
    /** @var array */
    protected $credentials;

    public function __construct()
    {
        $this->credentials = \App\Models\PaymentGateway::getCredentials('moneroo');

        // Ensure your Moneroo keys are present; throw if missing
        if (empty($this->credentials['api_key']) || empty($this->credentials['api_secret'])) {
            throw new \RuntimeException('Moneroo API credentials are not configured.');
        }
    }

    /**
     * Initiate a deposit/payment with Moneroo.
     *
     * @return RedirectResponse|string
     */
    public function deposit($amount, $currency, $trxId)
    {
        $url = 'https://api.moneroo.io/v1/payments/initialize';

        $data = [
            'amount'      => $amount,
            'currency'    => $currency,
            'description' => "Deposit for transaction {$trxId}",
            'return_url'  => route('status.callback', ['gateway' => 'moneroo', 'trx' => $trxId]),
            'metadata'    => [
                'trx_id' => $trxId,
                'source' => 'wallet-deposit',
            ],
        ];

        if (auth()->check()) {
            $user             = auth()->user();
            $data['customer'] = [
                'email'      => $user->email,
                'first_name' => $user->first_name ?? '',
                'last_name'  => $user->last_name  ?? '',
            ];
        }

        try {
            // Use secret key for all server‑side calls
            $response = Http::withToken($this->credentials['api_key'])
                ->acceptJson()
                ->post($url, $data);

            if ($response->status() !== 201) {
                Log::error('Moneroo payment init error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                    'trxId'  => $trxId,
                ]);

                return back()->withErrors(['error' => 'Failed to initialize payment with Moneroo.']);
            }

            $checkoutUrl = data_get($response->json(), 'data.checkout_url');
            if (! $checkoutUrl) {
                Log::error('Moneroo payment missing checkout_url', ['response' => $response->json()]);

                return back()->withErrors(['error' => 'Missing checkout URL from Moneroo.']);
            }

            return $checkoutUrl;
        } catch (Throwable $e) {
            Log::error('Moneroo payment init exception', ['message' => $e->getMessage(), 'trxId' => $trxId]);

            return back()->withErrors(['error' => 'An unexpected error occurred while initiating payment.']);
        }
    }

    /**
     * Initiate a payout/withdrawal with Moneroo.
     *
     * @param  array<string, mixed>|string|null $withdrawCredential
     * @return array<string, mixed>
     *
     * @throws Exception
     */
    public function withdraw($amount, $currency, $trxId, $withdrawCredential): array
    {
        $url         = 'https://api.moneroo.io/v1/payouts/initialize';
        $credentials = is_array($withdrawCredential)
            ? $withdrawCredential
            : WithdrawFieldNormalizer::values($withdrawCredential);

        $method    = $this->stringCredential($credentials, 'method');
        $recipient = $this->recipientPayload($credentials);

        if ($method === null) {
            throw new Exception('Moneroo withdrawal credential is missing: method');
        }

        if ($recipient === []) {
            throw new Exception('Moneroo withdrawal recipient details are missing.');
        }

        $data = [
            'amount'      => (int) $amount,
            'currency'    => strtoupper((string) $currency),
            'description' => "Withdrawal for transaction {$trxId}",
            'method'      => $method,
            'recipient'   => $recipient,
            'metadata'    => [
                'trx_id' => $trxId,
                'source' => 'wallet-withdraw',
            ],
        ];

        if (auth()->check()) {
            $user             = auth()->user();
            $data['customer'] = [
                'email'      => $user->email,
                'first_name' => $user->first_name ?? '',
                'last_name'  => $user->last_name  ?? '',
            ];
        }

        try {
            $response = Http::withToken($this->credentials['api_key'])
                ->acceptJson()
                ->post($url, $data);

            $payload = $response->json();
            if (! is_array($payload)) {
                $payload = ['raw' => $response->body()];
            }

            if (! $response->successful()) {
                Log::error('Moneroo payout init error', [
                    'status' => $response->status(),
                    'body'   => $payload,
                    'trxId'  => $trxId,
                ]);

                throw new Exception($this->extractResponseMessage($payload) ?? 'Failed to initialize payout with Moneroo.');
            }

            $payoutId = data_get($payload, 'data.id')
                ?? data_get($payload, 'data.reference')
                ?? data_get($payload, 'id')
                ?? data_get($payload, 'reference');

            if (! is_scalar($payoutId) || trim((string) $payoutId) === '') {
                Log::error('Moneroo payout missing ID', ['response' => $payload]);

                throw new Exception('Missing payout ID from Moneroo.');
            }

            $status = data_get($payload, 'data.status') ?? data_get($payload, 'status') ?? 'pending';

            return [
                'reference' => (string) $payoutId,
                'status'    => strtolower((string) $status),
                'payout_id' => (string) $payoutId,
                'data'      => data_get($payload, 'data', []),
                'raw'       => $payload,
            ];
        } catch (Throwable $e) {
            Log::error('Moneroo payout init exception', ['message' => $e->getMessage(), 'trxId' => $trxId]);

            throw $e instanceof Exception
                ? $e
                : new Exception('An unexpected error occurred while initiating Moneroo payout.', previous: $e);
        }
    }

    /**
     * Handle webhook/IPN notifications from Moneroo.
     *
     * @return Response
     */
    public function handleIPN(Request $request)
    {
        $secret = $this->credentials['api_secret'] ?? null;

        if (! $secret) {
            Log::warning('Moneroo webhook secret missing');
            http_response_code(403);
            exit('Webhook signing secret not configured.');
        }

        // Get raw payload and compute signature
        $payload           = file_get_contents('php://input');
        $signature         = hash_hmac('sha256', $payload, $secret);
        $receivedSignature = $_SERVER['HTTP_X_MONEROO_SIGNATURE'] ?? '';

        // Strict signature verification
        if (! hash_equals($signature, $receivedSignature)) {
            Log::warning('Invalid Moneroo webhook signature', [
                'computed' => $signature,
                'received' => $receivedSignature,
            ]);
            http_response_code(403);
            exit('Invalid webhook signature');
        }

        // Process webhook data
        $webhookData = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON in Moneroo webhook payload');
            http_response_code(400);
            exit('Invalid JSON payload');
        }

        $event         = $webhookData['event'] ?? null;
        $data          = $webhookData['data']  ?? [];
        $transactionId = $data['id']           ?? null;
        $refTrxId      = data_get($data, 'metadata.trx_id');

        // Handle payment and payout events
        switch ($event) {
            case 'payment.success':
                if ($refTrxId && $transactionId && $this->isSuccessStatus(data_get($this->verifyTransaction((string) $transactionId), 'status'))) {
                    \Transaction::completeTransaction($refTrxId);
                }
                break;

            case 'payment.failed':
            case 'payment.cancelled':
                if ($refTrxId) {
                    \Transaction::failTransaction($refTrxId);
                }
                break;

            case 'payout.failed':
                if ($refTrxId) {
                    \Transaction::cancelTransaction($refTrxId, __('Moneroo payout failed.'), true);
                }
                break;

            case 'payout.success':
                if ($refTrxId && $transactionId && $this->isSuccessStatus(data_get($this->verifyPayout((string) $transactionId), 'status'))) {
                    \Transaction::completeTransaction($refTrxId);
                }
                break;

                // Log unhandled events for debugging
            default:
                Log::info('Unhandled Moneroo webhook event', ['event' => $event, 'data' => $data]);
                break;
        }

        // Send successful response
        http_response_code(200);
        exit('OK');
    }

    /**
     * Verify a payment transaction with Moneroo’s verify endpoint.
     */
    protected function verifyTransaction(string $paymentId): ?array
    {
        try {
            $response = Http::withToken($this->credentials['api_key'])
                ->acceptJson()
                ->get("https://api.moneroo.io/v1/payments/{$paymentId}/verify");

            return $response->successful() ? $response->json('data') : null;
        } catch (Throwable $e) {
            Log::error('Moneroo verify transaction exception', [
                'paymentId' => $paymentId,
                'message'   => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verify a payout transaction with Moneroo’s verify endpoint.
     */
    protected function verifyPayout(string $payoutId): ?array
    {
        try {
            $response = Http::withToken($this->credentials['api_key'])
                ->acceptJson()
                ->get("https://api.moneroo.io/v1/payouts/{$payoutId}/verify");

            return $response->successful() ? $response->json('data') : null;
        } catch (Throwable $e) {
            Log::error('Moneroo verify payout exception', [
                'payoutId' => $payoutId,
                'message'  => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed> $credentials
     * @return array<string, mixed>
     */
    private function recipientPayload(array $credentials): array
    {
        $recipient = $credentials['recipient'] ?? null;

        if (is_array($recipient)) {
            return array_filter($recipient, fn (mixed $value): bool => $value !== null && $value !== '');
        }

        if (is_string($recipient) && trim($recipient) !== '') {
            $decoded = json_decode($recipient, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return array_filter($decoded, fn (mixed $value): bool => $value !== null && $value !== '');
            }
        }

        $recipientKey   = $this->stringCredential($credentials, 'recipient_key');
        $recipientValue = $this->stringCredential($credentials, 'recipient_value');

        if ($recipientKey !== null && $recipientValue !== null) {
            return [$recipientKey => $recipientValue];
        }

        $payload = [];

        foreach (['msisdn', 'phone', 'phone_number', 'account_number', 'account_name', 'bank_code', 'email', 'wallet_address'] as $key) {
            $value = $this->stringCredential($credentials, $key);

            if ($value !== null) {
                $payload[$key] = $value;
            }
        }

        return $payload;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    private function stringCredential(array $credentials, string $key): ?string
    {
        if (! isset($credentials[$key]) || ! is_scalar($credentials[$key])) {
            return null;
        }

        $value = trim((string) $credentials[$key]);

        return $value !== '' ? $value : null;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function extractResponseMessage(array $payload): ?string
    {
        foreach (['message', 'error', 'detail', 'reason'] as $key) {
            $value = $payload[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_array($value)) {
                $message = $this->extractResponseMessage($value);

                if ($message !== null) {
                    return $message;
                }
            }
        }

        $data = $payload['data'] ?? null;

        return is_array($data) ? $this->extractResponseMessage($data) : null;
    }

    private function isSuccessStatus(?string $status): bool
    {
        return in_array(strtolower((string) $status), ['success', 'successful', 'completed', 'complete'], true);
    }
}
