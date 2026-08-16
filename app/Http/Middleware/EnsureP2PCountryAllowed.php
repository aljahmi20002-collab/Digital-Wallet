<?php

namespace App\Http\Middleware;

use App\Services\IpInfoService;
use Closure;
use Illuminate\Http\Request;

class EnsureP2PCountryAllowed
{
    public function handle(Request $request, Closure $next)
    {
        $allowed = trim((string) setting('p2p_allowed_countries', ''));
        $blocked = trim((string) setting('p2p_blocked_countries', ''));

        if ($allowed === '' && $blocked === '') {
            return $next($request);
        }

        $ip   = $request->ip();
        $info = app(IpInfoService::class)->getIpInfo($ip);
        $code = strtoupper(trim((string) ($info['country'] ?? '')));

        // When an allow-list is configured we MUST be able to verify the
        // visitor's country — otherwise we'd silently fall through and let
        // unverified traffic into a region-locked marketplace. Fail closed.
        if ($allowed !== '' && $code === '') {
            abort(403, __('Your country is not allowed for P2P.'));
        }

        if ($code !== '' && $blocked !== '') {
            $blockedList = array_filter(array_map('trim', explode(',', strtoupper($blocked))));
            if (in_array($code, $blockedList, true)) {
                abort(403, __('Your country is not allowed for P2P.'));
            }
        }

        if ($code !== '' && $allowed !== '') {
            $allowedList = array_filter(array_map('trim', explode(',', strtoupper($allowed))));
            if (! in_array($code, $allowedList, true)) {
                abort(403, __('Your country is not allowed for P2P.'));
            }
        }

        return $next($request);
    }
}
