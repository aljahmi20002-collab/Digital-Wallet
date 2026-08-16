<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureP2PEnabled
{
    public function handle(Request $request, Closure $next)
    {
        if (! (bool) setting('p2p_enabled', false)) {
            abort(404);
        }

        return $next($request);
    }
}
