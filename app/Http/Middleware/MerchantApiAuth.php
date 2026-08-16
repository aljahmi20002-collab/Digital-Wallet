<?php

namespace App\Http\Middleware;

use App\Enums\EnvironmentMode;
use App\Models\Merchant;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class MerchantApiAuth
{
    /**
     * Handle an incoming request.
     *
     * Expected headers:
     * - X-Merchant-Key
     * - X-API-Key
     * - X-Timestamp Unix timestamp in seconds when signatures are required
     * - X-Signature HMAC-SHA256 of "timestamp.method.path_with_query.raw_body"
     * - X-Environment optional: "production" or "sandbox", defaults to "production"
     */
    public function handle(Request $request, Closure $next): Response|JsonResponse
    {
        $apiKey      = $request->header('X-API-Key');
        $merchantKey = $request->header('X-Merchant-Key');
        $environment = $request->header('X-Environment', EnvironmentMode::PRODUCTION->value);

        $validEnvironments = array_column(EnvironmentMode::cases(), 'value');
        if (! in_array($environment, $validEnvironments, true)) {
            return response()->json([
                'error'              => 'Invalid environment',
                'message'            => 'Use "production" or "sandbox"',
                'valid_environments' => $validEnvironments,
            ], 400);
        }

        $environmentEnum = EnvironmentMode::from($environment);
        $merchant        = $this->findMerchantByCredentials($apiKey, $merchantKey, $environmentEnum);

        if (! $merchant) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Invalid API credentials for the specified environment',
            ], 401);
        }

        if ($merchant->isActionLocked()) {
            return response()->json([
                'error'   => 'Merchant Unavailable',
                'message' => 'This merchant is currently disabled or rejected.',
            ], 403);
        }

        if ($environmentEnum->isProduction() && ! $merchant->isApproved()) {
            return response()->json([
                'error'   => 'Merchant Pending Approval',
                'message' => 'Production API access is available after merchant approval.',
            ], 403);
        }

        if ($environmentEnum->isSandbox() && ! $merchant->sandbox_enabled) {
            return response()->json([
                'error'   => 'Sandbox Disabled',
                'message' => 'Sandbox mode is disabled for this merchant',
            ], 403);
        }

        if ($rateLimitResponse = $this->guardApiRateLimit($request, $merchant)) {
            return $rateLimitResponse;
        }

        if ($signatureResponse = $this->guardApiSignature($request, $merchant, $environmentEnum)) {
            return $signatureResponse;
        }

        $merchant->current_environment = $environmentEnum;

        $request->merge([
            'merchant'    => $merchant,
            'environment' => $environmentEnum->value,
            'is_sandbox'  => $environmentEnum->isSandbox(),
        ]);

        return $next($request);
    }

    private function findMerchantByCredentials(?string $apiKey, ?string $merchantKey, EnvironmentMode $environment): ?Merchant
    {
        if ($apiKey === null || $merchantKey === null) {
            return null;
        }

        if ($environment->isSandbox()) {
            return Merchant::query()
                ->with(['user', 'currency', 'supportedCurrencies'])
                ->where('test_api_key', $apiKey)
                ->where('test_merchant_key', $merchantKey)
                ->where('sandbox_enabled', true)
                ->first();
        }

        return Merchant::query()
            ->with(['user', 'currency', 'supportedCurrencies'])
            ->where('api_key', $apiKey)
            ->where('merchant_key', $merchantKey)
            ->first();
    }

    private function guardApiRateLimit(Request $request, Merchant $merchant): ?JsonResponse
    {
        $limit = $this->merchantApiRateLimit();
        $key   = 'merchant-api:'.$merchant->id.':'.$request->ip();

        if (! RateLimiter::tooManyAttempts($key, $limit)) {
            RateLimiter::hit($key, 60);

            return null;
        }

        $seconds = RateLimiter::availableIn($key);

        return response()->json([
            'error'       => 'Too Many Requests',
            'message'     => 'Merchant API rate limit exceeded. Please try again later.',
            'retry_after' => $seconds,
        ], 429)->withHeaders([
            'Retry-After' => (string) $seconds,
        ]);
    }

    private function guardApiSignature(Request $request, Merchant $merchant, EnvironmentMode $environment): ?JsonResponse
    {
        if (! (bool) config('security.merchant_api_signature_required', true)) {
            return null;
        }

        $timestamp = (string) $request->header('X-Timestamp', '');
        $signature = (string) $request->header('X-Signature', '');

        if ($timestamp === '' || $signature === '') {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Missing API request signature headers.',
            ], 401);
        }

        if (! ctype_digit($timestamp) || $this->isTimestampOutsideTolerance((int) $timestamp)) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'API request timestamp is invalid or expired.',
            ], 401);
        }

        $secret = $this->merchantApiSecret($merchant, $environment);
        if ($secret === '') {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'API signing secret is not configured for this merchant.',
            ], 401);
        }

        $expected = hash_hmac('sha256', $this->signaturePayload($request, $timestamp), $secret);
        $provided = str_starts_with($signature, 'sha256=') ? substr($signature, 7) : $signature;

        if (! hash_equals($expected, $provided)) {
            return response()->json([
                'error'   => 'Unauthorized',
                'message' => 'Invalid API request signature.',
            ], 401);
        }

        return null;
    }

    private function signaturePayload(Request $request, string $timestamp): string
    {
        return implode('.', [
            $timestamp,
            strtoupper($request->method()),
            $request->getRequestUri(),
            $request->getContent(),
        ]);
    }

    private function merchantApiSecret(Merchant $merchant, EnvironmentMode $environment): string
    {
        if ($environment->isSandbox()) {
            return (string) $merchant->getRawOriginal('test_api_secret');
        }

        return (string) $merchant->getRawOriginal('api_secret');
    }

    private function isTimestampOutsideTolerance(int $timestamp): bool
    {
        return abs(now()->timestamp - $timestamp) > $this->merchantApiTimestampTolerance();
    }

    private function merchantApiTimestampTolerance(): int
    {
        return max(60, min(900, (int) config('security.merchant_api_timestamp_tolerance', 300)));
    }

    private function merchantApiRateLimit(): int
    {
        return max(30, min(600, (int) config('security.merchant_api_rate_limit_per_minute', 120)));
    }
}
