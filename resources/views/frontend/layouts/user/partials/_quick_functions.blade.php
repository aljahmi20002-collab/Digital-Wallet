@php
    $quickFunctionAccentMap = [
        'card' => 'indigo',
        'deposit' => 'emerald',
        'history' => 'sky',
        'withdraw' => 'rose',
        'exchange' => 'amber',
        'p2p_trading' => 'violet',
        'list-2' => 'slate',
        'add' => 'teal',
        'wallet' => 'cyan',
        'send-money' => 'blue',
        'request-money' => 'green',
        'mobile-recharge' => 'teal',
        'voucher' => 'orange',
        'referrals' => 'pink',
        'support' => 'cyan',
        'merchant' => 'purple',
        'sidebar-agent' => 'violet',
        'linked' => 'fuchsia',
        'qrcode' => 'zinc',
        'layer' => 'indigo',
        'trending-up' => 'emerald',
    ];

    $quickFunctionTitleMap = [
        'card' => __('My Cards'),
        'deposit' => __('Add Money'),
        'history' => __('Transaction History'),
        'send-money' => __('Send Money'),
        'request-money' => __('Request Money'),
        'linked' => __('Payment Links'),
        'sidebar-agent' => __('Agent Services'),
        'list-2' => __('My Ads'),
        'support' => __('Support Ticket'),
        'layer' => __('Subscriptions'),
        'trending-up' => __('Wallet Earn'),
    ];

    $quickFunctionOrder = [
        'wallet' => 10,
        'send-money' => 20,
        'request-money' => 30,
        'deposit' => 40,
        'withdraw' => 50,
        'history' => 60,
        'sidebar-agent' => 70,
        'linked' => 80,
        'mobile-recharge' => 90,
        'exchange' => 100,
        'card' => 110,
        'voucher' => 120,
        'referrals' => 130,
        'p2p_trading' => 135,
        'list-2' => 140,
        'layer' => 150,
        'trending-up' => 160,
        'support' => 170,
        'merchant' => 180,
        'add' => 190,
    ];

    $quickFunctionLinks = array_values(array_merge($quickLinksMain ?? [], $quickLinksMore ?? []));
    usort($quickFunctionLinks, fn (array $left, array $right): int => ($quickFunctionOrder[$left['icon']] ?? 999) <=> ($quickFunctionOrder[$right['icon']] ?? 999));

    $quickFunctionUser = auth()->user();
    $quickFunctionDefaultWallet = $quickFunctionUser?->default_wallet;
    $quickFunctionDefaultCurrency = $quickFunctionDefaultWallet?->currency;
    $quickFunctionDefaultSymbol = $quickFunctionDefaultCurrency?->symbol ?? siteCurrency('symbol');
    $quickFunctionDefaultCode = $quickFunctionDefaultCurrency?->code ?? siteCurrency();
    $quickFunctionDefaultBalance = $quickFunctionDefaultWallet
        ? $quickFunctionDefaultSymbol.' '.number_format((float) $quickFunctionDefaultWallet->balance, 2)
        : __('No wallet yet');
@endphp

<div id="{{ $dropdownId ?? 'quickFunctionDropdown' }}" class="quick-function-dropdown quick-function-menu" role="menu" aria-label="{{ __('Quick Functions') }}">
    <div class="quick-function-menu__header">
        <span class="quick-function-menu__header-mark" aria-hidden="true">
            <x-icon name="apps" height="18" width="18" />
        </span>

        <div class="quick-function-menu__heading-group">
            <h6 class="quick-function-menu__heading">{{ __('Quick Menu') }}</h6>
            <span class="quick-function-menu__subtitle">{{ __('Wallet Dashboard') }}</span>
        </div>

        <button type="button" class="quick-function-menu__close" data-quick-function-close aria-label="{{ __('Close') }}">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    </div>

    <div class="quick-function-menu__body" id="quickFunctionList_{{ $dropdownId ?? 'default' }}">
        <div class="quick-function-menu__feature-row">
            <a href="{{ route('user.wallet.index') }}" class="quick-function-menu__wallet-card" role="menuitem">
                <span class="quick-function-menu__wallet-art" aria-hidden="true">
                    <span class="quick-function-menu__wallet-paper quick-function-menu__wallet-paper--one"></span>
                    <span class="quick-function-menu__wallet-paper quick-function-menu__wallet-paper--two"></span>
                    <span class="quick-function-menu__wallet-body"></span>
                    <span class="quick-function-menu__wallet-flap"></span>
                </span>
                <span class="quick-function-menu__wallet-copy">
                    <span class="quick-function-menu__wallet-label">{{ __('Default Wallet') }}</span>
                    <span class="quick-function-menu__wallet-amount">
                        @if($quickFunctionDefaultWallet)
                            <span class="quick-function-menu__wallet-balance" data-balance-mask data-hidden="0">
                                {{ $quickFunctionDefaultBalance }}
                            </span>
                            <span class="quick-function-menu__wallet-eye dk-balance-eye"
                                  role="button"
                                  tabindex="0"
                                  aria-label="{{ __('Toggle balance') }}"
                                  aria-pressed="false">
                                <i class="fa fa-eye" aria-hidden="true"></i>
                            </span>
                        @else
                            {{ $quickFunctionDefaultBalance }}
                        @endif
                    </span>
                    <span class="quick-function-menu__wallet-code">{{ $quickFunctionDefaultCode }}</span>
                </span>
                <i class="fas fa-chevron-right quick-function-menu__wallet-arrow" aria-hidden="true"></i>
            </a>

            <button type="button" class="quick-function-menu__scan" data-dk-qr-scanner-open role="menuitem">
                <span class="quick-function-menu__scan-icon" aria-hidden="true">
                    <i class="fas fa-qrcode"></i>
                </span>
                <span>{{ __('Scan & Pay') }}</span>
            </button>
        </div>

        <div class="quick-function-menu__grid">
            @foreach($quickFunctionLinks as $quickFunctionLink)
                @php
                    $quickFunctionDisplayTitle = $quickFunctionTitleMap[$quickFunctionLink['icon']] ?? $quickFunctionLink['title'];

                    if ($quickFunctionLink['icon'] === 'history' && $quickFunctionLink['title'] !== __('History')) {
                        $quickFunctionDisplayTitle = $quickFunctionLink['title'];
                    }
                @endphp
                <a href="{{ $quickFunctionLink['link'] }}" class="quick-function-menu__action" data-accent="{{ $quickFunctionAccentMap[$quickFunctionLink['icon']] ?? 'blue' }}" title="{{ $quickFunctionLink['title'] }}" role="menuitem" tabindex="0">
                    <span class="quick-function-menu__icon" aria-hidden="true">
                        <x-icon name="{{ $quickFunctionLink['icon'] }}" height="22" width="22" />
                    </span>
                    <span class="quick-function-menu__label">{{ $quickFunctionDisplayTitle }}</span>
                </a>
            @endforeach
        </div>

        <a href="{{ route('user.wallet.index') }}" class="quick-function-menu__footer-link" role="menuitem">
            <span class="quick-function-menu__footer-icon" aria-hidden="true">
                <i class="fas fa-shield-alt"></i>
            </span>
            <span class="quick-function-menu__footer-copy">
                <strong>{{ __('Your Wallet, Your Control') }}</strong>
                <span>{{ __('Fast, secure & reliable payments') }}</span>
            </span>
            <i class="fas fa-chevron-right quick-function-menu__footer-arrow" aria-hidden="true"></i>
        </a>
    </div>
</div>

@push('scripts')
    @include('frontend.layouts.user.partials._quick_functions_js')
@endpush
