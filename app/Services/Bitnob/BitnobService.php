<?php

namespace App\Services\Bitnob;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Low-level Bitnob HTTP client.
 *
 * Every request is signed with HMAC over
 * CLIENT_ID:TIMESTAMP:NONCE:PAYLOAD. The four resulting auth headers are
 * sent on every call. Higher-level services (cards, payouts, deposits) use this.
 */
class BitnobService
{
    private const SENSITIVE_KEYS = [
        'authorization',
        'x-auth-client',
        'x-auth-signature',
        'client_id',
        'client_secret',
        'hmac_key',
        'api_key',
        'api_secret',
        'secret',
        'secret_key',
        'lightning_key',
        'webhook_secret',
        'token',
        'access_token',
        'bearer_token',
        'card_number',
        'cardNumber',
        'pan',
        'cvv',
        'cvv2',
        'id_number',
        'idNumber',
        'bvn',
        'userPhoto',
        'idImage',
    ];

    protected string $clientId;

    protected string $clientSecret;

    protected string $baseUrl;

    protected string $mode;

    protected string $signatureAlgo;

    public function __construct()
    {
        $credentials = PaymentGateway::getCredentials('bitnob');
        if ($this->credentialsLookLikePlaceholders($credentials)) {
            Cache::forget('payment_gateway_code_bitnob');
            $credentials = PaymentGateway::getCredentials('bitnob');
        }

        if ($this->credentialsLookLikePlaceholders($credentials)) {
            throw BitnobException::fromResponse('Bitnob API credentials are still placeholders. Paste the real Client ID and HMAC Key from the Bitnob dashboard, then clear cache and retry.');
        }

        $sandbox            = (bool) ($credentials['sandbox'] ?? true);
        $this->mode         = $sandbox ? 'sandbox' : 'live';
        $this->baseUrl      = rtrim((string) (config("bitnob.base_url.{$this->mode}")), '/');
        $this->clientId     = $this->firstCredentialValue($credentials, ['client_id']);
        $this->clientSecret = $this->firstCredentialValue($credentials, ['hmac_key', 'hmacKey', 'client_secret', 'secret_key', 'secretKey', 'secret']);
        // Bitnob's docs describe HMAC for request auth. Some plans use SHA256
        // and some use SHA512. We default to SHA256 (per their authentication
        // page) but allow override via the gateway credentials row.
        $this->signatureAlgo = (string) ($credentials['signature_algo'] ?? 'sha256');

        if ($this->clientId === '' || $this->clientSecret === '') {
            throw BitnobException::fromResponse('Bitnob API credentials are not configured.');
        }

        if ($this->hasPlaceholderCredentials()) {
            throw BitnobException::fromResponse('Bitnob API credentials are still placeholders. Update the Bitnob gateway with the real Client ID and HMAC Key from the Bitnob dashboard, then clear cache and retry.');
        }
    }

    public function mode(): string
    {
        return $this->mode;
    }

    public function clientSecret(): string
    {
        return $this->clientSecret;
    }

