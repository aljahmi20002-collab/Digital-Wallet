<?php

namespace App\Http\Middleware;

use App\Support\FrontendAuthRoutes;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response|RedirectResponse) $next
     * @param string|null                                   ...$guards
     */
    public function handle(Request $request, Closure $next, ...$guards): Response|RedirectResponse
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            switch ($guard) {
                case 'admin':
                    if (Auth::guard($guard)->check()) {
                        return redirect(FrontendAuthRoutes::authenticatedUrlFor($request, $guard));
                    }
                    break;

                default:
                    if (Auth::guard($guard)->check()) {
                        return redirect(FrontendAuthRoutes::authenticatedUrlFor($request, $guard));
                    }
                    break;
            }
        }

        return $next($request);
    }
}
