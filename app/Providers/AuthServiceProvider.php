<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\Merchant;
use App\Models\P2P\Offer;
use App\Models\P2P\Order;
use App\Models\P2P\PaymentAccount;
use App\Models\User;
use App\Policies\MerchantPolicy;
use App\Policies\P2POfferPolicy;
use App\Policies\P2POrderPolicy;
use App\Policies\P2PPaymentAccountPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Merchant::class       => MerchantPolicy::class,
        Offer::class          => P2POfferPolicy::class,
        Order::class          => P2POrderPolicy::class,
        PaymentAccount::class => P2PPaymentAccountPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate 1: Only Merchants can view merchant pages
        Gate::define('merchant', function (User $user) {
            return $user->role === UserRole::MERCHANT;
        });

        // Gate 2: Only standard users can view user pages
        Gate::define('user', function (User $user) {
            return $user->role === UserRole::USER;
        });

        // Gate 3: Only Agents can view agent pages
        Gate::define('agent', function (User $user) {
            return $user->role === UserRole::AGENT;
        });
    }
}