    private function hasPlaceholderCredentials(): bool
    {
        return $this->credentialsLookLikePlaceholders([
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);
    }

    private function credentialsLookLikePlaceholders(array $credentials): bool
    {
        $placeholders = [
            'client_id',
            'client_secret',
            'hmac_key',
            'api_key',
            'api_secret',
            'secret',
            'secret_key',
            'token',
            'access_token',
            'bearer_token',
            'your_client_id',
            'your_client_secret',
            'test-client-id',
            'test-client-secret',
        ];

        $clientId     = $this->firstRawCredentialValue($credentials, ['client_id']);
        $clientSecret = $this->firstRawCredentialValue($credentials, ['hmac_key', 'hmacKey', 'client_secret', 'secret_key', 'secretKey', 'secret']);

        return in_array(strtolower($clientId), $placeholders, true)
            || in_array(strtolower($clientSecret), $placeholders, true);
    }

    private function firstCredentialValue(array $credentials, array $keys): string
    {
        foreach ($keys as $key) {
            if (! empty($credentials[$key]) && is_scalar($credentials[$key])) {
                $value = trim((string) $credentials[$key]);
                if (! $this->isPlaceholderValue($value)) {
                    return $value;
                }
            }
        }

        return '';
    }

    private function firstRawCredentialValue(array $credentials, array $keys): string
    {
        foreach ($keys as $key) {
            if (! empty($credentials[$key]) && is_scalar($credentials[$key])) {
                return trim((string) $credentials[$key]);
            }
        }

        return '';
    }

    private function isPlaceholderValue(string $value): bool
    {
        $normalized = strtolower(trim($value));

        return $normalized === ''
            || str_ends_with($normalized, '_key')
            || str_ends_with($normalized, '_secret')
            || in_array($normalized, [
                'client_id',
                'client_secret',
                'api_key',
                'api_secret',
                'secret',
                'secret_key',
                'token',
                'access_token',
                'bearer_token',
                'your_client_id',
                'your_client_secret',
                'test-client-id',
                'test-client-secret',
            ], true);
    }

    /**
     * @param  array<string, mixed> $query
     * @return array<mixed>
     */
    public function get(string $path, array $query = []): array
    {
        return $this->request('GET', $path, [], $query);
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<mixed>
     */
    public function post(string $path, array $payload = []): array
    {
        return $this->request('POST', $path, $payload);
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<mixed>
     */
    public function put(string $path, array $payload = []): array
    {
        return $this->request('PUT', $path, $payload);
    }

    /**
     * @param  array<string, mixed> $payload
     * @return array<mixed>
     */
    public function delete(string $path, array $payload = []): array
    {
        return $this->request('DELETE', $path, $payload);
    }

    /**
     * Resolve an endpoint key to a full URL with sprintf substitutions.
     */
    public function url(string $key, mixed ...$args): string
    {
        $template = (string) config("bitnob.endpoints.$key");

        if ($template === '') {
            throw BitnobException::fromResponse("Bitnob endpoint not configured: $key");
        }

        return $args === [] ? $template : vsprintf($template, $args);
    }

    /**
     * Compute the HMAC signature for a canonical message.
     */
    public function sign(string $message, ?string $algo = null): string
    {
        return hash_hmac($algo ?? $this->signatureAlgo, $message, $this->clientSecret);
    }

    /**
     * @param  array<string, mixed> $payload
     * @param  array<string, mixed> $query
     * @return array<mixed>
     */
    protected function request(string $method, string $path, array $payload = [], array $query = []): array
    {
        $started   = microtime(true);
        $traceId   = 'bn_'.Str::uuid()->toString();
        $body      = $payload === [] ? '' : json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = (string) time();
        $nonce     = bin2hex(random_bytes(16));
        $message   = "{$this->clientId}:{$timestamp}:{$nonce}:{$body}";
        $signature = $this->sign($message);

        $url        = $this->baseUrl.$path;
        $logContext = [
            'trace_id' => $traceId,
            'mode'     => $this->mode,
            'method'   => strtoupper($method),
            'path'     => $path,
            'url'      => $url,
            'query'    => $this->redactForLog($query),
            'payload'  => $this->redactForLog($payload),
        ];

        Log::info('Bitnob API request', $logContext + [
            'payload_keys' => array_keys($payload),
            'query_keys'   => array_keys($query),
        ]);

        $http = Http::withHeaders([
            'X-Auth-Client'    => $this->clientId,
            'X-Auth-Timestamp' => $timestamp,
            'X-Auth-Nonce'     => $nonce,
            'X-Auth-Signature' => $signature,
            'Accept'           => 'application/json',
            'Content-Type'     => 'application/json',
        ])->timeout((int) config('bitnob.timeout', 20));

        try {
            $response = match (strtoupper($method)) {
                'GET'    => $http->get($url, $query),
                'POST'   => $body === '' ? $http->post($url) : $http->withBody($body, 'application/json')->post($url),
                'PUT'    => $body === '' ? $http->put($url) : $http->withBody($body, 'application/json')->put($url),
                'DELETE' => $body === '' ? $http->delete($url) : $http->withBody($body, 'application/json')->delete($url),
                default  => throw BitnobException::fromResponse("Unsupported HTTP verb: $method"),
            };
        } catch (\Throwable $e) {
            Log::error('Bitnob transport error', $logContext + [
                'duration_ms' => $this->durationMs($started),
                'error'       => $e->getMessage(),
                'exception'   => get_class($e),
            ]);
            throw BitnobException::fromResponse("Bitnob request failed: {$e->getMessage()}", [
                'trace_id' => $traceId,
                'method'   => strtoupper($method),
                'path'     => $path,
            ]);
        }

        $json = $response->json();

        if (! is_array($json)) {
            $json = ['raw' => (string) $response->body()];
        }

        if ($response->failed() || (isset($json['success']) && $json['success'] === false)) {
            $message = $this->extractErrorMessage($json) ?? 'Bitnob API error';

            Log::warning('Bitnob API failure', $logContext + [
                'duration_ms' => $this->durationMs($started),
                'status'      => $response->status(),
                'message'     => $message,
                'body'        => $this->redactForLog($json),
            ]);

            throw BitnobException::fromResponse(is_string($message) ? $message : 'Bitnob API error', [
                'trace_id' => $traceId,
                'status'   => $response->status(),
                'method'   => strtoupper($method),
                'path'     => $path,
                'body'     => $json,
            ]);
        }

        Log::info('Bitnob API response', $logContext + [
            'duration_ms'   => $this->durationMs($started),
            'status'        => $response->status(),
            'response_keys' => array_keys($json),
            'body'          => $this->redactForLog($json),
        ]);

        return $json;
    }

    private function durationMs(float $started): int
    {
        return (int) round((microtime(true) - $started) * 1000);
    }

    private function extractErrorMessage(array $payload): ?string
    {
        foreach (['message', 'error', 'errorMessage', 'failure_reason', 'failureReason', 'reason', 'detail', 'details'] as $key) {
            if (! array_key_exists($key, $payload)) {
                continue;
            }

            $value = $payload[$key];
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }

            if (is_array($value)) {
                $nested = $this->extractErrorMessage($value);
                if ($nested !== null) {
                    return $nested;
                }
            }
        }

        if (isset($payload['errors']) && is_array($payload['errors'])) {
            $nested = $this->extractErrorMessage($payload['errors']);
            if ($nested !== null) {
                return $nested;
            }
        }

        if (isset($payload['data']) && is_array($payload['data'])) {
            return $this->extractErrorMessage($payload['data']);
        }

        return null;
    }

    private function redactForLog(mixed $value): mixed
    {
        if (is_array($value)) {
            $redacted = [];
            foreach ($value as $key => $item) {
                $keyString      = is_string($key) ? $key : (string) $key;
                $redacted[$key] = $this->isSensitiveLogKey($keyString)
                    ? $this->redactedValue($item, $keyString)
                    : $this->redactForLog($item);
            }

            return $redacted;
        }

        return $value;
    }

    private function isSensitiveLogKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SENSITIVE_KEYS as $sensitiveKey) {
            if ($normalized === strtolower($sensitiveKey)) {
                return true;
            }
        }

        return str_contains($normalized, 'secret')
            || str_contains($normalized, 'token')
            || str_contains($normalized, 'signature')
            || str_contains($normalized, 'password');
    }

    private function redactedValue(mixed $value, string $key): string
    {
        if (! is_scalar($value)) {
            return '[redacted]';
        }

        $text = (string) $value;
        if ($text === '') {
            return '[empty]';
        }

        if (str_contains(strtolower($key), 'url') || in_array($key, ['userPhoto', 'idImage'], true)) {
            return '[url:'.parse_url($text, PHP_URL_HOST).']';
        }

        return strlen($text) <= 4
            ? '[redacted]'
            : substr($text, 0, 2).'...'.substr($text, -2);
    }

    /**
     * Verify a webhook signature. Bitnob signs the raw request body with
     * HMAC-SHA512 using the client secret and ships it in `x-bitnob-signature`.
     */
    public function verifyWebhookSignature(string $rawBody, string $providedSignature): bool
    {
        if ($providedSignature === '') {
            return false;
        }
        $expected = hash_hmac('sha512', $rawBody, $this->clientSecret);

        return hash_equals($expected, $providedSignature);
    }

    /**
     * Generate a deterministic-ish reference for idempotency.
     */
    public static function reference(string $prefix = 'dk'): string
    {
        return $prefix.'_'.now()->format('YmdHis').'_'.Str::random(8);
    }
}
