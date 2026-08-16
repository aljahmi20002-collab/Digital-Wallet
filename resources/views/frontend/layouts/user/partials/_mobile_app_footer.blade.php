@php
    $mobileFooterUser = auth()->user();
    $mobileFooterIsAgent = $mobileFooterUser?->can('agent') && $featureManager->isVisible('agent_program');
    $mobileFooterIsMerchant = $mobileFooterUser?->can('merchant');
    $mobileFooterPwa = app(\App\Http\Controllers\Frontend\PwaController::class);
    $mobileFooterTabFallback = [
        'label' => __('Wallet'),
        'route' => 'user.wallet.index',
        'params' => [],
        'icon' => 'fa fa-wallet',
        'active' => request()->routeIs('user.wallet.*'),
    ];
    $mobileFooterResolveTab = static function (array $tab) use ($featureManager, $mobileFooterTabFallback): array {
        if (isset($tab['feature']) && ! $featureManager->isVisible($tab['feature'])) {
            $tab = $tab['fallback'] ?? $mobileFooterTabFallback;
        }

        if (isset($tab['feature']) && ! $featureManager->isVisible($tab['feature'])) {
            $tab = $mobileFooterTabFallback;
        }

        if (! isset($tab['action']) && (! isset($tab['route']) || ! \Illuminate\Support\Facades\Route::has($tab['route']))) {
            $tab = $mobileFooterTabFallback;
        }

        return $tab + ['params' => []];
    };
    $mobileFooterRoleTabs = match (true) {
        (bool) $mobileFooterIsAgent => [
            [
                'label' => __('Agent'),
                'route' => 'user.agent.index',
                'icon' => 'fa fa-briefcase',
                'active' => request()->routeIs('user.agent.*') && ! in_array(request('tab'), ['cash-in', 'counter-cashout', 'cash-out-requests'], true),
            ],
            [
                'label' => __('Cash In'),
                'route' => 'user.agent.index',
                'params' => ['tab' => 'cash-in'],
                'icon' => 'fa fa-wallet',
                'active' => request()->routeIs('user.agent.*') && request('tab') === 'cash-in',
            ],
            [
                'label' => __('Cash Out'),
                'route' => 'user.agent.index',
                'params' => ['tab' => 'counter-cashout'],
                'icon' => 'fa fa-qrcode',
                'active' => request()->routeIs('user.agent.*') && in_array(request('tab'), ['counter-cashout', 'cash-out-requests'], true),
            ],
        ],
        (bool) $mobileFooterIsMerchant => [
            [
                'label' => __('Merchant'),
                'route' => 'user.merchant.index',
                'icon' => 'fa fa-store',
                'active' => request()->routeIs('user.merchant.*'),
            ],
            [
                'label' => __('Pay Links'),
                'route' => 'user.payment-links.index',
                'icon' => 'fa fa-link',
                'active' => request()->routeIs('user.payment-links.*'),
                'feature' => 'payment_link',
                'fallback' => $mobileFooterTabFallback,
            ],
            [
                'label' => __('Sales'),
                'route' => 'user.transaction.index',
                'params' => ['type' => 'receive_payment'],
                'icon' => 'fa fa-chart-line',
                'active' => request()->routeIs('user.transaction.*') && request('type') === 'receive_payment',
            ],
        ],
        default => [
            [
                'label' => __('Wallet'),
                'route' => 'user.wallet.index',
                'icon' => 'fa fa-wallet',
                'active' => request()->routeIs('user.wallet.*'),
            ],
            [
                'label' => __('QR Scan'),
                'action' => 'qr-scan',
                'icon' => 'fa fa-qrcode',
                'active' => false,
            ],
            [
                'label' => __('History'),
                'route' => 'user.transaction.index',
                'icon' => 'fa fa-clock-rotate-left',
                'active' => request()->routeIs('user.transaction.*'),
            ],
        ],
    };
    $mobileFooterTabs = array_map($mobileFooterResolveTab, $mobileFooterRoleTabs);
    $mobileMoreAppName = $mobileFooterPwa->appName();
    $mobileMoreAppIcon = $mobileFooterPwa->iconUrl('icon_192');
    $mobileMoreWorkspace = $mobileFooterIsAgent || $mobileFooterIsMerchant ? null : [
        'label' => __('Switch workspace'),
        'route' => 'user.wallet.index',
        'icon' => 'fas fa-wallet',
        'tone' => 'dark',
        'sub' => __('Currently: Personal Wallet'),
    ];
