@extends('backend.layouts.app')
@section('title', 'User Management')

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/css/user-management.css?v=' . config('app.version')) }}">
@endpush

@section('content')
    <div class="user-mgmt-page">
        @include('backend.user.partials._user_manage_overview')

        <div class="user-mgmt-layout">
            <aside class="user-mgmt-side">
                <section class="um-card um-wallet-card">
                    <div class="um-card__header">
                        <h5 class="um-card__title">
                            <i class="fa-solid fa-wallet"></i>
                            {{ __('Wallets') }}
                        </h5>
                    </div>

                    <div class="um-card__body">
                        <div class="um-wallets">
                            @foreach($user->activeWallets() as $wallet)
                                @php
                                    $amountColor = $wallet->latestTransaction?->amount_flow->color($wallet->latestTransaction->status);
                                    $amountSign = $wallet->latestTransaction?->amount_flow->sign($wallet->latestTransaction->status);
                                    $currencyFallback = mb_strtoupper(mb_substr($wallet->currency->code ?: $wallet->currency->name, 0, 2));
                                @endphp

                                <div class="um-wallet">
                                    <div class="um-wallet__main">
                                        <div class="um-wallet__flag" data-currency-fallback="{{ $currencyFallback }}">
                                            @if($wallet->currency->flag)
                                                <img src="{{ asset($wallet->currency->flag) }}"
                                                     alt="{{ $wallet->currency->code }}"
                                                     onerror="this.remove()" loading="lazy">
                                            @endif
                                        </div>

                                        <div>
                                            <div class="um-wallet__code">
                                                {{ __(':value Wallet', ['value' => $wallet->currency->code]) }}
                                                @if($wallet->currency->default)
                                                    <span class="badge badge-sm bg-success">{{ __('Default') }}</span>
                                                @endif
                                            </div>
                                            <p class="um-wallet__activity">
                                                @if($wallet->latestTransaction)
                                                    <span>{{ __('Recent:') }}</span>
                                                    <span class="fw-bold {{ $amountColor }}">
                                                        {{ $amountSign . getSymbol($wallet->currency->code) . $wallet->latestTransaction->amount }}
                                                    </span>
                                                    <span>{{ __('via') }}</span>
                                                    <span class="fw-bold text-{{ $wallet->latestTransaction->trx_type->badgeColor() }}">
                                                        {{ $wallet->latestTransaction->trx_type->label() }}
                                                    </span>
                                                @else
                                                    {{ __('No recent activity.') }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="um-wallet__balance">
                                        {{ $wallet->currency->symbol . $wallet->balance }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                @can('user-features-manage')
                    @php
                        $enabledFeatures = $user->features->filter(fn ($feature) => (bool) $feature->dynamic_status)->count();
                        $defaultFeatureIcons = [
                            'account_status'     => 'fa-user-shield',
                            'email_verification' => 'fa-envelope-open-text',
                            'kyc_verification'   => 'fa-id-card',
                            'deposit'            => 'fa-circle-arrow-down',
                            'exchange_money'     => 'fa-right-left',
                            'send_money'         => 'fa-paper-plane',
                            'request_money'      => 'fa-hand-holding-dollar',
                            'mobile_recharge'    => 'fa-mobile-screen-button',
                            'withdraw'           => 'fa-circle-arrow-up',
                        ];
                        $configuredFeatureIcons = collect(config('userFeatures.features', []))
                            ->mapWithKeys(fn (array $feature): array => [$feature['feature'] => $feature['icon'] ?? null])
                            ->filter()
                            ->all();
                        $featureIconMap = array_replace($defaultFeatureIcons, $configuredFeatureIcons);
                    @endphp

                    <section class="um-card um-controls-card">
                        <div class="um-card__header">
                            <h5 class="um-card__title">
                                <span class="um-controls__lead-icon">
                                    <i class="fa-solid fa-lock"></i>
                                </span>
                                {{ __('User Controls') }}
                            </h5>
                            <span class="um-controls__counter">
                                <strong>{{ $enabledFeatures }}</strong>
                                {{ __('On') }}
                            </span>
                        </div>

                        <div class="um-card__body--flush">
                            <div class="um-controls__list">
                                @foreach($user->features as $feature)
                                    @php
                                        $title = ucwords(str_replace('_', ' ', $feature->feature));
                                        $featureIcon = $featureIconMap[$feature->feature] ?? 'fa-sliders';
                                    @endphp

                                    <div class="um-controls__item {{ $feature->dynamic_status ? 'is-active' : '' }}">
                                        <span class="um-controls__icon">
                                            <i class="fa-solid {{ $featureIcon }}" aria-hidden="true"></i>
                                        </span>

                                        <div class="um-controls__copy">
                                            <label for="feature_{{ $feature->id }}" class="um-controls__label">
                                                {{ __($title) }}
                                            </label>
                                            <p class="um-controls__hint">{{ __($feature->description) }}</p>
                                        </div>

                                        <div class="form-check form-switch um-controls__switch">
                                            <input class="form-check-input feature-switch"
                                                   type="checkbox"
                                                   id="feature_{{ $feature->id }}"
                                                   data-feature="{{ $feature->feature }}"
                                                   data-user-id="{{ $user->id }}"
                                                   aria-label="{{ __('Toggle feature status') }}"
                                                @checked($feature->dynamic_status)>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                @endcan
            </aside>

            <section class="um-card user-mgmt-main">
                @include('backend.user.partials._user_manage_header')

                <div class="user-mgmt-content">
                    @yield('user_manage_content')
                </div>
            </section>
        </div>
    </div>

    @include('backend.user.partials._delete_user_modal')
    @include('backend.user.partials._convert_to_merchant_modal')
    @include('backend.user.partials._update_balance_modal')
    @include('backend.user.partials._notify_user_modal')
@endsection

@push('scripts')
    @include('backend.user.partials._user_manage_scripts')
@endpush
