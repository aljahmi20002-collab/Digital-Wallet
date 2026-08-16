<?php

namespace App\Http\Middleware;

use App\Support\FrontendAuthRoutes;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuthenticateMiddleware extends Authenticate
{
    protected array $guards = [];

    /**
     * Handle an incoming request.
     *
     * This method is responsible for authenticating the user based on the provided guards.
     * It sets the guards property and then calls the parent handle method.
     *
     * @param  Request $request   The incoming request.
     * @param  Closure $next      The next middleware.
     * @param  string  ...$guards The guards to use for authentication.
     * @return mixed   The response from the parent handle method.
     *
     * @throws AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards): mixed
    {
        $this->guards = $guards;

        return parent::handle($request, $next, ...$guards);
    }

    /**
     * Redirect the user based on the authentication guard and request type.
     *
     * @param Request $request The incoming request.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        return FrontendAuthRoutes::loginUrlFor($request, Arr::first($this->guards));
    }
}