@endphp

{{-- ==========================================================
 | Mobile Bottom Tab Bar with center FAB -> Dashboard
 | Visible < 992px only.
 ========================================================== --}}
<nav class="dk-tabbar d-lg-none" data-hidden="0">
    <div class="dk-tabbar__inner">
        @foreach(array_slice($mobileFooterTabs, 0, 2) as $mobileFooterTab)
            @if(($mobileFooterTab['action'] ?? null) === 'qr-scan')
                <button type="button"
                        class="dk-tab"
                        data-active="0"
                        data-dk-qr-scanner-open
                        aria-label="{{ __('QR Scan') }}">
                    <i class="{{ $mobileFooterTab['icon'] }}"></i>
                    <span class="dk-tab__lb">{{ $mobileFooterTab['label'] }}</span>
                </button>
            @else
                <a href="{{ route($mobileFooterTab['route'], $mobileFooterTab['params'] ?? []) }}"
                   class="dk-tab {{ $mobileFooterTab['active'] ? 'is-active' : '' }}"
                   data-active="{{ $mobileFooterTab['active'] ? '1' : '0' }}"
                   @if($mobileFooterTab['active']) aria-current="page" @endif>
                    <i class="{{ $mobileFooterTab['icon'] }}"></i>
                    <span class="dk-tab__lb">{{ $mobileFooterTab['label'] }}</span>
                </a>
            @endif
        @endforeach

        <div class="dk-fab-slot">
            <div class="dk-fab__ring"></div>
            <a class="dk-fab" href="{{ route('user.dashboard') }}" aria-label="{{ __('Home') }}">
                <i class="fa fa-house"></i>
            </a>
        </div>

        @foreach(array_slice($mobileFooterTabs, 2, 1) as $mobileFooterTab)
            @if(($mobileFooterTab['action'] ?? null) === 'qr-scan')
                <button type="button"
                        class="dk-tab"
                        data-active="0"
                        data-dk-qr-scanner-open
                        aria-label="{{ __('QR Scan') }}">
                    <i class="{{ $mobileFooterTab['icon'] }}"></i>
                    <span class="dk-tab__lb">{{ $mobileFooterTab['label'] }}</span>
                </button>
            @else
                <a href="{{ route($mobileFooterTab['route'], $mobileFooterTab['params'] ?? []) }}"
                   class="dk-tab {{ $mobileFooterTab['active'] ? 'is-active' : '' }}"
                   data-active="{{ $mobileFooterTab['active'] ? '1' : '0' }}"
                   @if($mobileFooterTab['active']) aria-current="page" @endif>
                    <i class="{{ $mobileFooterTab['icon'] }}"></i>
                    <span class="dk-tab__lb">{{ $mobileFooterTab['label'] }}</span>
                </a>
            @endif
        @endforeach

        <button type="button" class="dk-tab dk-open-more" aria-label="{{ __('More') }}">
            <i class="fa fa-ellipsis"></i>
            <span class="dk-tab__lb">{{ __('More') }}</span>
        </button>
    </div>
</nav>

