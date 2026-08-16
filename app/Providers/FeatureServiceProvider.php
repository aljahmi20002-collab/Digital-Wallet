<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\FeatureManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the FeatureManager into the container as a singleton, registers
 * the `@feature` / `@featureaccess` Blade directives, and shares the
 * enabled feature keys with every rendered view so menus and partials
 * can rely on a single source of truth.
 */
class FeatureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FeatureManager::class, fn () => new FeatureManager);
        $this->app->alias(FeatureManager::class, 'feature.manager');
    }

    public function boot(): void
    {
        $this->registerBladeDirectives();
        $this->shareFeatureMapWithViews();
    }

    private function registerBladeDirectives(): void
    {
        /*
         * @feature('send_money') ... @endfeature
         *   – renders only when the feature is visible for the current panel.
         */
        Blade::if('feature', function (string $key, ?string $panel = null): bool {
            return app(FeatureManager::class)->isVisible($key, $panel);
        });

        /*
         * @featureaccess('withdraw_money') ... @endfeatureaccess
         *   – renders only when the feature is actually usable right now
         *     (global on + panel visible + panel accessible + conditions).
         */
        Blade::if('featureaccess', function (string $key, ?string $panel = null): bool {
            return app(FeatureManager::class)->isAccessible($key, $panel);
        });
    }

    private function shareFeatureMapWithViews(): void
    {
        View::composer('*', function ($view): void {
            if ($view->offsetExists('enabledFeatures')) {
                return;
            }

            $manager = app(FeatureManager::class);

            $view->with('enabledFeatures', $manager->enabled()->keys()->all());
            $view->with('featureManager', $manager);
        });
    }
}
