@php
    use App\Enums\KycStatus;
    use App\Enums\WalletEarnStatus;

    $authUser = auth()->user();
    $featureManager = app(\App\Services\FeatureManager::class);
    $initials = collect(explode(' ', trim($authUser->name)))
        ->filter()
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $isKycVerified = $authUser->kycSubmission?->status === KycStatus::APPROVED;
    $identityVerificationTone = $isKycVerified ? 'verified' : 'unverified';
    $identityVerificationLabel = $isKycVerified ? __('Verified') : __('Unverified');
    $identityVerificationAriaLabel = $isKycVerified ? __('Identity verified') : __('Identity unverified');
    $activeSubscription = $authUser->activeSubscription()->with('plan')->first();
    $activeWalletEarnStake = $authUser->walletEarnStakes()
        ->where('status', WalletEarnStatus::Active)
        ->latest('starts_at')
        ->first();
    $activeWalletEarnCount = $authUser->walletEarnStakes()
        ->where('status', WalletEarnStatus::Active)
        ->count();
@endphp

<div class="ph-profile" data-ph-profile>
    <button type="button"
            class="ph-profile__btn"
            data-ph-profile-toggle
            data-verification-tone="{{ $identityVerificationTone }}"
            aria-haspopup="true"
            aria-expanded="false">
        <span class="ph-avatar">
            @if($authUser->avatar_alt)
                <img src="{{ asset($authUser->avatar_alt) }}" alt="{{ $authUser->name }}" loading="lazy">
            @else
                {{ $initials ?: 'U' }}
            @endif
        </span>
        <span class="ph-profile__text">
            <span class="ph-profile__name">{{ $authUser->name }}</span>
            <span class="ph-profile__verification ph-profile__verification--{{ $identityVerificationTone }}"
                  aria-label="{{ $identityVerificationAriaLabel }}">
                <span>{{ $identityVerificationLabel }}</span>
            </span>
        </span>
        <svg class="ph-profile__chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </button>

    <div class="ph-profile__menu" role="menu">
        <section class="ph-profile__menu-card" aria-label="{{ __('Profile summary') }}">
            <span class="ph-profile__menu-avatar">
                @if($authUser->avatar_alt)
                    <img src="{{ asset($authUser->avatar_alt) }}" alt="{{ $authUser->name }}" loading="lazy">
                @else
                    {{ $initials ?: 'U' }}
                @endif
            </span>
            <div class="ph-profile__menu-user">
                <h4>{{ $authUser->name }}</h4>
                <div class="ph-profile__menu-badges">
                    @if($authUser->rank)
                        <a class="ph-profile__menu-badge ph-profile__menu-badge--rank" href="{{ route('user.rank.showcase') }}">
                            <i class="fas fa-star" aria-hidden="true"></i>
                            {{ __(':rank Member', ['rank' => $authUser->rank->name]) }}
                        </a>
                    @endif
                </div>
            </div>
        </section>

        <div class="ph-profile__menu-list">
            <a href="{{ route('user.settings.profile') }}" class="ph-profile__menu-item" role="menuitem">
                <span class="ph-profile__menu-icon"><x-icon name="user-cog"/></span>
                <span class="ph-profile__menu-copy">
                    <span class="ph-profile__menu-label">{{ __('My Settings') }}</span>
                    <span class="ph-profile__menu-hint">{{ __('Manage your account settings') }}</span>
                </span>
                <i class="fas fa-chevron-right ph-profile__menu-arrow" aria-hidden="true"></i>
            </a>
            <a href="{{ route('user.notifications.index') }}" class="ph-profile__menu-item" role="menuitem">
                <span class="ph-profile__menu-icon"><x-icon name="notification"/></span>
                <span class="ph-profile__menu-copy">
                    <span class="ph-profile__menu-label">{{ __('Notifications') }}</span>
                    <span class="ph-profile__menu-hint">{{ __('View and manage alerts') }}</span>
                </span>
                <i class="fas fa-chevron-right ph-profile__menu-arrow" aria-hidden="true"></i>
            </a>
            <a href="{{ route('user.wallet.index') }}" class="ph-profile__menu-item" role="menuitem">
                <span class="ph-profile__menu-icon"><x-icon name="wallet-2"/></span>
                <span class="ph-profile__menu-copy">
                    <span class="ph-profile__menu-label">{{ __('My Wallets') }}</span>
                    <span class="ph-profile__menu-hint">{{ __('Manage your wallets') }}</span>
                </span>
                <i class="fas fa-chevron-right ph-profile__menu-arrow" aria-hidden="true"></i>
            </a>
            <a href="{{ route('user.support-ticket.index') }}" class="ph-profile__menu-item" role="menuitem">
                <span class="ph-profile__menu-icon"><x-icon name="ticket"/></span>
                <span class="ph-profile__menu-copy">
                    <span class="ph-profile__menu-label">{{ __('Support Ticket') }}</span>
                    <span class="ph-profile__menu-hint">{{ __('Get help and support') }}</span>
                </span>
                <i class="fas fa-chevron-right ph-profile__menu-arrow" aria-hidden="true"></i>
            </a>

            <div class="ph-profile__menu-divider" aria-hidden="true"></div>

            <div class="ph-profile__status-panel" aria-label="{{ __('Account product status') }}">
                @if($featureManager->isVisible('subscription_system'))
                    <a href="{{ route('user.subscription.current') }}" class="ph-profile__status-item {{ $activeSubscription ? 'is-active' : '' }}">
                        <span class="ph-profile__status-icon"><i class="fas fa-layer-group" aria-hidden="true"></i></span>
                        <span class="ph-profile__status-copy">
                            <span class="ph-profile__status-label">{{ __('Subscription') }}</span>
                            <span class="ph-profile__status-value">
                                {{ $activeSubscription?->plan?->name ?? __('No active plan') }}
                            </span>
                        </span>
                        <span class="ph-profile__status-pill">
                            {{ $activeSubscription?->status?->label() ?? __('Inactive') }}
                        </span>
                    </a>
                @endif

                <a href="{{ route('user.wallet-earn.stakes') }}" class="ph-profile__status-item {{ $activeWalletEarnCount > 0 ? 'is-active' : '' }}">
                    <span class="ph-profile__status-icon"><i class="fas fa-chart-line" aria-hidden="true"></i></span>
                    <span class="ph-profile__status-copy">
                        <span class="ph-profile__status-label">{{ __('Wallet Earn') }}</span>
                        <span class="ph-profile__status-value">
                            @if($activeWalletEarnCount > 0)
                                {{ trans_choice(':count active position|:count active positions', $activeWalletEarnCount, ['count' => $activeWalletEarnCount]) }}
                            @else
                                {{ __('No active stake') }}
                            @endif
                        </span>
                    </span>
                    <span class="ph-profile__status-pill">
                        {{ $activeWalletEarnStake?->status?->label() ?? __('Idle') }}
                    </span>
                </a>
            </div>

            <div class="ph-profile__menu-divider" aria-hidden="true"></div>

            <a href="#" class="ph-profile__menu-item is-logout" role="menuitem"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="ph-profile__menu-icon"><x-icon name="logout"/></span>
                <span class="ph-profile__menu-copy">
                    <span class="ph-profile__menu-label">{{ __('Logout') }}</span>
                    <span class="ph-profile__menu-hint">{{ __('Sign out from your account') }}</span>
                </span>
                <i class="fas fa-chevron-right ph-profile__menu-arrow" aria-hidden="true"></i>
            </a>
        </div>

        <form id="logout-form" method="POST" action="{{ route('user.logout') }}">
            @csrf
        </form>
    </div>
</div>

@push('scripts')
    <script>
        (function () {
            'use strict';

            document.querySelectorAll('[data-ph-profile]').forEach(function (root) {
                const toggle = root.querySelector('[data-ph-profile-toggle]');

                if (!toggle) {
                    return;
                }

                toggle.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const isOpen = root.classList.toggle('is-open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });

                document.addEventListener('click', function (event) {
                    if (!root.contains(event.target)) {
                        root.classList.remove('is-open');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape' && root.classList.contains('is-open')) {
                        root.classList.remove('is-open');
                        toggle.setAttribute('aria-expanded', 'false');
                        toggle.focus();
                    }
                });
            });
        })();
    </script>
@endpush