@if(setting('pwa_enabled', true))
{{-- PWA install prompt for mobile user app pages. --}}
<div class="dk-pwa-prompt d-lg-none" hidden data-dk-pwa-prompt role="dialog" aria-live="polite" aria-label="{{ __('Install app') }}">
    <div class="dk-pwa-prompt__icon" aria-hidden="true">
        <img src="{{ $mobileMoreAppIcon }}" alt="" loading="lazy">
    </div>
    <div class="dk-pwa-prompt__body">
        <div class="dk-pwa-prompt__title">{{ __('Install App') }}</div>
        <div class="dk-pwa-prompt__text" data-dk-pwa-prompt-text>{{ __('Add :name to your home screen for faster access.', ['name' => $mobileMoreAppName]) }}</div>
    </div>
    <div class="dk-pwa-prompt__actions">
        <button type="button" class="dk-pwa-prompt__install" data-dk-pwa-install>{{ __('Install') }}</button>
        <button type="button" class="dk-pwa-prompt__dismiss" data-dk-pwa-dismiss aria-label="{{ __('Not now') }}">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<div class="dk-sheet-backdrop dk-pwa-help-backdrop d-lg-none" data-dk-pwa-help-close></div>
<div class="dk-sheet dk-pwa-help-sheet d-lg-none" role="dialog" aria-label="{{ __('Install app help') }}" aria-hidden="true" data-dk-pwa-help>
    <div class="dk-sheet__handle"></div>
    <div class="dk-sheet__head">
        <h3 class="dk-sheet__title">{{ __('Install as a mobile app') }}</h3>
        <button type="button" class="dk-icon-btn dk-sheet-close" data-dk-pwa-help-close aria-label="{{ __('Close') }}"><i class="fas fa-times"></i></button>
    </div>
    <div class="dk-pwa-help__body">
        <p class="dk-pwa-help__note" data-dk-pwa-help-note>{{ __('If the browser install dialog does not open, use your browser menu to add this app manually.') }}</p>
        <ol class="dk-pwa-help__steps">
            <li data-dk-pwa-help-step-one>{{ __('Tap the browser menu button.') }}</li>
            <li data-dk-pwa-help-step-two>{{ __('Tap Install app or Add to Home screen.') }}</li>
            <li data-dk-pwa-help-step-three>{{ __('Tap Add or Install to place the app icon on your home screen.') }}</li>
        </ol>
    </div>
</div>
@endif

