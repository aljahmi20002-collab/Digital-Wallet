<?php

namespace App\Support;

use Illuminate\Http\Request;

class FrontendAuthRoutes
{
    public static function loginRouteNameFor(Request $request, ?string $guard = null): string
    {
        if ($guard === 'admin' || self::matchesAdmin($request)) {
            return 'admin.login-view';
        }

        if (self::matchesAgent($request)) {
            return 'agent.login';
        }

        if (self::matchesMerchant($request)) {
            return 'merchant.login';
        }

        return 'user.login';
    }

    public static function loginUrlFor(Request $request, ?string $guard = null): string
    {
        return route(self::loginRouteNameFor($request, $guard));
    }

    public static function authenticatedRouteNameFor(Request $request, ?string $guard = null): string
    {
        if ($guard === 'admin' || self::matchesAdmin($request)) {
            return 'admin.dashboard';
        }

        return 'user.dashboard';
    }

    public static function authenticatedUrlFor(Request $request, ?string $guard = null): string
    {
        return route(self::authenticatedRouteNameFor($request, $guard));
    }

    private static function matchesAdmin(Request $request): bool
    {
        $adminPrefix = trim((string) setting('admin_prefix', 'admin')) ?: 'admin';

        return $request->routeIs('admin.*')
            || $request->is($adminPrefix)
            || $request->is($adminPrefix.'/*');
    }

    private static function matchesAgent(Request $request): bool
    {
        return $request->routeIs('agent.*', 'user.agent.*')
            || $request->is('agent')
            || $request->is('agent/*')
            || $request->is('user/agent')
            || $request->is('user/agent/*');
    }

    private static function matchesMerchant(Request $request): bool
    {
        return $request->routeIs('merchant.*', 'user.merchant.*')
            || $request->is('merchant')
            || $request->is('merchant/*')
            || $request->is('user/merchant')
            || $request->is('user/merchant/*');
    }
}
