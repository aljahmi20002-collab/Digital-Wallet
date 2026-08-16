@php
    $securityTabs = [
        [
            'route' => 'user.settings.security.index',
            'match' => 'user.settings.security.*',
            'icon' => 'fas fa-shield-alt',
            'label' => __('Overview'),
            'hint' => __('Access status'),
        ],
        [
            'route' => 'user.settings.phone.verify',
            'match' => 'user.settings.phone.*',
            'icon' => 'fas fa-mobile-screen-button',
            'label' => __('Phone'),
            'hint' => __('SMS protection'),
        ],
        [
            'route' => 'user.settings.2fa.setup',
            'match' => 'user.settings.2fa.*',
            'icon' => 'fas fa-user-shield',
            'label' => __('2FA'),
            'hint' => __('Authenticator'),
        ],
        [
            'route' => 'user.settings.password.change',
            'match' => 'user.settings.password.*',
            'icon' => 'fas fa-lock',
            'label' => __('Password'),
            'hint' => __('Login secret'),
        ],
        [
            'route' => 'user.settings.wallet-pin',
            'match' => 'user.settings.wallet-pin*',
            'icon' => 'fas fa-key',
            'label' => __('Wallet PIN'),
            'hint' => __('Payment PIN'),
        ],
    ];
@endphp

<nav class="security-suite-tabs" aria-label="{{ __('Security settings') }}">
    @foreach($securityTabs as $tab)
        @php $isActive = request()->routeIs(...(array) $tab['match']); @endphp
        <a href="{{ route($tab['route']) }}"
           class="security-suite-tabs__item @if($isActive) is-active @endif"
           @if($isActive) aria-current="page" @endif>
            <span class="security-suite-tabs__icon" aria-hidden="true">
                <i class="{{ $tab['icon'] }}"></i>
            </span>
            <span class="security-suite-tabs__copy">
                <span class="security-suite-tabs__label">{{ $tab['label'] }}</span>
                <span class="security-suite-tabs__hint">{{ $tab['hint'] }}</span>
            </span>
        </a>
    @endforeach
</nav>
