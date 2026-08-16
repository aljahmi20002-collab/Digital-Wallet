<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilterBoostBrowserLoggerScript
{
    private const BROWSER_LOGGER_SCRIPT_PATTERN = '/<script\b(?=[^>]*\bid=(["\'])browser-logger-active\1)[^>]*>.*?<\/script>\s*/is';

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldFilter($response)) {
            return $response;
        }

        $originalView    = $response->original ?? null;
        $filteredContent = $this->removeBrowserLoggerScript((string) $response->getContent());

        $response->setContent($filteredContent);
        $response->headers->remove('Content-Length');

        if ($originalView instanceof View && property_exists($response, 'original')) {
            $response->original = $originalView;
        }

        return $response;
    }

    protected function shouldFilter(Response $response): bool
    {
        $ignoredResponses = [
            StreamedResponse::class,
            BinaryFileResponse::class,
            JsonResponse::class,
            RedirectResponse::class,
        ];

        foreach ($ignoredResponses as $ignoredResponse) {
            if ($response instanceof $ignoredResponse) {
                return false;
            }
        }

        $contentType = strtolower((string) $response->headers->get('content-type', ''));

        if (! str_contains($contentType, 'html')) {
            return false;
        }

        return str_contains((string) $response->getContent(), 'browser-logger-active');
    }

    protected function removeBrowserLoggerScript(string $content): string
    {
        return preg_replace(self::BROWSER_LOGGER_SCRIPT_PATTERN, '', $content) ?? $content;
    }
}
