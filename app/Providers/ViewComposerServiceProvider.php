<?php

namespace App\Providers;

use App\Enums\TrxType;
use App\Models\FooterSection;
use App\Models\Language;
use App\Models\Navigation;
use App\Models\Social;
use App\Services\FeatureManager;
use App\Support\AdminSidebarIndicatorManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->composeLanguages();
        $this->composeUserNotifications();
        $this->composeSocialLinks();
        $this->composeNavigation();
        $this->composeFooter();
        $this->composeAdminPermissions();
        $this->composeQuickFunctions();
        $this->composeSignupBonusPopup();
    }

    /**
     * Share active languages with caching across needed views.
     */
    protected function composeLanguages(): void
    {
        View::composer([
            'backend.layouts.partials._header',
            'frontend.layouts.user.partials._mobile_navbar',
            'frontend.layouts.user.partials._mobile_app_header',
            'frontend.layouts.user.partials._navbar',
            'frontend.layouts.partials._language_switcher',
            'frontend.layouts.golden.partials._language_switcher',
        ], function ($view) {
            $languages = Language::activeCached();

            $view->with('languages', $languages);
        });
    }

    /**
     * Share user unread notifications across frontend navbar views.
     */
    protected function composeUserNotifications(): void
    {
        app()->singleton('unreadNotificationsData', function () {
            $user = auth()->user();

            if (! $user) {
                return [
                    'count'         => 0,
                    'notifications' => collect(),
                ];
            }

            $allUnread = $user->unreadNotifications()->latest()->get();

            return [
                'count'         => $allUnread->count(),
                'notifications' => $allUnread,
            ];
        });

        View::composer([
            'frontend.layouts.user.partials._navbar',
            'frontend.layouts.user.partials._mobile_navbar',
            'frontend.layouts.user.partials._mobile_app_header',
        ], function ($view) {
            $notificationData = app('unreadNotificationsData');

            $view->with([
                'notifications'     => $notificationData['notifications'],
                'notificationCount' => $notificationData['count'],
            ]);
        });
    }

    /**
     * Inject the pending signup-bonus popup payload into the user layout.
     *
     * Resolves to `null` (so the partial renders nothing) unless the
     * authenticated user has been awarded a bonus that they haven't
     * acknowledged yet. The payload mirrors the design's variables
     * exactly: name, amount, currency, tier, walletUrl, transactionUrl.
     */
    protected function composeSignupBonusPopup(): void
    {
        View::composer(['frontend.layouts.user.index'], function ($view): void {
            $user = auth()->user();

            $payload = null;

            if ($user
                && $user->signup_bonus_awarded_at !== null
                && $user->signup_bonus_seen_at === null
            ) {
                $transaction = $user->transactions()
                    ->where('trx_type', TrxType::SIGNUP_BONUS)
                    ->latest('id')
                    ->first();

                if ($transaction) {
                    $payload = [
                        'name'           => $user->first_name ?? $user->name,
                        'amount'         => (float) $transaction->amount,
                        'currency'       => $transaction->payable_currency ?? siteCurrency(),
                        'tier'           => $user->role->title().' Bonus',
                        'walletUrl'      => route('user.wallet.index'),
                        'transactionUrl' => route('user.transaction.index'),
                        'acknowledgeUrl' => route('user.signup-bonus.acknowledge'),
                    ];
                }
            }

            $view->with('signupBonusPopup', $payload);
        });
    }

    protected function composeQuickFunctions(): void
    {

        View::composer([
            'frontend.layouts.user.partials._quick_functions',
            'frontend.layouts.user.partials._mobile_app_header',
        ], function ($view) {
            $mainCount      = 6;
            $quickLinksMain = array_slice($this->getQuickLinks(), 0, $mainCount);
            $quickLinksMore = array_slice($this->getQuickLinks(), $mainCount);
            $view->with([
                'quickLinksMain' => $quickLinksMain,
                'quickLinksMore' => $quickLinksMore,
            ]);
        });
    }

    /**
     * Share active social links in frontend parts.
     */
    protected function composeSocialLinks(): void
    {
        View::composer([
            'frontend.layouts.partials._offcanvas',
            'frontend.layouts.partials._header_top',
            // Golden theme — top strip + footer both render social icons
            'frontend.layouts.golden.partials._topstrip',
            'frontend.layouts.golden.partials._footer',
        ], function ($view) {
            $socials = Social::activeCached();

            $view->with('socials', $socials);
        });
    }

    protected function composeNavigation()
    {
        View::composer([
            'frontend.layouts.partials._menu_list',
            'frontend.layouts.golden.partials._menu_list',
        ], function ($view) {
            $navigations = Navigation::activeCached();
            $view->with('navigations', $navigations);
        });
    }

    protected function composeFooter()
    {
        View::composer([
            'frontend.layouts.partials._footer',
            'frontend.layouts.golden.partials._footer',
        ], function ($view) {
            $footers = FooterSection::activeCached();
            $view->with('footers', $footers);
        });
    }

    private function composeAdminPermissions()
    {
        view()->composer('backend.layouts.partials._sidebar', function ($view) {
            if (Auth::guard('admin')->check() && ! session()->has('admin_permissions')) {
                session([
                    'admin_permissions' => Auth::guard('admin')->user()->getAllPermissions()->pluck('name')->toArray(),
                ]);
            }
        });

        View::composer('backend.layouts.partials._sidebar', function ($view) {
            $admin = Auth::guard('admin')->user();

            if ($admin && ! session()->has('admin_permissions')) {
                session([
                    'admin_permissions' => $admin->getAllPermissions()->pluck('name')->toArray(),
                ]);
            }

            $view->with('sidebarIndicators', AdminSidebarIndicatorManager::build(session('admin_permissions', [])));
        });
    }

    /**
     * Build the quick-function links for the authenticated user.
     *
     * Each link may declare a `feature` key; the Feature Management
     * system decides whether to keep or drop the link based on global
     * enable state and panel visibility rules. Links without a feature
     * key always render (e.g. transactions, support, profile).
     *
     * @return array<int, array{title: string, icon: string, link: string}>
     */
    private function getQuickLinks(): array
    {
        $links = [];

        if (auth()->user()->can('merchant')) {
            $links[] = ['Merchant', 'merchant', 'user.merchant.index', null];
        }

        if (auth()->user()->can('agent')) {
            $links[] = ['Agent Services', 'sidebar-agent', 'user.agent.index', null];
        }

        $mainLinks = [
            ['Wallet', 'wallet', 'user.wallet.index', null],
            ['Send Money', 'send-money', 'user.send-money.create', 'send_money'],
            ['Request Money', 'request-money', 'user.request-money.create', 'request_money'],
            ['Deposit', 'deposit', 'user.deposit.create', 'deposit_money'],
            ['Withdraw', 'withdraw', 'user.withdraw.create', 'withdraw_money'],
            ['History', 'history', 'user.transaction.index', null],
            ['Payment Links', 'linked', 'user.payment-links.index', 'payment_link'],
            ['Mobile Recharge', 'mobile-recharge', 'user.mobile-recharge.create', 'mobile_recharge'],
            ['Exchange', 'exchange', 'user.exchange-money.create', 'exchange_money'],
            ['Cards', 'card', 'user.virtual-card.index', 'virtual_card'],
            ['P2P Market', 'p2p_trading', 'user.p2p.offers.index', 'p2p_marketplace'],
            ['My Ads', 'list-2', 'user.p2p.offers.my', 'p2p_marketplace'],
            ['Create Ad', 'add', 'user.p2p.offers.create', 'p2p_marketplace'],
            ['Trades', 'history', 'user.p2p.orders.index', 'p2p_marketplace'],
            ['Voucher', 'voucher', 'user.voucher.my', 'vouchers'],
            ['Gift Cards', 'voucher', 'user.gift-card.index', 'gift_cards'],
            ['Referral', 'referrals', 'user.referral.index', 'referral_program'],
            ['Subscriptions', 'layer', 'user.subscription.plans', 'subscription_system'],
            ['Wallet Earn', 'trending-up', 'user.wallet-earn.plans', 'wallet_earn'],
            ['Support', 'support', 'user.support-ticket.index', null],
        ];

        $links   = array_merge($links, $mainLinks);
        $manager = app(FeatureManager::class);

        $filtered = array_filter(
            $links,
            function (array $link) use ($manager): bool {
                if ($link[3] === 'p2p_marketplace' && ! (bool) setting('p2p_enabled')) {
                    return false;
                }

                return $link[3] === null || $manager->isVisible($link[3]);
            }
        );

        return array_values(array_map(
            fn (array $link) => [
                'title' => __($link[0]),
                'icon'  => $link[1],
                'link'  => route($link[2]),
            ],
            $filtered
        ));
    }
}
