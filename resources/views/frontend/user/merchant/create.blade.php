@extends('frontend.layouts.user.index')
@section('title', __('Merchant Request'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/merchant.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/merchant.css'))) }}">
@endpush
@section('content')
    @php
        $hasCurrencyChoices = $currencies->isNotEmpty();
        $selectedCurrencyIds = collect(old('currency_ids', []))
            ->map(fn ($id) => (int) $id)
            ->all();
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card single-form-card merchant-service-card">
                <x-user-feature-header
                    :title="__('Merchant Registration')"
                    :subtitle="__('Set up a branded checkout profile with multi-currency wallet support.')"
                    icon="fas fa-shop"
                >
                    <a href="{{ route('user.transaction.index', ['type' => \App\Enums\TrxType::RECEIVE_PAYMENT]) }}" class="btn btn-light-merchant btn-sm">
                        <i class="fas fa-list"></i> {{ __('Payments') }}
                    </a>
                    <a href="{{ route('user.merchant.index') }}" class="btn btn-light-merchant btn-sm">
                        <i class="fas fa-store"></i> {{ __('Merchants') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main merchant-context merchant-application">
                    <div class="merchant-application-hero">
                        <div class="merchant-application-hero__intro">
                            <span class="merchant-application-hero__eyebrow">
                                <i class="fa-solid fa-store"></i>
                                {{ __('Merchant onboarding') }}
                            </span>
                            <h2>{{ __('Open your merchant checkout') }}</h2>
                            <p>{{ __('Submit your business identity, website, and accepted wallet currencies. API credentials unlock after approval.') }}</p>
                        </div>

                        <div class="merchant-process-rail" aria-label="{{ __('Merchant setup steps') }}">
                            <div class="merchant-process-step is-active">
                                <span>1</span>
                                <div>
                                    <strong>{{ __('Profile') }}</strong>
                                    <small>{{ __('Business info') }}</small>
                                </div>
                            </div>
                            <div class="merchant-process-step">
                                <span>2</span>
                                <div>
                                    <strong>{{ __('Currencies') }}</strong>
                                    <small>{{ __('Wallet rails') }}</small>
                                </div>
                            </div>
                            <div class="merchant-process-step">
                                <span>3</span>
                                <div>
                                    <strong>{{ __('Review') }}</strong>
                                    <small>{{ __('API access') }}</small>
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

                    <form action="{{ route('user.merchant.store') }}" method="POST" enctype="multipart/form-data" class="merchant-application-form">
                        @csrf

                        <div class="merchant-application-main">
                            <div class="merchant-application-logo">
                                <div class="merchant-field-title">
                                    <span>{{ __('Brand') }}</span>
                                    <strong>{{ __('Business Logo') }}</strong>
                                    <small>{{ __('Optional, square image recommended') }}</small>
                                </div>
                                <div class="merchant-logo-uploader">
                                    <x-img name="business_logo" :old="old('business_logo')" :ref="'business-logo'"></x-img>
                                </div>
                                <div class="merchant-upload-hint">
                                    <i class="fas fa-upload"></i>
                                    <span>{{ __('PNG, JPG, SVG. Maximum 1 MB.') }}</span>
                                </div>
                                <div class="merchant-demo-request">
                                    <span><i class="fa-solid fa-link"></i> {{ __('Checkout preview') }}</span>
                                    <strong>{{ __('DigiKash Mart') }}</strong>
                                    <small>{{ __('USD, EUR, BDT - https://shop.example.com - 2.5% merchant fee') }}</small>
                                </div>
                            </div>

                            <div class="merchant-application-fields">
                                <div class="merchant-application-note">
                                    <span class="merchant-application-note__icon">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <div>
                                        <strong>{{ __('One shop, multiple currencies') }}</strong>
                                        <span>{{ __('Select every active wallet currency your business can accept. The first selected currency becomes the primary settlement currency.') }}</span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="mb-2 single-input-inner style-border">
                                            <label for="business_name" class="form-label">{{ __('Business Name') }}</label>
                                            <input type="text" class="form-control" id="business_name" name="business_name"
                                                   value="{{ old('business_name') }}" required placeholder="{{ __('DigiKash Mart') }}">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-2 single-input-inner style-border">
                                            <label for="business_email" class="form-label">{{ __('Business Email') }}</label>
                                            <input type="email" class="form-control" id="business_email" name="business_email"
                                                   value="{{ old('business_email') }}" required placeholder="{{ __('payments@example.com') }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-2 single-input-inner style-border">
                                            <label for="site_url" class="form-label">{{ __('Website') }}</label>
                                            <input type="url" class="form-control" id="site_url" name="site_url" required
                                                   value="{{ old('site_url') }}" placeholder="{{ __('https://yourwebsite.com') }}">
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
                                                      name="business_description" placeholder="{{ __('Describe your products, checkout flow, and customer use case.') }}">{{ old('business_description') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="merchant-application-actions">
                                    <div>
                                        <strong>{{ __('Ready for merchant review?') }}</strong>
                                        <span>{{ __('Admin approval activates API credentials, checkout links, and production payments.') }}</span>
                                    </div>
                                    <button type="submit" class="btn btn-merchant" @disabled(! $hasCurrencyChoices)>
                                        <x-icon name="check" height="22" /> {{ __('Submit Merchant Request') }}
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
