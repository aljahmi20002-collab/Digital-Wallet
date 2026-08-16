<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\FeatureManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin-side feature gate. Unlike EnsureFeatureEnabled, this does not skip
 * the check for the admin panel – it simply verifies the feature's global
 * `is_enabled` flag so that disabling a feature from Feature Management also
 * hides it from every admin route, not just the user-facing surface.
 *
 * Usage: Route::middleware('admin.feature.enabled:p2p_marketplace')->group(...)
 */
class EnsureAdminFeatureEnabled
{
    public function __construct(private readonly FeatureManager $features) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (! $this->features->isEnabled($feature)) {
            abort(404);
        }

        return $next($request);
    }
}
