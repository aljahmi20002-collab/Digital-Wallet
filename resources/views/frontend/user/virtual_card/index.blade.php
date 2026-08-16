@php
    use App\Enums\TrxType;
    use App\Enums\VirtualCard\VirtualCardStatus;
@endphp
@extends('frontend.layouts.user.index')
@section('title', __('My Virtual Cards'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/virtual-card.css?v='.config('app.version')) }}">
@endpush

@section('content')
    <div class="single-form-card">
        <x-user-feature-header
            :title="__('My Virtual Cards')"
            :subtitle="__('Issue, control, and reconcile virtual cards across teams and budgets.')"
            icon="fas fa-credit-card"
        >
            <a class="btn btn-light-secondary btn-sm" href="{{ route('user.virtual-card.cardholders.index') }}">
                <i class="fa-solid fa-users"></i> {{ __('Cardholders') }}
            </a>
            <a class="btn btn-light-primary btn-sm" href="{{ route('user.virtual-card.request.index') }}">
                <i class="fa-solid fa-list"></i> {{ __('My Requests') }}
            </a>
            <button type="button" class="btn btn-light-success btn-sm" data-bs-toggle="modal" data-bs-target="#requestVirtualCardModal">
                <i class="fa-solid fa-credit-card"></i> {{ __('Request New') }}
            </button>
        </x-user-feature-header>


        <div class="vc-page" data-vc-page>

            {{-- Stats strip --}}
            <div class="vc-stats">
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Total Balance') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--brand">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                    </div>
                    <div class="vc-stat__value">{{ siteCurrency('symbol') . number_format($stats['total_balance'], 2) }}</div>
                    <div class="vc-stat__sub">
                        <span>{{ __('across :n cards', ['n' => $stats['total_cards']]) }}</span>
                    </div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Spent This Month') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--violet">
                            <i class="fa-solid fa-cart-shopping"></i>
                        </div>
                    </div>
                    <div class="vc-stat__value">{{ siteCurrency('symbol') . number_format($stats['monthly_spend'], 2) }}</div>
                    <div class="vc-stat__sub">
                        @if($stats['monthly_trend'])
                            <span class="vc-trend is-{{ $stats['monthly_trend']['dir'] }}">
                                <i class="fa-solid {{ $stats['monthly_trend']['dir'] === 'up' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                {{ $stats['monthly_trend']['pct'] }}%
                            </span>
                            <span>{{ __('vs last month') }}</span>
                        @else
                            <span>{{ __('No spend last month') }}</span>
                        @endif
                    </div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Pending Auth') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--amber">
                            <i class="fa-regular fa-clock"></i>
                        </div>
                    </div>
                    <div class="vc-stat__value">{{ siteCurrency('symbol') . number_format($stats['pending_auth'], 2) }}</div>
                    <div class="vc-stat__sub">
                        <span>{{ __(':n transactions', ['n' => $stats['pending_count']]) }}</span>
                    </div>
                </div>
                <div class="vc-stat">
                    <div class="vc-stat__top">
                        <div class="vc-stat__label">{{ __('Total Cards') }}</div>
                        <div class="vc-stat__icon vc-stat__icon--green">
                            <i class="fa-solid fa-credit-card"></i>
                        </div>
                    </div>
                    <div class="vc-stat__value">{{ $stats['total_cards'] }}</div>
                    <div class="vc-stat__sub">
                        <span>{{ __(':active active · :frozen frozen', ['active' => $stats['active_cards'], 'frozen' => $stats['frozen_cards']]) }}</span>
                    </div>
                </div>
            </div>

            @if($cards->count())
                @php
                    // Theme rotation per card so 3+ cards stay visually distinct
                    $themeWheel = ['midnight', 'ocean', 'graphite', 'emerald', 'violet'];
                @endphp

                <div class="vc-grid">
                    {{-- LEFT: hero + switcher + transactions --}}
                    <div>
                        {{-- Card hero --}}
                        <div class="vc-panel" data-vc-hero>
                            <div class="vc-panel__head">
                                <div>
                                    <div class="vc-hero__eyebrow">
                                        {{ __('Selected Card') }}
                                    </div>
                                    <div class="vc-hero__title-row">
                                        <h2 class="vc-panel__title vc-hero__title" data-vc-hero-label>—</h2>
                                        <span class="vc-pill" data-vc-hero-status>
                                            <span class="vc-pill__dot"></span>
                                            <span data-vc-hero-status-label>—</span>
                                        </span>
                                        <span class="vc-pill vc-pill--blue" data-vc-hero-provider>—</span>
                                    </div>
                                </div>
                            </div>
                            <div class="vc-panel__body">
                                {{-- Hero row: card visual + balance / monthly spend always side by side --}}
                                <div class="vc-hero">
                                    <div data-vc-hero-card>
                                        {{-- Active card visual injected by JS --}}
                                    </div>

                                    <div class="vc-hero__info">
                                        <div class="vc-hero__balance-label">{{ __('Available Balance') }}</div>
                                        <div class="vc-hero__balance" data-vc-hero-balance>—</div>

                                        <div class="vc-hero__progress">
                                            {{-- Value on top — its own line so the long "$X / $Y" string
                                                 never gets squeezed against the label. --}}
                                            <div class="vc-hero__progress-value mono" data-vc-hero-card-balance>—</div>
                                            <div class="vc-progress">
                                                <div class="vc-progress__fill" data-vc-hero-progress data-front-progress-pct="0"></div>
                                            </div>
                                            {{-- Label below the bar — full-width so it can't be truncated. --}}
                                            <div class="vc-hero__progress-label">{{ __('Monthly spend') }}</div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Field boxes (Card Number + Expiry · CVV) sit below the hero
                                     so they get the full panel width and never compete for room
                                     with the balance/spend column. --}}
                                <div class="vc-hero__rows">
                                    <div class="vc-hero__field">
                                        <div class="vc-hero__field-label">{{ __('Card Number') }}</div>
                                        <div class="vc-hero__field-value">
                                            <span data-vc-hero-pan>•••• •••• •••• ••••</span>
                                            <button type="button" class="vc-icon-btn" data-vc-action="copy" data-target="pan" title="{{ __('Copy') }}">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="vc-hero__field">
                                        <div class="vc-hero__field-label">{{ __('Expiry · CVV') }}</div>
                                        <div class="vc-hero__field-value">
                                            <span data-vc-hero-exp>—</span>
                                            @if(!$demoMode)
                                                <button type="button" class="vc-link-btn" data-vc-action="reveal">
                                                    <i class="fa-regular fa-eye"></i> {{ __('Reveal') }}
                                                </button>
                                            @else
                                                <button type="button" class="vc-link-btn" data-vc-action="reveal-demo">
                                                    <i class="fa-regular fa-eye"></i> {{ __('Reveal') }}
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Quick actions: each is data-driven and capability-aware --}}
                            <div class="vc-actions" data-vc-actions>
                                <button type="button" class="vc-actions__btn" data-tone="blue" data-vc-action="topup" data-cap="topup">
                                    <span class="vc-actions__icon"><i class="fa-solid fa-arrow-down"></i></span>
                                    {{ __('Top Up') }}
                                </button>
                                <button type="button" class="vc-actions__btn" data-tone="violet" data-vc-action="freeze" data-cap="freeze">
                                    <span class="vc-actions__icon"><i class="fa-regular fa-snowflake"></i></span>
                                    <span data-vc-freeze-label>{{ __('Freeze') }}</span>
                                </button>
                                <button type="button" class="vc-actions__btn" data-tone="amber" data-vc-action="limits" data-cap="limits">
                                    <span class="vc-actions__icon"><i class="fa-regular fa-clock"></i></span>
                                    {{ __('Set Limits') }}
                                </button>
                                <button type="button" class="vc-actions__btn" data-tone="green" data-vc-action="withdraw" data-cap="withdraw">
                                    <span class="vc-actions__icon"><i class="fa-solid fa-arrow-up"></i></span>
                                    {{ __('Withdraw') }}
                                </button>
                            </div>
                        </div>

                        {{-- Card switcher --}}
                        <div class="vc-panel vc-panel--stacked">
                            <div class="vc-panel__head">
                                <div>
                                    <h3 class="vc-panel__title">{{ __('Your cards') }}</h3>
                                    <div class="vc-panel__subtitle">{{ __('Tap a card to inspect') }}</div>
                                </div>
                                <a href="{{ route('user.virtual-card.request.index') }}" class="vc-link-btn">
                                    {{ __('View all') }} <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                            <div class="vc-panel__body">
                                <div class="vc-switcher {{ $cards->count() > 3 ? 'vc-switcher--scroll' : '' }}" data-vc-switcher>
                                    @foreach($cards as $i => $card)
                                        @php
                                            // User-chosen theme wins; fall back to rotating wheel for legacy cards.
                                            $userTheme   = $card->meta['theme'] ?? null;
                                            $theme       = in_array($userTheme, $themeWheel, true)
                                                ? $userTheme
                                                : $themeWheel[$i % count($themeWheel)];
                                            $providerCfg = $card->provider;
                                            $caps        = $providerCfg ? $providerCfg->resolved_capabilities : config('virtual_card.default_capabilities');
                                            $statusVal   = $card->status?->value ?? 'active';
                                            $isActive    = $statusVal === VirtualCardStatus::Active->value;
                                        @endphp
                                        @php
                                            $providerCode = $providerCfg ? $providerCfg->code : null;
                                            $providerLabel = $providerCfg ? $providerCfg->dashboard_label : '';
                                            $providerColor = $providerCfg ? $providerCfg->brand_color_hex : null;
                                            // Resolve a meaningful cardholder name + nickname:
                                            // — `cardholderName`  → printed on the bottom-left of the card visual
                                            // — `cardholderLabel` → printed on the hero title row + on the card front "VIRTUAL · ..."
                                            $cardholderRow   = optional($card->request)->cardholder;
                                            $cardholderName  = $cardholderRow?->card_type?->isBusiness() && $cardholderRow?->business
                                                ? $cardholderRow->business->business_name
                                                : ($cardholderRow?->full_name
                                                    ?: ($card->user->full_name ?? $card->user->name ?? __('Cardholder')));
                                            $cardholderLabel = $card->wallet->name
                                                ?: ($card->wallet->currency->name
                                                    ?: ($card->wallet->currency->code ?? __('Wallet')));
                                        @endphp
                                        <button type="button"
                                                class="vc-mini {{ $loop->first ? 'is-active' : '' }}"
                                                data-vc-card-id="{{ $card->id }}"
                                                data-vc-provider="{{ $providerCode }}"
                                                data-vc-provider-name="{{ $providerCfg ? $providerCfg->name : __('Unknown provider') }}"
                                                data-vc-provider-label="{{ $providerLabel }}"
                                                data-vc-provider-color="{{ $providerColor }}"
                                                data-vc-provider-card-id="{{ $card->meta['card_id'] ?? $card->provider_card_id }}"
                                                data-vc-label="{{ $cardholderLabel }}"
                                                data-vc-cardholder-name="{{ $cardholderName }}"
                                                data-vc-last4="{{ $card->last4 }}"
                                                data-vc-brand="{{ $card->brand }}"
                                                data-vc-network="{{ $card->network }}"
                                                data-vc-exp="{{ $card->expiry_month }}/{{ $card->expiry_year }}"
                                                data-vc-status="{{ $statusVal }}"
                                                data-vc-theme="{{ $theme }}"
                                                data-vc-currency="{{ $card->wallet->currency->code ?? '' }}"
                                                data-vc-currency-symbol="{{ $card->wallet->currency->symbol ?? '$' }}"
                                                data-vc-wallet-balance="{{ (float) ($card->wallet->balance ?? 0) }}"
                                                data-vc-card-balance="{{ (float) ($card->meta['balance'] ?? 0) }}"
                                                data-vc-topup-url="{{ route('user.virtual-card.topup', $card) }}"
                                                data-vc-withdraw-url="{{ route('user.virtual-card.withdraw', $card) }}"
                                                data-vc-freeze-url="{{ route('user.virtual-card.freeze', $card) }}"
                                                data-vc-unfreeze-url="{{ route('user.virtual-card.unfreeze', $card) }}"
                                                data-vc-limits-url="{{ route('user.virtual-card.limits.update', $card) }}"
                                                data-vc-controls-url="{{ route('user.virtual-card.controls.update', $card) }}"
                                                data-vc-details-url="{{ route('user.virtual-card.card-details', array_filter(['id' => $card->id, 'provider' => $providerCode])) }}"
                                                data-vc-caps='@json($caps)'
                                                data-vc-controls='@json($card->meta['controls'] ?? [])'
                                                data-vc-limits='@json($card->meta['limits'] ?? [])'>
                                            <div class="vc-mini__inner {{ $providerColor ? 'has-brand-tint' : '' }}"
                                                 data-theme="{{ $theme }}"
                                                 @if($providerColor) style="--vc-brand-tint: {{ $providerColor }};" @endif>
                                                <div class="vc-mini__top">
                                                    <div class="vc-mini__top-left">
                                                        <div class="vc-mini__label">{{ Str::limit($card->wallet->name ?? $card->wallet->currency->code ?? __('Wallet'), 18) }}</div>
                                                        @if($providerLabel)
                                                            <span class="vc-mini__provider-pill">{{ $providerLabel }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="vc-mini__network">
                                                        <span class="vc-mini__network-mark" data-vc-mini-network></span>
                                                    </div>
                                                </div>
                                                <div class="vc-mini__bottom">
                                                    <div class="vc-mini__last4">•••• {{ $card->last4 }}</div>
                                                    <div class="vc-mini__row">
                                                        @php
                                                            // Mini card shows the SAME "Available Balance" as the
                                                            // hero — i.e. the wallet balance. The on-card meta balance
                                                            // (incremented after explicit topups) is added on top so
                                                            // both topped-up cards and never-topped-up cards display
                                                            // a meaningful spendable figure.
                                                            $walletBalance = (float) ($card->wallet->balance ?? 0);
                                                            $cardOnHand    = (float) ($card->meta['balance'] ?? 0);
                                                            $availableMini = max($walletBalance, $cardOnHand);
                                                        @endphp
                                                        <div class="vc-mini__balance">
                                                            {{ ($card->wallet->currency->symbol ?? '$') . number_format($availableMini, 2) }}
                                                        </div>
                                                        <div class="vc-mini__exp">
                                                            @if($isActive)
                                                                {{ __('EXP') }} {{ $card->expiry_month }}/{{ $card->expiry_year }}
                                                            @else
                                                                {{ Str::upper($statusVal) }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach

                                    <button type="button" class="vc-mini--add" data-bs-toggle="modal" data-bs-target="#requestVirtualCardModal">
                                        <div class="vc-mini--add__plus"><i class="fa-solid fa-plus"></i></div>
                                        <div class="vc-mini--add__title">{{ __('Issue card') }}</div>
                                        <div class="vc-mini--add__hint">{{ __('Submit new request') }}</div>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Recent transactions --}}
                        <div class="vc-panel vc-panel--stacked">
                            <div class="vc-panel__head">
                                <div>
                                    <h3 class="vc-panel__title">{{ __('Recent transactions') }}</h3>
                                    <div class="vc-panel__subtitle">{{ __('Top-ups and withdrawals across all cards') }}</div>
                                </div>
                                <a href="{{ route('user.transaction.index', ['type' => TrxType::CARD_TOPUP]) }}" class="vc-link-btn">
                                    {{ __('View all') }} <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>

                            <div class="vc-tx">
                                <div class="vc-tx__head">
                                    <div>{{ __('Type') }}</div>
                                    <div>{{ __('When') }}</div>
                                    <div>{{ __('Status') }}</div>
                                    <div class="vc-tx__amount-head">{{ __('Amount') }}</div>
                                </div>

                                @forelse($recentTxns as $t)
                                    @php
                                        $isIn      = $t->trx_type === TrxType::CARD_TOPUP;
                                        $statusObj = $t->status;
                                    @endphp
                                    <div class="vc-tx__row">
                                        <div class="vc-tx__merchant">
                                            <div class="vc-tx__icon {{ $isIn ? 'vc-tx__icon--in' : 'vc-tx__icon--out' }}">
                                                <i class="fa-solid {{ $isIn ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                            </div>
                                            <div>
                                                <div class="vc-tx__title">
                                                    {{ $t->trx_type?->label() }}
                                                    @if($t->provider)
                                                        <span class="vc-tx__provider">{{ Str::upper($t->provider) }}</span>
                                                    @endif
                                                </div>
                                                <div class="vc-tx__sub">
                                                    @if($t->trx_id)
                                                        <span class="mono">#{{ $t->trx_id }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="vc-tx__when">
                                            <strong>{{ $t->created_at?->format('M d, H:i') }}</strong>
                                            <span class="vc-tx__when-sub mono">{{ $t->created_at?->diffForHumans(null, true) }}</span>
                                        </div>
                                        <div class="vc-tx__status">
                                            @if($statusObj)
                                                <span class="vc-pill vc-pill--{{ $statusObj === \App\Enums\TrxStatus::COMPLETED ? 'green' : ($statusObj === \App\Enums\TrxStatus::PENDING ? 'amber' : 'red') }}">
                                                    <span class="vc-pill__dot"></span>{{ $statusObj->label() }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="vc-tx__amount {{ $isIn ? 'is-in' : 'is-out' }}">
                                            {{ $isIn ? '+' : '−' }}{{ siteCurrency('symbol') . number_format((float) $t->amount, 2) }}
                                        </div>
                                    </div>
                                @empty
                                    <x-user-not-found
                                        class="vc-not-found vc-not-found--inline"
                                        :title="__('No card transactions yet')"
                                        :message="__('Top up a card to see activity here.')"
                                        icon="fa-receipt"
                                    />
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT: spending chart + provider mix + controls panel --}}
                    <div>
                        {{-- Spending bar chart --}}
                        <div class="vc-panel">
                            <div class="vc-panel__head">
                                <div>
                                    <h3 class="vc-panel__title">{{ __('Spending') }}</h3>
                                    <div class="vc-panel__subtitle" data-vc-spend-period-label>{{ __('Daily · last 30 days') }}</div>
                                </div>
                                <div class="vc-spend__period" data-vc-spend-period>
                                    <button type="button" data-period="7d">7d</button>
                                    <button type="button" data-period="30d" class="is-active">30d</button>
                                    <button type="button" data-period="90d">90d</button>
                                </div>
                            </div>
                            <div class="vc-panel__body">
                                <div class="vc-spend__total">
                                    <span class="vc-spend__total-value" data-vc-spend-total>—</span>
                                    @if($stats['monthly_trend'])
                                        <span class="vc-spend__trend is-{{ $stats['monthly_trend']['dir'] }}">
                                            <i class="fa-solid {{ $stats['monthly_trend']['dir'] === 'up' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                            {{ $stats['monthly_trend']['pct'] }}%
                                        </span>
                                        <span class="vc-spend__trend-sub">{{ __('vs last month') }}</span>
                                    @endif
                                </div>
                                <div class="vc-spend__chart" data-vc-spend-chart></div>
                            </div>
                        </div>

                        {{-- By provider donut --}}
                        <div class="vc-panel vc-panel--stacked">
                            <div class="vc-panel__head">
                                <div>
                                    <h3 class="vc-panel__title">{{ __('By provider') }}</h3>
                                    <div class="vc-panel__subtitle">{{ __('This month') }}</div>
                                </div>
                                <a href="{{ route('user.transaction.index', ['type' => TrxType::CARD_TOPUP]) }}" class="vc-link-btn">
                                    {{ __('Details') }} <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                            <div class="vc-panel__body">
                                @if(!empty($providerMix['items']))
                                    <div class="vc-mix">
                                        <div class="vc-mix__donut" data-vc-mix-donut></div>
                                        <div class="vc-mix__center">
                                            <div>
                                                <div class="vc-mix__center-label">{{ __('Spent') }}</div>
                                                <div class="vc-mix__center-value">{{ siteCurrency('symbol') . number_format($providerMix['total'], 0) }}</div>
                                            </div>
                                        </div>
                                        <div class="vc-mix__legend">
                                            @foreach($providerMix['items'] as $item)
                                                <div class="vc-mix__row">
                                                    <span class="vc-mix__swatch" style="background: {{ $item['color'] }};"></span>
                                                    <span class="vc-mix__name">{{ $item['name'] }}</span>
                                                    <span class="vc-mix__pct">{{ $item['pct'] }}%</span>
                                                    <span class="vc-mix__amount">{{ siteCurrency('symbol') . number_format($item['value'], 0) }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <x-user-not-found
                                        class="vc-not-found vc-not-found--inline"
                                        :title="__('No provider spend yet')"
                                        :message="__('Top up a card to compare provider activity this month.')"
                                        icon="fa-chart-pie"
                                    />
                                @endif
                            </div>
                        </div>

                        {{-- Card controls (soft toggles persisted to meta JSON) --}}
                        <div class="vc-panel vc-panel--stacked" data-vc-controls-panel>
                            <div class="vc-panel__head">
                                <div>
                                    <h3 class="vc-panel__title">{{ __('Card controls') }}</h3>
                                    <div class="vc-panel__subtitle">
                                        {{ __('Applies to') }} <span class="mono" data-vc-controls-target>—</span>
                                    </div>
                                </div>
                                <span class="vc-pill vc-pill--green" data-vc-controls-state>{{ __('Saved') }}</span>
                            </div>
                            <div class="vc-panel__body">
                                <div class="vc-control-row" data-vc-control="online">
                                    <div class="vc-control-row__icon"><i class="fa-solid fa-cart-shopping"></i></div>
                                    <div class="vc-control-row__copy">
                                        <div class="vc-control-row__title">{{ __('Online purchases') }}</div>
                                        <div class="vc-control-row__sub">{{ __('E-commerce, subscriptions') }}</div>
                                    </div>
                                    <button type="button" class="vc-toggle" data-vc-toggle="online"></button>
                                </div>
                                <div class="vc-control-row" data-vc-control="atm">
                                    <div class="vc-control-row__icon"><i class="fa-solid fa-money-bill"></i></div>
                                    <div class="vc-control-row__copy">
                                        <div class="vc-control-row__title">{{ __('ATM withdrawals') }}</div>
                                        <div class="vc-control-row__sub">{{ __('Cash advances') }}</div>
                                    </div>
                                    <button type="button" class="vc-toggle" data-vc-toggle="atm"></button>
                                </div>
                                <div class="vc-control-row" data-vc-control="intl">
                                    <div class="vc-control-row__icon"><i class="fa-solid fa-globe"></i></div>
                                    <div class="vc-control-row__copy">
                                        <div class="vc-control-row__title">{{ __('International transactions') }}</div>
                                        <div class="vc-control-row__sub">{{ __('Outside merchant country') }}</div>
                                    </div>
                                    <button type="button" class="vc-toggle" data-vc-toggle="intl"></button>
                                </div>
                                <div class="vc-control-row" data-vc-control="contactless">
                                    <div class="vc-control-row__icon"><i class="fa-solid fa-wifi"></i></div>
                                    <div class="vc-control-row__copy">
                                        <div class="vc-control-row__title">{{ __('Contactless & in-store') }}</div>
                                        <div class="vc-control-row__sub">{{ __('NFC, mobile wallets') }}</div>
                                    </div>
                                    <button type="button" class="vc-toggle" data-vc-toggle="contactless"></button>
                                </div>

                                <div class="vc-action-row">
                                    <button type="button" class="vc-btn vc-btn--secondary" data-vc-action="freeze" data-cap="freeze">
                                        <i class="fa-regular fa-snowflake"></i>
                                        <span data-vc-freeze-label-secondary>{{ __('Freeze card') }}</span>
                                    </button>
                                    <button type="button" class="vc-btn vc-btn--secondary" data-vc-action="limits" data-cap="limits">
                                        <i class="fa-regular fa-clock"></i> {{ __('Limits') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <x-user-not-found
                    class="vc-not-found"
                    :title="__('No virtual cards yet')"
                    :message="__('Request a new virtual card and track approval from your card requests page.')"
                    icon="fa-credit-card"
                    :action-url="route('user.virtual-card.request.index')"
                    :action-label="__('Request New Card')"
                    action-icon="fa-plus"
                />
            @endif
        </div>
    </div>

    {{-- Modals --}}
    @if(!$demoMode)
        @include('frontend.user.virtual_card.partials._card_details_modal')
    @endif

    @include('frontend.user.virtual_card.partials._topup_modal')
    @include('frontend.user.virtual_card.partials._withdraw_modal')
    @include('frontend.user.virtual_card.partials._freeze_modal')
    @include('frontend.user.virtual_card.partials._limits_modal')

    @include('frontend.user.virtual_card.request.partials._add_card_request_modal')
@endsection

@push('scripts')
    <script>
    'use strict';
        window.VCPageData = {
            spend:          {!! json_encode($spendChart) !!},
            providerMix:    {!! json_encode($providerMix) !!},
            currencySymbol: @json(siteCurrency('symbol')),
        };
    </script>
    @include('frontend.user.virtual_card.partials._script')
@endpush
