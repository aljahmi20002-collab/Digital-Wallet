<?php

namespace App\Services\Payment\Paymob;

use App\Enums\TrxStatus;
use App\Models\PaymentGateway;
use App\Models\Transaction as TransactionModel;
use App\Services\Payment\PaymentGateway as PaymentGatewayInterface;
use App\Services\TransactionService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use Transaction;

class PaymobPaymentGateway implements PaymentGatewayInterface
{
    private string $apiKey;

    private string $secretKey;

    private string $publicKey;

    /**
     * @var array<int, int|string>
     */
    private array $paymentMethods;

    private string $baseUrl;

    private string $hmacSecret;

    private bool $sandbox;

    public function __construct()
    {
        $credentials = $this->normalizeCredentials(PaymentGateway::getCredentials('paymob'));

        $this->apiKey         = $credentials['api_key'];
        $this->secretKey      = $credentials['secret_key'];
        $this->publicKey      = $credentials['public_key'];
        $this->paymentMethods = $credentials['payment_methods'];
        $this->hmacSecret     = $credentials['hmac'];
        $this->sandbox        = $credentials['sandbox'];
        $this->baseUrl        = rtrim($credentials['base_url'], '/');

        if (
            $this->isPlaceholderValue($this->secretKey)
            || $this->isPlaceholderValue($this->publicKey)
            || $this->paymentMethods === []
        ) {
            throw new Exception('Paymob credentials are not configured. Set secret_key, public_key, and payment_methods.');
        }
    }

    public function deposit($amount, $currency, $trxId)
    {
        $amountCents = $this->toCents($amount);
        $currency    = strtoupper((string) $currency);
        $transaction = TransactionModel::where('trx_id', $trxId)->first();
        $billingData = $this->buildBillingData($transaction?->trx_data ?? []);

        $intention = $this->createIntention(
            amountCents: $amountCents,
            currency: $currency,
            merchantOrderId: (string) $trxId,
            billingData: $billingData
        );

        $clientSecret = (string) ($intention['client_secret'] ?? '');
        if ($clientSecret === '') {
            Log::error('Paymob intention response missing client_secret', [
                'transaction_id' => $trxId,
                'response'       => $this->redactForLog($intention),
            ]);

            throw new Exception('Paymob client secret missing.');
        }

        $this->mergeTransactionData((string) $trxId, [
            'paymob_intention' => $this->redactForStorage($intention),
        ], $clientSecret);

        return $this->checkoutUrl($clientSecret);
    }

