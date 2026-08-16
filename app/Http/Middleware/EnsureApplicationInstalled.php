<?php

namespace App\Http\Middleware;

use App\Support\InstallationManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class EnsureApplicationInstalled
{
    public function __construct(private readonly InstallationManager $installer) {}

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInstallerRoute = $request->is('install') || $request->is('install/*');
        $isInstalled      = $this->installer->isInstalled();

        if ($isInstallerRoute && ! $isInstalled) {
            if (Config::get('session.driver') === 'database' && ! $this->installer->sessionTableAvailable()) {
                Config::set('session.driver', 'file');
            }

            return $next($request);
        }

        if ($isInstallerRoute) {
            return redirect()->route('home');
        }

        if ($isInstalled) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => __('The application has not been installed yet.'),
            ], 503);
        }

        return redirect()->route('install.index');
    }
}
