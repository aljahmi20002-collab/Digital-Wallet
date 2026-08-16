<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMode
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get current app maintenance state and the desired setting
        $isDownForMaintenance = $this->app->isDownForMaintenance();
        $maintenanceMode      = (bool) setting('maintenance_mode', false);

        // Only toggle if states differ
        if ($isDownForMaintenance && ! $maintenanceMode) {
            // Disable maintenance mode if app is down but setting is off
            Artisan::call('up');
        } elseif (! $isDownForMaintenance && $maintenanceMode) {
            // Enable maintenance mode if app is up but setting is on
            $secretKey = setting('secret_key');
            $command   = 'down';
            $renderView = 'errors.maintenance';
            // Only use custom render view if it exists to avoid errors during optimize/view cache
            if (app('view')->exists($renderView)) {
                $command .= ' --render="'.addslashes($renderView).'"';
            }
            if (! empty($secretKey)) {
                $command .= ' --secret='.escapeshellarg($secretKey);
            }
            Artisan::call($command);
        }

        return $next($request);
    }
}
