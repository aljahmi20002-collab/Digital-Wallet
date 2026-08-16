@extends('frontend.layouts.user.index')
@section('title', __('Merchants'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/merchant.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/merchant.css'))) }}">
@endpush
@section('content')
    @php
        $walletCurrencyCodes = collect($walletCurrencyCodes ?? [])
            ->map(fn ($code): string => strtoupper((string) $code))
            ->filter()
            ->unique()
            ->values();
        $currencyCodesFor = function ($merchant) {
            $codes = $merchant->supportedCurrencies->pluck('code');

            if ($codes->isEmpty() && $merchant->currency?->code) {
                $codes = collect([$merchant->currency->code]);
            }

            return $codes
                ->map(fn ($code): string => strtoupper((string) $code))
                ->filter()
                ->unique()
                ->values();
        };
        $gatewayCountFor = function ($merchant) use ($currencyCodesFor, $walletCurrencyCodes): int {
            $walletReadyCodes = $currencyCodesFor($merchant)->intersect($walletCurrencyCodes)->values();

            return $merchant->paymentMethods
                ->filter(fn (\App\Models\DepositMethod $method): bool => (bool) $method->status
                    && $method->type === \App\Enums\MethodType::AUTOMATIC
                    && $walletReadyCodes->contains(strtoupper((string) $method->currency)))
                ->count();
        };
        $approvedCount = $merchants->filter(fn ($merchant) => $merchant->status === \App\Enums\MerchantStatus::APPROVED)->count();
        $pendingCount = $merchants->filter(fn ($merchant) => $merchant->status === \App\Enums\MerchantStatus::PENDING)->count();
        $currencyCodes = $merchants
            ->flatMap(fn ($merchant) => $currencyCodesFor($merchant))
            ->filter()
            ->unique()
            ->values();
        $walletReadyCurrencyCodes = $currencyCodes->intersect($walletCurrencyCodes)->values();
        $statusInsightFor = function ($merchant, int $gatewayCount, $missingWalletCodes): array {
            if ($merchant->status === \App\Enums\MerchantStatus::PENDING) {
                return [
                    'tone' => 'warning',
                    'icon' => 'fa-hourglass-half',
                    'label' => __('Under admin review'),
                    'detail' => __('Production unlocks after approval.'),
                ];
            }

            if ($merchant->status === \App\Enums\MerchantStatus::DISABLED) {
                return [
                    'tone' => 'danger',
                    'icon' => 'fa-ban',
                    'label' => __('Shop disabled'),
                    'detail' => __('Payments and live API are paused.'),
                ];
            }

            if ($merchant->status === \App\Enums\MerchantStatus::REJECTED) {
                return [
                    'tone' => 'danger',
                    'icon' => 'fa-circle-xmark',
                    'label' => __('Review rejected'),
                    'detail' => __('Update profile or contact support.'),
                ];
            }

            if ($missingWalletCodes->isNotEmpty()) {
                return [
                    'tone' => 'warning',
                    'icon' => 'fa-wallet',
                    'label' => __('Wallet missing'),
                    'detail' => __('Activate :currencies wallet before checkout.', ['currencies' => $missingWalletCodes->implode(', ')]),
                ];
            }

            if ($gatewayCount < 1) {
                return [
                    'tone' => 'warning',
                    'icon' => 'fa-credit-card',
                    'label' => __('Gateway missing'),
                    'detail' => __('Select a matching gateway in API config.'),
                ];
            }

            return [
                'tone' => 'success',
                'icon' => 'fa-circle-check',
                'label' => __('Ready for payments'),
                'detail' => __('Wallet, API, and gateway are ready.'),
            ];
        };
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card single-form-card merchant-service-card merchant-index-card">
                <x-user-feature-header
                    :title="__('Merchants')"
                    :subtitle="__('Manage merchant profiles, payment links, API configuration, and settlement readiness.')"
                    icon="fas fa-store"
                >
                    <a class="btn btn-light-merchant btn-sm"
                       href="{{ route('user.transaction.index', ['type' => \App\Enums\TrxType::RECEIVE_PAYMENT]) }}">
                        <i class="fas fa-list"></i> {{ __('Payments') }}
                    </a>
                    <a class="btn btn-merchant btn-sm" href="{{ route('user.merchant.create') }}">
                        <i class="fas fa-plus-circle"></i> {{ __('Create Merchant') }}
                    </a>
                </x-user-feature-header>

                <div class="card-body merchant-console">
                    @if($merchants->isEmpty())
                        <div class="merchant-command-panel merchant-command-panel--neutral">
                            <div class="merchant-command-panel__identity">
                                <span class="merchant-command-panel__empty-icon"><i class="fa-solid fa-store"></i></span>
                                <div class="min-w-0">
                                    <span class="merchant-command-panel__eyebrow">{{ __('Merchant workspace') }}</span>
                                    <h2>{{ __('Build your first checkout profile') }}</h2>
                                    <p>{{ __('Create a merchant once, select supported wallet currencies, then connect payment links or API checkout after approval.') }}</p>
                                </div>
                            </div>
                            <a class="btn btn-merchant" href="{{ route('user.merchant.create') }}">
                                <i class="fa-solid fa-plus me-1"></i>{{ __('Create Merchant') }}
                            </a>
                        </div>
                    @endif

                    <div class="merchant-stat-grid">
                        <div class="merchant-stat">
                            <span class="merchant-stat__icon"><i class="fa-solid fa-store"></i></span>
                            <div>
                                <span>{{ __('Total shops') }}</span>
                                <strong>{{ $merchants->count() }}</strong>
                                <small>{{ __('All merchant profiles') }}</small>
                            </div>
                        </div>
                        <div class="merchant-stat">
                            <span class="merchant-stat__icon"><i class="fa-solid fa-circle-check"></i></span>
                            <div>
                                <span>{{ __('Live ready') }}</span>
                                <strong>{{ $approvedCount }}</strong>
                                <small>{{ __('Approved for production') }}</small>
                            </div>
                        </div>
                        <div class="merchant-stat">
                            <span class="merchant-stat__icon"><i class="fa-solid fa-hourglass-half"></i></span>
                            <div>
                                <span>{{ __('In review') }}</span>
                                <strong>{{ $pendingCount }}</strong>
                                <small>{{ __('Waiting admin decision') }}</small>
                            </div>
                        </div>
                        <div class="merchant-stat">
                            <span class="merchant-stat__icon"><i class="fa-solid fa-wallet"></i></span>
                            <div>
                                <span>{{ __('Wallet coverage') }}</span>
                                <strong>{{ $walletReadyCurrencyCodes->count() }}/{{ $currencyCodes->count() }}</strong>
                                <small>{{ __('Ready currency rails') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="merchant-shop-list">
                        @if($merchants->isNotEmpty())
                            <div class="merchant-shop-list__head">
                                <div>
                                    <span>{{ __('Shop list') }}</span>
                                    <strong>{{ __('Shop readiness overview') }}</strong>
                                </div>
                                <small>{{ __('Each row shows approval, wallet rail, gateway, and checkout action status.') }}</small>
                            </div>
                        @endif

                        @forelse($merchants as $merchant)
                            @php
                                $locked = $merchant->isActionLocked();
                                $lockTitle = $locked ? __('Unavailable: merchant is disabled or rejected') : null;
                                $lockAttrs = $locked ? ' tabindex="-1" aria-disabled="true"' : '';
                                $lockClasses = $locked ? ' disabled pointer-events-none opacity-75' : '';
                                $merchantCurrencyCodes = $currencyCodesFor($merchant);
                                $walletReadyCodes = $merchantCurrencyCodes->intersect($walletCurrencyCodes)->values();
                                $missingWalletCodes = $merchantCurrencyCodes->diff($walletCurrencyCodes)->values();
                                $primaryCurrencyCode = strtoupper((string) ($merchant->primaryCurrency()?->code ?? $merchant->currency?->code ?? $merchantCurrencyCodes->first() ?? '-'));
                                $gatewayCount = $gatewayCountFor($merchant);
                                $mode = $merchant->current_mode ?? \App\Enums\EnvironmentMode::SANDBOX;
                                $statusInsight = $statusInsightFor($merchant, $gatewayCount, $missingWalletCodes);
                            @endphp

                            <article class="merchant-shop-card">
                                <div class="merchant-shop-card__identity">
                                    <img src="{{ asset($merchant->business_logo) }}" alt="{{ $merchant->business_name }}" class="merchant-shop-card__logo" loading="lazy">
                                    <div class="min-w-0">
                                        <div class="merchant-shop-card__title-line">
                                            <strong class="merchant-shop-card__name">{{ $merchant->business_name }}</strong>
                                            <x-badge :status="$merchant->status"/>
                                        </div>
                                        @if($merchant->site_url)
                                            <a class="merchant-shop-card__url" href="{{ safeUrl($merchant->site_url) }}" target="_blank" rel="noopener">
                                                {{ $merchant->site_url }}
                                            </a>
                                        @else
                                            <span class="merchant-shop-card__url">{{ __('No site URL') }}</span>
                                        @endif
                                        <div class="merchant-shop-card__badges">
                                            <span class="merchant-chip merchant-chip--muted">
                                                <i class="{{ $mode->icon() }}"></i>
                                                {{ $mode->label() }}
                                            </span>
                                            <span class="merchant-chip merchant-chip--wallet {{ $missingWalletCodes->isNotEmpty() ? 'is-missing' : '' }}">
                                                <i class="fa-solid fa-wallet"></i>
                                                {{ __('Wallet :ready/:total', ['ready' => $walletReadyCodes->count(), 'total' => $merchantCurrencyCodes->count()]) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="merchant-shop-card__rail">
                                    <div class="merchant-status-note merchant-status-note--{{ $statusInsight['tone'] }}">
                                        <i class="fa-solid {{ $statusInsight['icon'] }}"></i>
                                        <div>
                                            <strong>{{ $statusInsight['label'] }}</strong>
                                            <span>{{ $statusInsight['detail'] }}</span>
                                        </div>
                                    </div>
                                    <div class="merchant-currency-rail">
                                        @foreach($merchantCurrencyCodes as $code)
                                            @php
                                                $isPrimaryCurrency = $primaryCurrencyCode === $code;
                                                $hasWallet = $walletReadyCodes->contains($code);
                                            @endphp
                                            <span class="merchant-currency-pill {{ $isPrimaryCurrency ? 'is-primary' : '' }} {{ $hasWallet ? '' : 'is-missing' }}">
                                                <i class="fa-solid {{ $isPrimaryCurrency ? 'fa-bullseye' : ($hasWallet ? 'fa-wallet' : 'fa-triangle-exclamation') }}"></i>
                                                {{ $code }}
                                            </span>
                                        @endforeach
                                        @if($merchantCurrencyCodes->isEmpty())
                                            <span class="merchant-currency-pill is-missing">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                {{ __('No currency') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="merchant-shop-card__ops">
                                    <ul class="merchant-shop-card__fact-list" aria-label="{{ __('Shop details') }}">
                                        <li>
                                            <i class="fa-solid fa-id-card"></i>
                                            <span>{{ __('ID') }}</span>
                                            <strong>{{ $merchant->merchant_key ?: __('Pending') }}</strong>
                                        </li>
                                        <li>
                                            <i class="fa-solid fa-credit-card"></i>
                                            <span>{{ __('Gateways') }}</span>
                                            <strong>{{ $gatewayCount }}</strong>
                                        </li>
                                        <li>
                                            <i class="fa-solid fa-percent"></i>
                                            <span>{{ __('Fee') }}</span>
                                            <strong>{{ number_format((float) ($merchant->fee ?? 0), 2) }}%</strong>
                                        </li>
                                    </ul>

                                    <div class="merchant-shop-card__actions" role="list" aria-label="{{ __('Shop actions') }}">
                                        <a href="{{ route('user.payment-links.create', ['merchant_id' => $merchant->id]) }}"
                                           class="merchant-shop-action btn-light-merchant{{ $lockClasses }}" title="{{ $lockTitle ?? __('Create payment link') }}" role="listitem"{!! $lockAttrs !!}>
                                            <i class="fas fa-link"></i> {{ __('Link') }}
                                        </a>
                                        <a href="{{ route('user.merchant.config', $merchant->id) }}"
                                           class="merchant-shop-action btn-outline-merchant{{ $lockClasses }}" title="{{ $lockTitle ?? __('API and gateway config') }}" role="listitem"{!! $lockAttrs !!}>
                                            <i class="fa-solid fa-sliders"></i> {{ __('API') }}
                                        </a>
                                        <a href="{{ route('user.merchant.edit', $merchant->id) }}"
                                           class="merchant-shop-action btn-merchant{{ $lockClasses }}" title="{{ $lockTitle ?? __('Edit merchant') }}" role="listitem"{!! $lockAttrs !!}>
                                            <i class="fa-solid fa-pen-to-square"></i> {{ __('Edit') }}
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-user-not-found
                                :title="__('No merchants found')"
                                :message="__('Create your first merchant to start accepting wallet payments.')"
                                :eyebrow="__('Merchant workspace ready')"
                                icon="fa-store"
                                :action-url="route('user.merchant.create')"
                                :action-label="__('Create Merchant')"
                                action-icon="fa-plus"
                                :secure-label="__('Accept payments faster')"
                            />
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