{{-- More services bottom sheet --}}
<div class="dk-sheet dk-more-sheet d-lg-none" role="dialog" aria-label="{{ __('More services') }}" aria-hidden="true">
    <div class="dk-sheet__handle"></div>
    <div class="dk-more-menu">
        <div class="dk-more-menu__head">
            <h3 class="dk-more-menu__title">{{ __('More') }}</h3>
            <button type="button" class="dk-more-menu__close dk-sheet-close" aria-label="{{ __('Close') }}"><i class="fas fa-times"></i></button>
        </div>

        @if(setting('pwa_enabled', true))
            <button type="button" class="dk-more-install" data-dk-pwa-install data-dk-pwa-install-persistent data-dk-pwa-install-card aria-label="{{ __('Install :name app', ['name' => $mobileMoreAppName]) }}">
                <span class="dk-more-install__icon">
                    <img src="{{ $mobileMoreAppIcon }}" alt="" loading="lazy">
                </span>
                <div class="dk-more-install__copy">
                    <strong>{{ __('Install :name app', ['name' => $mobileMoreAppName]) }}</strong>
                    <span>{{ __('One tap install - works offline') }}</span>
                </div>
                <span class="dk-more-install__button">{{ __('Install') }}</span>
            </button>
        @endif

        @php
            $moreItems = $mobileMoreWorkspace ? [$mobileMoreWorkspace] : [];

            $moreItems[] = [
                'label' => __('KYC & Security'),
                'route' => 'user.settings.kyc.verify',
                'icon' => 'fas fa-shield-alt',
                'tone' => 'warning',
                'sub' => __('Identity & account protection'),
            ];

            $moreItems[] = [
                'label' => __('Virtual Cards'),
                'route' => 'user.virtual-card.index',
                'icon' => 'fas fa-credit-card',
                'tone' => 'rose',
                'sub' => __('Manage active cards'),
                'feature' => 'virtual_card',
            ];

            if ($mobileFooterUser?->can('agent')) {
                $moreItems[] = [
                    'label' => __('Agent Services'),
                    'route' => 'user.agent.index',
                    'icon' => 'fas fa-briefcase',
                    'tone' => 'violet',
                    'sub' => __('Cash-in, QR cash-out & queue'),
                    'feature' => 'agent_program',
                ];
            }

            if ($mobileFooterUser?->can('merchant')) {
                $moreItems[] = [
                    'label' => __('Merchant Tools'),
                    'route' => 'user.merchant.index',
                    'icon' => 'fas fa-store',
                    'tone' => 'violet',
                    'sub' => __('Pay links & checkout'),
                ];
            }

            $moreItems[] = [
                'label' => __('Support'),
                'route' => 'user.support-ticket.index',
                'icon' => 'fas fa-life-ring',
                'tone' => 'blue',
                'sub' => __('Tickets & help'),
            ];

            $moreItems[] = [
                'label' => __('Settings'),
                'route' => 'user.settings.profile',
                'icon' => 'fas fa-cog',
                'tone' => 'slate',
                'sub' => __('Profile, security, alerts'),
            ];

            $moreItems = array_values(array_filter(
                $moreItems,
                fn (array $item): bool => \Illuminate\Support\Facades\Route::has($item['route'])
                    && (! isset($item['feature']) || $featureManager->isVisible($item['feature']))
                    && (! isset($item['setting']) || setting($item['setting']))
            ));

            $mobileMoreReferralCard = null;

            if (
                $featureManager->isVisible('referral_program')
                && \Illuminate\Support\Facades\Route::has('user.referral.index')
            ) {
                $mobileMoreReferralCard = [
                    'label' => __('Referral Boost'),
                    'title' => __('Invite friends & earn'),
                    'sub' => __('Share your link and earn rewards'),
                    'route' => 'user.referral.index',
                ];
            }

            $mobileMoreHighlights = [];

            if (
                $mobileFooterUser
                && $featureManager->isVisible('wallet_earn')
            ) {
                $activeEarnQuery = $mobileFooterUser->walletEarnStakes()
                    ->where('status', \App\Enums\WalletEarnStatus::Active->value);
                $activeEarnCount = (clone $activeEarnQuery)->count();
                $activeEarnAmount = (float) (clone $activeEarnQuery)->sum('principal_amount');
                $pendingEarnCount = $mobileFooterUser->walletEarnStakes()
                    ->where('status', \App\Enums\WalletEarnStatus::Pending->value)
                    ->count();
                $walletEarnState = match (true) {
                    $activeEarnCount > 0 => 'active',
                    $pendingEarnCount > 0 => 'pending',
                    default => 'inactive',
                };

                $mobileMoreHighlights[] = [
                    'label' => __('Wallet Earn'),
                    'icon' => 'fas fa-chart-line',
                    'tone' => 'earn',
                    'state' => $walletEarnState,
                    'sub' => match ($walletEarnState) {
                        'active' => __('Principal :amount', ['amount' => siteCurrency('symbol').number_format($activeEarnAmount, 2)]),
                        'pending' => trans_choice(':count request in review|:count requests in review', $pendingEarnCount, ['count' => $pendingEarnCount]),
                        default => __('No active earning position'),
                    },
                    'value' => match ($walletEarnState) {
                        'active' => __('Active'),
                        'pending' => __('Pending'),
                        default => __('Inactive'),
                    },
                    'meta' => match ($walletEarnState) {
                        'active' => trans_choice(':count position|:count positions', $activeEarnCount, ['count' => $activeEarnCount]),
                        'pending' => __('Review'),
                        default => __('Status'),
                    },
                ];
            }

            if (
                $mobileFooterUser
                && $featureManager->isVisible('subscription_system')
            ) {
                $activeSubscription = $mobileFooterUser->activeSubscription()
                    ->with('plan')
                    ->first();
                $currentSubscription = $activeSubscription
                    ?? $mobileFooterUser->subscriptions()
                        ->with('plan')
                        ->latest()
                        ->first();
                $subscriptionDays = $activeSubscription?->daysRemaining();
                $subscriptionState = $currentSubscription?->status?->value ?? 'inactive';

                $mobileMoreHighlights[] = [
                    'label' => __('Subscription'),
                    'icon' => 'fas fa-layer-group',
                    'tone' => 'subscription',
                    'state' => $subscriptionState,
                    'sub' => $currentSubscription?->plan?->name ?? __('No active plan'),
                    'value' => $currentSubscription?->status?->label() ?? __('Inactive'),
                    'meta' => match (true) {
                        $activeSubscription && $subscriptionDays === null => __('Unlimited'),
                        $activeSubscription !== null => trans_choice(':count day left|:count days left', $subscriptionDays, ['count' => $subscriptionDays]),
                        $currentSubscription !== null => __('Last status'),
                        default => __('Status'),
                    },
                ];
            }
        @endphp

        @if($mobileMoreHighlights !== [])
            <section class="dk-more-overview" aria-label="{{ __('Current status') }}">
                <div class="dk-more-overview__head">
                    <span>{{ __('For you') }}</span>
                    <small>{{ __('Current status') }}</small>
                </div>
                <div class="dk-more-overview__grid">
                    @foreach($mobileMoreHighlights as $highlight)
                        <div class="dk-more-overview-card" data-tone="{{ $highlight['tone'] }}" data-state="{{ $highlight['state'] }}">
                            <span class="dk-more-overview-card__icon">
                                <i class="{{ $highlight['icon'] }}"></i>
                            </span>
                            <span class="dk-more-overview-card__copy">
                                <strong>{{ $highlight['label'] }}</strong>
                                <small>{{ $highlight['sub'] }}</small>
                            </span>
                            <span class="dk-more-overview-card__stat">
                                <b>{{ $highlight['value'] }}</b>
                                <em>{{ $highlight['meta'] }}</em>
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="dk-more-menu__list">
            @foreach($moreItems as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="dk-more-menu__item">
                    <span class="dk-more-menu__icon dk-more-menu__icon--{{ $item['tone'] }}">
                        <i class="{{ $item['icon'] }}"></i>
                    </span>
                    <div class="dk-more-menu__copy">
                        <strong>{{ $item['label'] }}</strong>
                        <small>{{ $item['sub'] }}</small>
                    </div>
                    <i class="fas fa-chevron-right dk-more-menu__arrow"></i>
                </a>
            @endforeach

            <form action="{{ route('user.logout') }}" method="POST" class="dk-more-menu__form">
                @csrf
                <button type="submit" class="dk-more-menu__item dk-more-menu__item--button">
                    <span class="dk-more-menu__icon dk-more-menu__icon--danger">
                        <i class="fas fa-sign-out-alt"></i>
                    </span>
                    <div class="dk-more-menu__copy">
                        <strong>{{ __('Logout') }}</strong>
                        <small>{{ __('Sign out of this device') }}</small>
                    </div>
                    <i class="fas fa-chevron-right dk-more-menu__arrow"></i>
                </button>
            </form>

            @if($mobileMoreReferralCard)
                <a href="{{ route($mobileMoreReferralCard['route']) }}" class="dk-more-referral-card" aria-label="{{ $mobileMoreReferralCard['title'] }}">
                    <span class="dk-more-referral-card__media">
                        <x-icon name="gift" width="42" height="32" class="dk-more-referral-card__gift"/>
                        <span class="dk-more-referral-card__person" aria-hidden="true">
                            <x-icon name="user" width="9" height="9" class="dk-more-referral-card__person-icon"/>
                        </span>
                    </span>
                    <span class="dk-more-referral-card__copy">
                        <span class="dk-more-referral-card__badge">
                            <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            {{ $mobileMoreReferralCard['label'] }}
                        </span>
                        <strong>{{ $mobileMoreReferralCard['title'] }}</strong>
                        <small>{{ $mobileMoreReferralCard['sub'] }}</small>
                    </span>
                    <span class="dk-more-referral-card__go" aria-hidden="true">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </a>
            @endif
        </div>
    </div>
</div>
