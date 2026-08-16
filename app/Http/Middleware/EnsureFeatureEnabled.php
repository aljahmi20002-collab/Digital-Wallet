<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\NotifyErrorException;
use App\Services\FeatureManager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Route-level guard that blocks access to any feature surface whose key
 * is disabled globally, hidden for the current panel, or whose runtime
 * conditions (KYC, phone, country, ...) fail for the authenticated actor.
 *
 * Usage in routes:
 *
 *   Route::middleware('feature.enabled:p2p_marketplace')->group(...)
 *
 * Behaviour:
 *   - Feature disabled / panel off → 404 (the surface behaves as if it
 *     does not exist so sensitive features can be softly removed).
 *   - KYC / country conditions fail → friendly notifiable error so the
 *     user understands what they need to do next.
 */
class EnsureFeatureEnabled
{
    public function __construct(private readonly FeatureManager $features) {}

    /**
     * @throws NotifyErrorException
     */
    public function handle(Request $request, Closure $next, string $feature, ?string $panel = null): Response
    {
        $user = $request->user();

        $reason = $this->features->denialReason($feature, $panel, $user);

        if ($reason === null) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return match ($reason) {
                FeatureManager::DENIED_KYC_REQUIRED => new JsonResponse([
                    'status'  => false,
                    'message' => __('Please complete your KYC verification to access this feature.'),
                ], 403),
                FeatureManager::DENIED_PHONE_REQUIRED => new JsonResponse([
                    'status'  => false,
                    'message' => __('Please enable phone verification to access this feature.'),
                ], 403),
                FeatureManager::DENIED_COUNTRY_BLOCKED => new JsonResponse([
                    'status'  => false,
                    'message' => __('This feature is not available in your country.'),
                ], 403),
                default => $this->notFound($request),
            };
        }

        return match ($reason) {
            FeatureManager::DENIED_KYC_REQUIRED => throw new NotifyErrorException(
                __('Please complete your KYC verification to access this feature.'),
                403
            ),
            FeatureManager::DENIED_PHONE_REQUIRED => throw new NotifyErrorException(
                __('Please enable phone verification to access this feature.'),
                403
            ),
            FeatureManager::DENIED_COUNTRY_BLOCKED => throw new NotifyErrorException(
                __('This feature is not available in your country.'),
                403
            ),
            default => $this->notFound($request),
        };
    }

    private function notFound(Request $request): Response
    {
        if ($request->expectsJson()) {
            return new JsonResponse([
                'status'  => false,
                'message' => __('This feature is currently unavailable.'),
            ], 404);
        }

        abort(404);
    }
}
