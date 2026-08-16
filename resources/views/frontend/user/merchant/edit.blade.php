@extends('frontend.layouts.user.index')
@section('title', __('Edit Merchant'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/merchant.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/merchant.css'))) }}">
@endpush
@section('content')
    @php
        $hasCurrencyChoices = $currencies->isNotEmpty();
        $selectedCurrencyIds = collect(old('currency_ids', $merchant->supportedCurrencyIds()))
            ->map(fn ($id) => (int) $id)
            ->all();
        $currencyCodes = $merchant->supportedCurrencies->pluck('code')->implode(', ') ?: $merchant->currency?->code;
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card single-form-card merchant-service-card">
                <x-user-feature-header
                    :title="__('Edit Merchant')"
                    :subtitle="__('Refine business details while keeping approval requirements clear.')"
                    icon="fas fa-pen-ruler"
                >
                    <a href="{{ route('user.transaction.index', ['type' => \App\Enums\TrxType::RECEIVE_PAYMENT]) }}" class="btn btn-light-merchant btn-sm">
                        <i class="fas fa-list"></i> {{ __('Payments') }}
                    </a>
                    <a href="{{ route('user.merchant.index') }}" class="btn btn-light-merchant btn-sm">
                        <i class="fas fa-store"></i> {{ __('Merchants') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main merchant-context merchant-application merchant-application--edit">
                    <div class="merchant-application-hero">
                        <div class="merchant-application-hero__intro">
                            <span class="merchant-application-hero__eyebrow">
                                <i class="fa-solid fa-rotate"></i>
                                {{ __('Profile refresh') }}
                            </span>
                            <h2>{{ __('Keep your checkout details sharp') }}</h2>
                            <p>{{ __('Business identity or currency changes can move the merchant back to review, so keep updates intentional and accurate.') }}</p>
                        </div>

                        <div class="merchant-process-rail" aria-label="{{ __('Merchant update steps') }}">
                            <div class="merchant-process-step is-active">
                                <span>1</span>
                                <div>
                                    <strong>{{ __('Update') }}</strong>
                                    <small>{{ __('Profile') }}</small>
                                </div>
                            </div>
                            <div class="merchant-process-step">
                                <span>2</span>
                                <div>
                                    <strong>{{ __('Review') }}</strong>
                                    <small>{{ __('If needed') }}</small>
                                </div>
                            </div>
                            <div class="merchant-process-step">
                                <span>3</span>
                                <div>
                                    <strong>{{ __('Operate') }}</strong>
                                    <small>{{ __('Checkout') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @unless($hasCurrencyChoices)
                        <x-user-not-found
                            :title="__('No active wallet found')"
                            :message="__('Create or activate a wallet first. Your merchant checkout must accept at least one active wallet currency.')"
                            icon="fa-wallet"
                            :action-url="route('user.wallet.index')"
                            :action-label="__('Open Wallets')"
                            action-icon="fa-wallet"
                        />
                    @endunless

                    <form action="{{ route('user.merchant.update', $merchant->id) }}" method="POST" enctype="multipart/form-data" class="merchant-application-form">
                        @csrf
                        @method('PUT')

                        <div class="merchant-application-main merchant-application-main--edit">
                            <div class="merchant-application-logo">
                                <div class="merchant-field-title">
                                    <span>{{ __('Current profile') }}</span>
                                    <strong>{{ __('Business Logo') }}</strong>
                                    <small>{{ __('Keep brand recognition consistent across checkout.') }}</small>
                                </div>
                                <div class="merchant-logo-uploader">
                                    <x-img name="business_logo" :old="old('business_logo', $merchant->business_logo)" :ref="'business-logo'"></x-img>
                                </div>
                                <div class="merchant-field-summary merchant-field-summary--compact">
                                    <img src="{{ asset($merchant->business_logo) }}" alt="{{ $merchant->business_name }}" class="merchant-field-summary__logo" loading="lazy">
                                    <div>
                                        <strong>{{ $merchant->business_name }}</strong>
                                        <span>{{ __('Status: :status', ['status' => $merchant->status->label()]) }}</span>
                                    </div>
                                </div>
                                <div class="merchant-demo-request">
                                    <span><i class="fa-solid fa-coins"></i> {{ __('Active currencies') }}</span>
                                    <strong>{{ $currencyCodes ?: __('No currency selected') }}</strong>
                                    <small>{{ __('Primary: :currency', ['currency' => $merchant->primaryCurrency()?->code ?? $merchant->currency?->code ?? '-']) }}</small>
                                </div>
                            </div>

                            <div class="merchant-application-fields">
                                <div class="merchant-application-note merchant-application-note--compact">
                                    <span class="merchant-application-note__icon">
                                        <i class="fas fa-shield-halved"></i>
                                    </span>
                                    <div>
                                        <strong>{{ __('Review-sensitive changes') }}</strong>
                                        <span>{{ __('Changing business identity or supported currencies sends the merchant back for admin approval to protect live checkout access.') }}</span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-2 single-input-inner style-border">
                                            <label for="business_name" class="form-label">{{ __('Business Name') }}</label>
                                            <input type="text" class="form-control" id="business_name" name="business_name"
                                                   value="{{ old('business_name', $merchant->business_name) }}" required
                                                   placeholder="{{ __('Enter your business name') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-2 single-input-inner style-border">
                                            <label for="business_email" class="form-label">{{ __('Business Email') }}</label>
                                            <input type="email" class="form-control" id="business_email" name="business_email"
                                                   value="{{ old('business_email', $merchant->business_email) }}" required
                                                   placeholder="{{ __('Enter your business email') }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-2 single-input-inner style-border">
                                            <label for="site_url" class="form-label">{{ __('Website') }}</label>
                                            <input type="url" class="form-control" id="site_url" name="site_url" required
                                                   value="{{ old('site_url', $merchant->site_url) }}"
                                                   placeholder="{{ __('https://yourwebsite.com') }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Supported Currencies') }}
                                                <i data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{ __('Select every active wallet currency this merchant can accept. The first selected currency becomes primary.') }}"
                                                   class="fa-solid fa-circle-info ms-1"></i>
                                            </label>

                                            <div class="merchant-currency-grid">
                                                @foreach($currencies as $currency)
                                                    @php
                                                        $currencyInputId = 'merchant_currency_'.$currency->id;
                                                    @endphp
                                                    <input
                                                        type="checkbox"
                                                        class="merchant-currency-card__input"
                                                        id="{{ $currencyInputId }}"
                                                        name="currency_ids[]"
                                                        value="{{ $currency->id }}"
                                                        @checked(in_array((int) $currency->id, $selectedCurrencyIds, true))
                                                        @disabled(! $hasCurrencyChoices)
                                                    >
                                                    <label class="merchant-currency-card" for="{{ $currencyInputId }}">
                                                        <span class="merchant-currency-card__mark"><i class="fas fa-check"></i></span>
                                                        <span class="merchant-currency-card__body">
                                                            <strong>{{ $currency->code }}</strong>
                                                            <small>{{ $currency->name }}</small>
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>

                                            @error('currency_ids')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            @error('currency_ids.*')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-2">
                                            <label for="business_description" class="form-label">{{ __('Business Overview') }}</label>
                                            <textarea class="form-control h-25" id="business_description"
                                                      name="business_description" placeholder="{{ __('Describe your business') }}">{{ old('business_description', $merchant->business_description) }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="merchant-application-actions">
                                    <div>
                                        <strong>{{ __('Save merchant changes') }}</strong>
                                        <span>{{ __('Your checkout profile stays locked from rejected or disabled states and re-enters review when key business details change.') }}</span>
                                    </div>
                                    <button type="submit" class="btn btn-merchant" @disabled(! $hasCurrencyChoices)>
                                        <x-icon name="check" height="22" /> {{ __('Update Merchant') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
