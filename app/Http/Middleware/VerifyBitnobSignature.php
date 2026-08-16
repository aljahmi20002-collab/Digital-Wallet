<?php

namespace App\Http\Middleware;

use App\Models\PaymentGateway;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bitnob signs each webhook with HMAC over the raw request body using the
 * gateway webhook secret. We accept sha256/sha512 in hex or base64 form and
 * reject mismatches with 401 when a real webhook secret is configured.
 */
class VerifyBitnobSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = (string) ($request->header('X-Bitnob-Signature')
            ?? $request->header('x-bitnob-signature')
            ?? $request->header('X-Bitnob-Signature-256')
            ?? $request->header('x-bitnob-signature-256')
            ?? $request->header('X-Bitnob-Signature-512')
            ?? $request->header('x-bitnob-signature-512')
            ?? $request->header('X-Signature')
            ?? $request->header('x-signature')
            ?? '');
        $rawBody = $request->getContent();

        if (! $this->verifySignature($rawBody, $signature)) {
            Log::warning('Bitnob webhook signature mismatch', [
                'path' => $request->path(),
                'ip'   => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        return $next($request);
    }

    private function verifySignature(string $rawBody, string $providedSignature): bool
    {
        $credentials = PaymentGateway::getCredentials('bitnob');
        $secret      = (string) ($credentials['webhook_secret']
            ?? $credentials['webhook_signing_secret']
            ?? $credentials['signing_secret']
            ?? $credentials['webhook_key']
            ?? $credentials['webhook_token']
            ?? $credentials['secret']
            ?? '');

        if ($this->isBlankSecret($secret)) {
            return true;
        }

        if ($providedSignature === '') {
            return false;
        }

        $providedSignature = $this->normalizeSignature(trim($providedSignature));
        foreach (['sha256', 'sha512'] as $algo) {
            $hex = hash_hmac($algo, $rawBody, $secret);
            if (hash_equals($hex, $providedSignature)) {
                return true;
            }

            $binary = hex2bin($hex);
            if ($binary !== false && hash_equals(base64_encode($binary), $providedSignature)) {
                return true;
            }
        }

        return false;
    }

    private function isBlankSecret(string $secret): bool
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

    private function normalizeSignature(string $signature): string
    {
        foreach (['sha256=', 'sha512=', 'hmac-sha256=', 'hmac-sha512='] as $prefix) {
            if (str_starts_with(strtolower($signature), $prefix)) {
                return substr($signature, strlen($prefix));
            }
        }

        return $signature;
    }
}
