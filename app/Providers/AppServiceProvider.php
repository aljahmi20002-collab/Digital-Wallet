<?php

namespace App\Providers;

use App\Listeners\AwardSignupBonusOnVerified;
use App\Models\Agent;
use App\Models\Merchant;
use App\Models\User;
use App\Observers\AgentObserver;
use App\Observers\MerchantObserver;
use App\Observers\UserObserver;
use App\Services\AppConfigService;
use App\Services\CurrencyService;
use App\Services\IpInfoService;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\PaymentService;
use App\Services\QRCodeService;
use App\Services\TransactionService;
use App\Services\WalletService;
use App\Support\InstallationManager;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services into the service container.
     */
    public function register(): void
    {
        $this->registerServices();
        $this->bindFacades();

        // Bind AppConfigService for application-wide configuration
        $this->app->singleton(AppConfigService::class, fn ($app) => new AppConfigService);
    }

    /**
     * Register singleton services for dependency injection.
     * - Use `singleton()` for shared instances across the application.
     * - Use `bind()` if a new instance is needed for each resolve.
     */
    protected function registerServices(): void
    {
        $this->app->singleton(CurrencyService::class, fn ($app) => new CurrencyService);
        $this->app->singleton(WalletService::class, fn ($app) => new WalletService);
        $this->app->singleton(TransactionService::class, fn ($app) => new TransactionService);
        $this->app->singleton(IpInfoService::class, fn ($app) => new IpInfoService);
        $this->app->singleton(QRCodeService::class, fn ($app) => new QRCodeService);

        // Bind PaymentService with dependency injection
        $this->app->singleton(PaymentService::class, fn ($app) => new PaymentService($app->make(PaymentGatewayFactory::class)));
    }

    /**
     * Bind services with aliases for Facade support.
     * This allows accessing services statically via Facades.
     */
    protected function bindFacades(): void
    {
        $this->app->singleton('currency.service', fn ($app) => $app->make(CurrencyService::class));
        $this->app->singleton('wallet.service', fn ($app) => $app->make(WalletService::class));
        $this->app->singleton('transaction.service', fn ($app) => $app->make(TransactionService::class));
        $this->app->singleton('payment.service', fn ($app) => $app->make(PaymentService::class));
        $this->app->singleton('ifinfo.service', fn ($app) => $app->make(IpInfoService::class));
    }

    /**
     * Bootstrap application services.
     * Loads configuration settings, sets up observers, and ensures security features.
     */
    public function boot(AppConfigService $appConfigService, InstallationManager $installer): void
    {
        // Model observers must register in every environment (including
        // tests) so model lifecycle hooks like AgentObserver::creating run.
        $this->configureObservers();
        $this->configureEventListeners();

        // The `getDefaultLocale` macro is consumed widely (Page::getLabelAttribute,
        // PageController::create, the page-create / page-edit hero blades, several
        // models). Register it in EVERY environment so route-level feature tests
        // don't crash with "Method ... ::getDefaultLocale does not exist". It's
        // pure config lookup, so there's no install-state coupling.
        if (! Application::hasMacro('getDefaultLocale')) {
            Application::macro('getDefaultLocale', function () {
                return config('app.default_language') ?? config('app.fallback_locale') ?? config('app.locale');
            });
        }

        if ($this->app->environment('testing')) {
            return;
        }

        if (! $installer->isInstalled()) {
            $this->ensureAppKey();

            return;
        }

        $this->ensureAppKey();
        $appConfigService->applyAppSettings();
        $appConfigService->applyMailSettings();
        $appConfigService->forceHttpsIfEnabled();
        $appConfigService->applySmsConfig();
        $appConfigService->applyGoogleReCaptchaConfig();
        $appConfigService->ensureStorageSymlink();
    }

    /**
     * Ensure the application key is set.
     *
     * `.env` is created from `.env.example` earlier in bootstrap/app.php
     * (before Laravel loads env vars), so by the time we reach here the
     * file exists with `APP_KEY=` empty. We just need to generate a fresh
     * key on the very first request and update the runtime config so the
     * encrypter doesn't see `null`.
     *
     * `empty()` not `=== ''` — missing/blank env vars surface here as
     * `null`, which the strict-equality check would silently skip.
     */
    protected function ensureAppKey(): void
    {
        if (! empty(config('app.key'))) {
            return;
        }

        try {
            Artisan::call('key:generate', ['--force' => true]);
            Log::info('Application key generated successfully during deployment.');
        } catch (\Throwable $e) {
            Log::warning('key:generate failed during deployment: '.$e->getMessage());
        }
    }

    /**
     * Register model observers to handle model events.
     */
    protected function configureObservers(): void
    {
        User::observe(UserObserver::class);
        Merchant::observe(MerchantObserver::class);
        Agent::observe(AgentObserver::class);
    }

    /**
     * Register framework event listeners.
     */
    protected function configureEventListeners(): void
    {
        Event::listen(Verified::class, AwardSignupBonusOnVerified::class);
    }
}