    public function handleIPN(Request $request): JsonResponse
    {
        $obj  = $this->extractIpnPayload($request);
        $hmac = $this->extractIpnHmac($request);

        if ($obj === []) {
            Log::warning('Paymob IPN: missing obj payload', ['payload' => $request->all()]);

            return response()->json(['error' => 'Invalid payload'], 400);
        }

        if ($this->hmacSecret !== '') {
            if ($hmac === '') {
                Log::warning('Paymob IPN: missing hmac');

                return response()->json(['error' => 'Missing hmac'], 400);
            }

            if (! $this->verifyHmac($obj, $hmac)) {
                Log::warning('Paymob IPN: invalid hmac', ['obj' => $this->redactForLog($obj)]);

                return response()->json(['error' => 'Invalid hmac'], 401);
            }
        }

        $merchantOrderId = $this->extractMerchantOrderId($obj, $request);

        if ($merchantOrderId === null) {
            Log::warning('Paymob IPN: missing merchant order reference', ['obj' => $this->redactForLog($obj)]);

            return response()->json(['error' => 'Missing merchant_order_id'], 400);
        }

        try {
            $transaction = app(TransactionService::class)->findTransaction($merchantOrderId);

            if (! $transaction) {
                Log::info('Paymob IPN ignored because transaction was not found', [
                    'merchant_order_id' => $merchantOrderId,
                ]);

                return response()->json(['status' => 'ignored']);
            }

            $this->mergeTransactionData($merchantOrderId, [
                'paymob_ipn' => $obj,
            ], $this->extractPaymobReference($obj));

            if ($transaction->status !== TrxStatus::PENDING) {
                return response()->json(['status' => 'success']);
            }

            if ($this->isSuccessfulPayment($obj)) {
                Transaction::completeTransaction($merchantOrderId, 'PAYMOB_PAYMENT_SUCCESS');

                return response()->json(['status' => 'success']);
            }

            Transaction::failTransaction($merchantOrderId, 'PAYMOB_PAYMENT_FAILED');

            return response()->json(['status' => 'failed']);
        } catch (Throwable $e) {
            Log::error('Paymob IPN processing error', [
                'merchant_order_id' => $merchantOrderId,
                'error'             => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extractIpnPayload(Request $request): array
    {
        $payload = $request->input('obj');
        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        $decodedContent = json_decode((string) $request->getContent(), true);
        if (is_array($decodedContent) && isset($decodedContent['obj']) && is_array($decodedContent['obj'])) {
            return $decodedContent['obj'];
        }

        $requestData = $request->all();
        unset($requestData['hmac'], $requestData['gateway']);

        $allowedKeys = [
            'success',
            'pending',
            'is_voided',
            'is_refunded',
            'is_standalone_payment',
            'order',
            'id',
            'amount_cents',
            'currency',
            'created_at',
            'integration_id',
            'merchant_order_id',
            'special_reference',
            'source_data',
            'error_occured',
            'has_parent_transaction',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'owner',
        ];

        return array_intersect_key($requestData, array_flip($allowedKeys));
    }

    private function extractIpnHmac(Request $request): string
    {
        return (string) (
            $request->input('hmac')
            ?: $request->query('hmac')
            ?: $request->header('X-HMAC-SHA256-SIGNATURE')
            ?: $request->header('x-hmac-sha256-signature')
            ?: $request->header('Paymob-Hmac')
            ?: ''
        );
    }

    /**
     * @param  array<string, mixed> $billingData
     * @return array<string, mixed>
     */
    private function createIntention(
        int $amountCents,
        string $currency,
        string $merchantOrderId,
        array $billingData
    ): array {
        $payload = [
            'amount'          => $amountCents,
            'currency'        => $currency,
            'payment_methods' => $this->paymentMethods,
            'items'           => [
                [
                    'name'        => 'Payment '.$merchantOrderId,
                    'amount'      => $amountCents,
                    'description' => 'Payment '.$merchantOrderId,
                    'quantity'    => 1,
                ],
            ],
            'billing_data'      => $billingData,
            'special_reference' => $merchantOrderId,
            'notification_url'  => route('ipn.handle', ['gateway' => 'paymob']),
            'redirection_url'   => route('status.callback', ['gateway' => 'paymob', 'trx' => $merchantOrderId]),
            'expiration'        => 3600,
            'extras'            => [
                'trx_id'  => $merchantOrderId,
                'sandbox' => $this->sandbox,
            ],
        ];

        $response = Http::timeout(30)
            ->connectTimeout(10)
            ->acceptJson()
            ->asJson()
            ->withToken($this->secretKey, 'Token')
            ->post($this->baseUrl.'/v1/intention/', $payload);

        $json = $response->json();

        if (! is_array($json)) {
            $json = ['raw' => $response->body()];
        }

        if ($response->failed()) {
            $errorMessage = $this->extractPaymobError($json, $response->body());
            Log::error('Paymob intention creation failed', [
                'status' => $response->status(),
                'body'   => $this->redactForLog($json),
                'error'  => $errorMessage,
            ]);

            throw new Exception('Paymob intention creation failed. '.$errorMessage);
        }

        return $json;
    }

    /**
     * @param  array<string, mixed>                                                                                                                                   $credentials
     * @return array{api_key: string, secret_key: string, public_key: string, payment_methods: array<int, int|string>, hmac: string, base_url: string, sandbox: bool}
     */
    private function normalizeCredentials(array $credentials): array
    {
        $secretKey      = trim((string) ($credentials['secret_key'] ?? ''));
        $apiKey         = trim((string) ($credentials['api_key'] ?? ''));
        $paymentMethods = $this->normalizePaymentMethods(
            $credentials['payment_methods']
            ?? $credentials['integration_ids']
            ?? $credentials['integration_id']
            ?? $credentials['card_integration_id']
            ?? null
        );

        return [
            'api_key'         => $apiKey,
            'secret_key'      => $secretKey !== '' ? $secretKey : $apiKey,
            'public_key'      => trim((string) ($credentials['public_key'] ?? '')),
            'payment_methods' => $paymentMethods,
            'hmac'            => trim((string) ($credentials['hmac'] ?? $credentials['hmac_secret'] ?? '')),
            'base_url'        => trim((string) ($credentials['base_url'] ?? 'https://ksa.paymob.com')),
            'sandbox'         => filter_var($credentials['sandbox'] ?? false, FILTER_VALIDATE_BOOL),
        ];
    }

    /**
     * @param  array<string, mixed>  $trxData
     * @return array<string, string>
     */
    private function buildBillingData(array $trxData): array
    {
        $user     = auth()->user();
        $fullName = trim((string) ($trxData['customer_name'] ?? $user?->name ?? 'Customer'));
        $email    = trim((string) ($trxData['customer_email'] ?? $user?->email ?? 'customer@example.com'));
        $phone    = $this->normalizePhoneNumber(trim((string) ($trxData['customer_phone'] ?? $user?->phone ?? '0000000000')));

        return [
            'apartment'       => 'NA',
            'email'           => $email !== '' ? $email : 'customer@example.com',
            'floor'           => 'NA',
            'first_name'      => $this->firstName($fullName),
            'street'          => 'NA',
            'building'        => 'NA',
            'phone_number'    => $phone !== '' ? $phone : '0000000000',
            'shipping_method' => 'NA',
            'postal_code'     => 'NA',
            'city'            => 'NA',
            'country'         => 'NA',
            'last_name'       => $this->lastName($fullName),
            'state'           => 'NA',
        ];
    }

    /**
     * @param array<string, mixed> $responseData
     */
    private function extractPaymobError(array $responseData, string $rawBody): string
    {
        if (isset($responseData['detail'])) {
            return (string) $responseData['detail'];
        }

        if (isset($responseData['errors']) && is_array($responseData['errors'])) {
            return $this->flattenValidationErrors($responseData['errors']);
        }

        $fallback = trim($rawBody);

        return $fallback !== '' ? $fallback : 'No additional response message from Paymob.';
    }

    /**
     * @param array<string, mixed> $errors
     */
    private function flattenValidationErrors(array $errors): string
    {
        $parts = [];

        foreach ($errors as $field => $messages) {
            $parts[] = is_array($messages)
                ? $field.': '.implode(', ', array_map('strval', $messages))
                : $field.': '.(string) $messages;
        }

        return implode(' | ', $parts);
    }

    private function normalizePhoneNumber(string $phone): string
    {
        $normalized = preg_replace('/[^0-9]/', '', $phone) ?? '';

        if ($normalized === '') {
            return '0000000000';
        }

        return strlen($normalized) > 15 ? substr($normalized, 0, 15) : $normalized;
    }

    /**
     * @return array<int, int|string>
     */
    private function normalizePaymentMethods(mixed $paymentMethods): array
    {
        if (is_string($paymentMethods)) {
            $paymentMethods = preg_split('/[\s,|]+/', $paymentMethods, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        if (is_numeric($paymentMethods)) {
            $paymentMethods = [$paymentMethods];
        }

        if (! is_array($paymentMethods)) {
            return [];
        }

        return collect($paymentMethods)
            ->map(static fn (mixed $method): int|string => is_numeric($method) ? (int) $method : trim((string) $method))
            ->filter(fn (int|string $method): bool => $method !== '' && $method !== 0 && ! $this->isPlaceholderValue((string) $method))
            ->values()
            ->all();
    }

    private function isPlaceholderValue(string $value): bool
    {
        $normalized = strtolower(trim($value));

        return $normalized === ''
            || str_ends_with($normalized, '_key')
            || str_ends_with($normalized, '_secret')
            || in_array($normalized, [
                'api_key',
                'secret_key',
                'public_key',
                'hmac',
                'hmac_secret',
                'integration_id',
                'integration_ids',
                'payment_methods',
                'card_integration_id',
            ], true);
    }

    private function checkoutUrl(string $clientSecret): string
    {
        return $this->baseUrl.'/unifiedcheckout/?'.http_build_query([
            'publicKey'    => $this->publicKey,
            'clientSecret' => $clientSecret,
        ]);
    }

    /**
     * @param array<string, mixed> $obj
     */
    private function verifyHmac(array $obj, string $hmac): bool
    {
        $fields = [
            'amount_cents',
            'created_at',
            'currency',
            'error_occured',
            'has_parent_transaction',
            'id',
            'integration_id',
            'is_3d_secure',
            'is_auth',
            'is_capture',
            'is_refunded',
            'is_standalone_payment',
            'is_voided',
            'order',
            'owner',
            'pending',
            'source_data.pan',
            'source_data.sub_type',
            'source_data.type',
            'success',
        ];

        $concat = '';

        foreach ($fields as $key) {
            $value = $this->hmacValue($obj, $key);

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            } elseif ($value === null) {
                $value = '';
            }

            $concat .= (string) $value;
        }

        return hash_equals(hash_hmac('sha512', $concat, $this->hmacSecret), $hmac);
    }

    /**
     * @param array<string, mixed> $obj
     */
    private function hmacValue(array $obj, string $key): mixed
    {
        if ($key !== 'order') {
            return data_get($obj, $key);
        }

        $order = data_get($obj, 'order');

        if (is_array($order)) {
            return $order['id'] ?? '';
        }

        return $order;
    }

    private function toCents($amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function firstName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        return $parts[0] ?? 'Customer';
    }

    private function lastName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];

        return $parts[1] ?? 'NA';
    }

    /**
     * @param array<string, mixed> $obj
     */
    private function extractMerchantOrderId(array $obj, Request $request): ?string
    {
        $reference = data_get($obj, 'order.merchant_order_id')
            ?: data_get($obj, 'special_reference')
            ?: data_get($obj, 'merchant_order_id')
            ?: $request->input('special_reference')
            ?: $request->input('merchant_order_id');

        if (! is_scalar($reference)) {
            return null;
        }

        $reference = trim((string) $reference);

        return $reference !== '' ? $reference : null;
    }

    /**
     * @param array<string, mixed> $obj
     */
    private function extractPaymobReference(array $obj): ?string
    {
        $reference = data_get($obj, 'id') ?: data_get($obj, 'transaction_id');

        return is_scalar($reference) && trim((string) $reference) !== ''
            ? (string) $reference
            : null;
    }

    /**
     * @param array<string, mixed> $obj
     */
    private function isSuccessfulPayment(array $obj): bool
    {
        return (bool) data_get($obj, 'success', false)
            && ! (bool) data_get($obj, 'pending', false)
            && ! (bool) data_get($obj, 'is_voided', false)
            && ! (bool) data_get($obj, 'is_refunded', false);
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
    private function redactForStorage(array $payload): array
    {
        unset($payload['client_secret']);

        return $payload;
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
                || str_contains($keyString, 'hmac')
                || str_contains($keyString, 'authorization')
                    ? '[redacted]'
                    : $this->redactForLog($item);
        }

        return $redacted;
    }
}
