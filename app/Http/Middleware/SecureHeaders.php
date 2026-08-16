<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecureHeaders
{
    private array $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->removeUnwantedHeaders($response);

        if ((bool) config('security.secure_response_headers', true)) {
            $this->applyBrowserProtectionHeaders($response);
        }

        if ((bool) config('security.strict_transport_security', true)) {
            $this->applyStrictTransportSecurity($response);
        }

        return $response;
    }

    private function removeUnwantedHeaders(Response $response): void
    {
        foreach ($this->unwantedHeaderList as $header) {
            $response->headers->remove($header);
        }
    }

    private function applyBrowserProtectionHeaders(Response $response): void
    {
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=(self)');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('X-Download-Options', 'noopen');
    }

    private function applyStrictTransportSecurity(Response $response): void
    {
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }
}
