@extends('frontend.layouts.user.index')
@section('title', __('Agent Application'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
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
            <div class="card single-form-card agent-service-card">
                <x-user-feature-header
                    :title="__('Agent Application')"
                    :subtitle="__('Apply once for your agent account and start counter services after approval.')"
                    icon="fas fa-user-tie"
                >
                    <a href="{{ route('user.agent.index', ['tab' => 'counter-cashout']) }}" class="btn btn-light-agent btn-sm">
                        <i class="fas fa-briefcase"></i> {{ __('Agent Services') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main agent-context agent-application">
                    <div class="agent-application-hero">
                        <div class="agent-application-hero__intro">
                            <span class="agent-application-hero__eyebrow">
                                <i class="fa-solid fa-id-badge"></i>
                                {{ __('Single agent account') }}
                            </span>
                            <h2>{{ __('Open your agent counter') }}</h2>
                            <p>{{ __('Submit your display name, supported currencies, and operating note. Your personal details come from your verified user profile.') }}</p>
                        </div>

                        <div class="agent-process-rail" aria-label="{{ __('Application steps') }}">
                            <div class="agent-process-step is-active">
                                <span>1</span>
                                <div>
                                    <strong>{{ __('Submit') }}</strong>
                                    <small>{{ __('Application') }}</small>
                                </div>
                            </div>
                            <div class="agent-process-step">
                                <span>2</span>
                                <div>
                                    <strong>{{ __('Review') }}</strong>
                                    <small>{{ __('Admin check') }}</small>
                                </div>
                            </div>
                            <div class="agent-process-step">
                                <span>3</span>
                                <div>
                                    <strong>{{ __('Operate') }}</strong>
                                    <small>{{ __('Cash tools') }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @unless($hasCurrencyChoices)
                        <x-user-not-found
                            :title="__('No active wallet found')"
                            :message="__('Create or activate a wallet first. Your agent currency must match one of your active wallets.')"
                            icon="fa-wallet"
                            :action-url="route('user.wallet.index')"
                            :action-label="__('Open Wallets')"
                            action-icon="fa-wallet"
                        />
                    @endunless

                    <form action="{{ route('user.agent.store') }}" method="POST" enctype="multipart/form-data" class="agent-application-form">
                        @csrf

                        <div class="agent-application-main">
                            <div class="agent-application-logo">
                                <div class="agent-field-title">
                                    <span>{{ __('Brand') }}</span>
                                    <strong>{{ __('Agent Logo') }}</strong>
                                    <small>{{ __('Optional, square image recommended') }}</small>
                                </div>
                                <div class="agent-logo-uploader">
                                    <x-img :name="'logo'" :old="old('logo')" :ref="'agent-logo'"></x-img>
                                </div>
                                <div class="agent-upload-hint">
                                    <i class="fas fa-upload"></i>
                                    <span>{{ __('PNG, JPG, SVG. Maximum 1 MB.') }}</span>
                                </div>
                                <div class="agent-demo-request">
                                    <span><i class="fa-solid fa-location-dot"></i> {{ __('Demo request') }}</span>
                                    <strong>{{ __('DigiKash Uttara Counter') }}</strong>
                                    <small>{{ __('USD, EUR, SAR - Uttara Sector 7 - 9 AM to 8 PM') }}</small>
                                </div>
                            </div>

                            <div class="agent-application-fields">
                                <div class="agent-application-note">
                                    <span class="agent-application-note__icon">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <div>
                                        <strong>{{ __('One account per user') }}</strong>
                                        <span>{{ __('Use one agent account for every selected wallet currency. You can manage the account from Agent Services after submission.') }}</span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="mb-2 single-input-inner style-border">
                                            <label for="agent_name" class="form-label">{{ __('Agent Display Name') }}</label>
                                            <input type="text" class="form-control" id="agent_name" name="agent_name"
                                                   value="{{ old('agent_name') }}" required placeholder="{{ __('DigiKash Uttara Counter') }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="mb-2">
                                            <label class="form-label">{{ __('Supported Currencies') }}
                                                <i data-bs-toggle="tooltip" data-bs-placement="top"
                                                   title="{{ __('Select every active wallet currency this agent can operate with.') }}"
                                                   class="fa-solid fa-circle-info ms-1"></i>
                                            </label>

                                            <div class="agent-currency-grid">
                                                @foreach($currencies as $currency)
                                                    @php($currencyInputId = 'agent_currency_'.$currency->id)
                                                    <input
                                                        type="checkbox"
                                                        class="agent-currency-card__input"
                                                        id="{{ $currencyInputId }}"
                                                        name="currency_ids[]"
                                                        value="{{ $currency->id }}"
                                                        @checked(in_array((int) $currency->id, $selectedCurrencyIds, true))
                                                        @disabled(! $hasCurrencyChoices)
                                                    >
                                                    <label class="agent-currency-card" for="{{ $currencyInputId }}">
                                                        <span class="agent-currency-card__mark"><i class="fas fa-check"></i></span>
                                                        <span class="agent-currency-card__body">
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
                                            <label for="description" class="form-label">{{ __('Operating Note') }}</label>
                                            <textarea class="form-control h-25" id="description" maxlength="500"
                                                      name="description" placeholder="{{ __('Uttara Sector 7, Dhaka. Cash-in and cash-out available from 9 AM to 8 PM.') }}">{{ old('description') }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="agent-application-actions">
                                    <div>
                                        <strong>{{ __('Ready to send for review?') }}</strong>
                                        <span>{{ __('Admin will activate your counter services after approval.') }}</span>
                                    </div>
                                    <button type="submit" class="btn btn-agent" @disabled(! $hasCurrencyChoices)>
                                        <x-icon name="check" height="22"/> {{ __('Submit Application') }}
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
