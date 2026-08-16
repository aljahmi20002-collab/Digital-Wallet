@extends('frontend.layouts.user.index')
@section('title', __('Settings'))
@section('content')
    @php
        $user = auth()->user();
        $featureManager = app(\App\Services\FeatureManager::class);
        $settingsTitle = match (true) {
            request()->routeIs('user.notifications.index') => __('Notifications'),
            request()->routeIs('user.settings.security.*') => __('Security Center'),
            request()->routeIs('user.settings.phone.*') => __('Phone Verification'),
            request()->routeIs('user.settings.2fa.*') => __('2FA Security'),
            request()->routeIs('user.settings.password.*') => __('Change Password'),
            request()->routeIs('user.settings.wallet-pin*') => __('Wallet PIN'),
            request()->routeIs('user.settings.subscription.status') => __('My Subscription'),
            request()->routeIs('user.settings.wallet-earn.status') => __('My Wallet Earn'),
            request()->routeIs('user.settings.kyc.*') => __('KYC Verification'),
            default => __('Profile Settings'),
        };

        $settingsSubtitle = match (true) {
            request()->routeIs('user.notifications.index') => __('Review account alerts and keep important activity visible in one feed.'),
            request()->routeIs('user.settings.security.*') => __('Review wallet protection, sign-in activity, and active access controls from one place.'),
            request()->routeIs('user.settings.phone.*') => __('Verify the phone number used for mobile recharge and SMS account alerts.'),
            request()->routeIs('user.settings.2fa.*') => __('Manage two-factor authentication and strengthen account access protection.'),
            request()->routeIs('user.settings.password.*') => __('Rotate your password and keep your sign-in credentials current.'),
            request()->routeIs('user.settings.wallet-pin*') => __('Create or reset the 6-digit PIN used to authorise wallet payments.'),
            request()->routeIs('user.settings.subscription.status') => __('Review your current active plan without leaving account settings.'),
            request()->routeIs('user.settings.wallet-earn.status') => __('Review active Wallet Earn positions and payout timing from settings.'),
            request()->routeIs('user.settings.kyc.*') => __('Submit, review, or update verification details required for account access.'),
            default => __('Update personal details, preferences, and default settings from one workspace.'),
        };

        $settingsIcon = match (true) {
            request()->routeIs('user.notifications.index') => 'fas fa-bell',
            request()->routeIs('user.settings.security.*') => 'fas fa-user-shield',
            request()->routeIs('user.settings.phone.*') => 'fas fa-mobile-screen-button',
            request()->routeIs('user.settings.2fa.*') => 'fas fa-shield-alt',
            request()->routeIs('user.settings.password.*') => 'fas fa-lock',
            request()->routeIs('user.settings.wallet-pin*') => 'fas fa-key',
            request()->routeIs('user.settings.subscription.status') => 'fas fa-layer-group',
            request()->routeIs('user.settings.wallet-earn.status') => 'fas fa-chart-line',
            request()->routeIs('user.settings.kyc.*') => 'fas fa-id-card',
            default => 'fas fa-user-cog',
        };

        $displayName = $user?->name ?: __('User');
        $initials = collect(explode(' ', trim((string) $displayName)))
            ->filter()
            ->map(fn (string $part): string => mb_substr($part, 0, 1))
            ->take(2)
            ->implode('');
        $initials = $initials !== '' ? mb_strtoupper($initials) : 'U';
        $roleTitle = $user?->role?->title() ?? __('User');
        $settingsSubscription = $user?->activeSubscription()->with('plan')->first();
        $settingsWalletEarnCount = $user?->walletEarnStakes()
            ->where('status', \App\Enums\WalletEarnStatus::Active)
            ->count() ?? 0;
    @endphp

    <div class="single-form-card settings-pro-card">
        <x-user-feature-header
            :title="$settingsTitle"
            :subtitle="$settingsSubtitle"
            :icon="$settingsIcon"
        />
        <div class="card-main settings-shell">
            @php
                $settingsNavItems = [
                    ['route' => 'user.settings.profile',         'match' => 'user.settings.profile',         'icon' => 'fas fa-user',       'label' => __('Profile'), 'hint' => __('Identity and contact details')],
                    ['route' => 'user.notifications.index',      'match' => 'user.notifications.index',      'icon' => 'fas fa-bell',       'label' => __('Notifications'), 'hint' => __('Alerts and account updates')],
                    ['route' => 'user.settings.security.index',  'match' => ['user.settings.security.*', 'user.settings.phone.*', 'user.settings.2fa.*', 'user.settings.password.*', 'user.settings.wallet-pin*'], 'icon' => 'fas fa-user-shield', 'label' => __('Security'), 'hint' => __('Protection, sessions, and credentials')],
                    [
                        'route' => 'user.settings.subscription.status',
                        'match' => 'user.settings.subscription.status',
                        'icon' => 'fas fa-layer-group',
                        'label' => __('My Subscription'),
                        'hint' => $settingsSubscription?->plan?->name ?? __('No active plan'),
                        'meta' => $settingsSubscription?->status?->label() ?? __('Inactive'),
                        'meta_class' => $settingsSubscription ? 'is-positive' : 'is-muted',
                        'feature' => 'subscription_system',
                    ],
                    [
                        'route' => 'user.settings.wallet-earn.status',
                        'match' => 'user.settings.wallet-earn.status',
                        'icon' => 'fas fa-chart-line',
                        'label' => __('My Wallet Earn'),
                        'hint' => $settingsWalletEarnCount > 0
                            ? trans_choice(':count active position|:count active positions', $settingsWalletEarnCount, ['count' => $settingsWalletEarnCount])
                            : __('No active stake'),
                        'meta' => $settingsWalletEarnCount > 0 ? __('Active') : __('Idle'),
                        'meta_class' => $settingsWalletEarnCount > 0 ? 'is-positive' : 'is-muted',
                    ],
                    ['route' => 'user.settings.kyc.verify',      'match' => 'user.settings.kyc.verify',      'icon' => 'fas fa-id-card',    'label' => __('KYC Verification'), 'hint' => __('Identity review status')],
                ];

                $settingsNavItems = array_values(array_filter(
                    $settingsNavItems,
                    fn (array $item): bool => ! isset($item['feature']) || $featureManager->isVisible($item['feature'])
                ));
            @endphp

            <div class="settings-shell__grid">
                <aside class="settings-sidebar" aria-label="{{ __('Account settings summary') }}">
                    <section class="settings-profile-card">
                        <div class="settings-profile-card__avatar">
                            @if($user && ! empty($user->avatar))
                                <img src="{{ asset($user->avatar_alt) }}" alt="{{ $displayName }}" loading="lazy">
                            @else
                                <span>{{ $initials }}</span>
                            @endif
                        </div>
                        <div class="settings-profile-card__body">
                            <span class="settings-profile-card__eyebrow">{{ __('Account Control') }}</span>
                            <h5 class="settings-profile-card__name">{{ $displayName }}</h5>
                            <p class="settings-profile-card__meta">{{ $user?->email }}</p>
                        </div>
                        <span class="settings-profile-card__role">
                            <i class="fas fa-user-tag" aria-hidden="true"></i>
                            {{ $roleTitle }}
                        </span>
                    </section>

                    <nav class="settings-nav" aria-label="{{ __('Account settings navigation') }}">
                        @foreach($settingsNavItems as $item)
                            @php
                                $matches = (array) $item['match'];
                                $isActive = request()->routeIs(...$matches);
                            @endphp
                            <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                               class="settings-nav__link @if($isActive) is-active @endif"
                               @if($isActive) aria-current="page" @endif>
                                <span class="settings-nav__icon" aria-hidden="true">
                                    <i class="{{ $item['icon'] }}"></i>
                                </span>
                                <span class="settings-nav__body">
                                    <span class="settings-nav__label">{{ $item['label'] }}</span>
                                    <span class="settings-nav__hint">{{ $item['hint'] }}</span>
                                </span>
                                @if(isset($item['meta']))
                                    <span class="settings-nav__meta {{ $item['meta_class'] ?? '' }}">
                                        {{ $item['meta'] }}
                                    </span>
                                @endif
                                @if($isActive)
                                    <span class="settings-nav__active-dot" aria-hidden="true"></span>
                                @endif
                            </a>
                        @endforeach

                        <a href="#"
                           class="settings-nav__link settings-nav__link--danger d-lg-none"
                           onclick="event.preventDefault(); document.getElementById('user-settings-logout').submit();">
                            <span class="settings-nav__icon" aria-hidden="true">
                                <i class="fas fa-right-from-bracket"></i>
                            </span>
                            <span class="settings-nav__body">
                                <span class="settings-nav__label">{{ __('Logout') }}</span>
                                <span class="settings-nav__hint">{{ __('End this session') }}</span>
                            </span>
                        </a>
                    </nav>

                    <form id="user-settings-logout" method="POST" action="{{ route('user.logout') }}" class="d-none">
                        @csrf
                    </form>
                </aside>

                <section class="settings-content settings-content--panel" aria-label="{{ $settingsTitle }}">
                    <header class="settings-content__header">
                        <div class="settings-content__heading">
                            <span class="settings-content__eyebrow">{{ __('Settings Workspace') }}</span>
                            <h5 class="settings-content__title">{{ $settingsTitle }}</h5>
                        </div>
                        <div class="settings-content__actions">
                            @hasSection('user_setting_actions')
                                @yield('user_setting_actions')
                            @endif
                            <span class="settings-content__badge">
                                <i class="{{ $settingsIcon }}" aria-hidden="true"></i>
                                {{ __('Active') }}
                            </span>
                        </div>
                    </header>

                    <div class="settings-content__scroll" data-settings-content-scroll>
                        @yield('user_setting_content')
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
