<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Demo-mode admin guard.
 *
 * When APP_DEMO=true the admin panel is the public sandbox — any
 * visitor with the published admin credentials can log in. We must
 * keep the panel browsable for buyers/evaluators, but stop a
 * curious visitor from breaking the install for the next person.
 *
 * Guard rails:
 *
 *   1. Login-as-User (GET admin/user/login/{id}) is REFUSED — the
 *      evaluator should sign in directly with the published user
 *      credentials instead of hijacking a session from the admin
 *      panel. A notifyEvs() toast tells them what just happened.
 *
 *   2. Every write method (POST/PUT/PATCH/DELETE) is refused so
 *      settings, users, plans, currencies, etc. cannot be mutated
 *      by an anonymous evaluator. The per-controller demo guards
 *      (isDemoProtectedAccount() on credentials, etc.) cover the
 *      few write paths that need finer rules.
 */
class DemoMode
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('app.demo')) {
            return $next($request);
        }

        $route = $request->route();

        if ($route && $route->getName() === 'admin.user.login') {
            notifyEvs('error', __('Login as User is disabled while demo mode is on. Sign in with the published user credentials instead.'));

            return redirect()->back();
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            notifyEvs('error', __('This action is disabled in demo mode.'));

            return redirect()->back();
        }

        return $next($request);
    }
}
