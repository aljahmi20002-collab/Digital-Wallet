@extends('frontend.layouts.user.index')
@section('title', __('Edit Agent Account'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/agent.css?v=' . config('app.version') . '-' . filemtime(public_path('frontend/css/agent.css'))) }}">
@endpush
@section('content')
    @php
        $hasCurrencyChoices = $currencies->isNotEmpty();
        $selectedCurrencyIds = collect(old('currency_ids', $agent->supportedCurrencyIds()))
            ->map(fn ($id) => (int) $id)
            ->all();
        $logoPreview = asset($agent->logo);
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="card single-form-card agent-service-card">
                <x-user-feature-header
                    :title="__('Edit Agent Account')"
                    :subtitle="__('Keep your single agent account accurate for counter operations.')"
                    icon="fas fa-pen-ruler"
                >
                    <a href="{{ route('user.agent.index', ['tab' => 'counter-cashout']) }}" class="btn btn-light-agent btn-sm">
                        <i class="fas fa-briefcase"></i> {{ __('Agent Services') }}
                    </a>
                </x-user-feature-header>

                <div class="card-main agent-context agent-application agent-application--edit">
                    @unless($hasCurrencyChoices)
                        <x-user-not-found
                            :title="__('No active wallet found')"
                            :message="__('Create or activate a wallet before changing your agent settlement currency.')"
                            icon="fa-wallet"
                            :action-url="route('user.wallet.index')"
                            :action-label="__('Open Wallets')"
                            action-icon="fa-wallet"
                        />
                    @endunless

                    <form action="{{ route('user.agent.update', $agent->id) }}" method="POST" enctype="multipart/form-data" class="agent-application-form">
                        @csrf
                        @method('PUT')

                        <div class="agent-application-main agent-application-main--edit">
                            <div class="agent-application-logo">
                                <div class="agent-field-title">
                                    <span>{{ __('Brand') }}</span>
                                    <strong>{{ __('Agent Logo') }}</strong>
                                    <small>{{ __('Optional, square image recommended') }}</small>
                                </div>
                                <div class="agent-logo-uploader">
                                    <x-img :name="'logo'" :old="$logoPreview" :ref="'agent-logo'"></x-img>
                                </div>
                                <div class="agent-field-summary agent-field-summary--compact">
                                    <img src="{{ $logoPreview }}" alt="{{ $agent->agent_name }}" class="agent-field-summary__logo" loading="lazy">
                                    <div>
                                        <strong>{{ $agent->agent_name }}</strong>
                                        <span>{{ $agent->agent_code }} &middot; {{ $agent->status->label() }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="agent-application-fields">
                                <div class="agent-application-note agent-application-note--compact">
                                    <span class="agent-application-note__icon">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <div>
                                        <strong>{{ __('Agent account details') }}</strong>
                                        <span>{{ __('Update counter name, logo, supported currencies, and operating note. Personal information stays on your user profile.') }}</span>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="single-input-inner style-border">
                                            <label for="agent_name" class="form-label">{{ __('Agent Display Name') }}</label>
                                            <input type="text" class="form-control" id="agent_name" name="agent_name"
                                                   value="{{ old('agent_name', $agent->agent_name) }}" required placeholder="{{ __('DigiKash Uttara Counter') }}">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">{{ __('Supported Currencies') }}</label>
                                        <div class="agent-currency-grid agent-currency-grid--edit">
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

                                    <div class="col-12">
                                        <label for="description" class="form-label">{{ __('Operating Note') }}</label>
                                        <textarea class="form-control h-25" id="description" maxlength="500"
                                                  name="description" placeholder="{{ __('Uttara Sector 7, Dhaka. Cash-in and cash-out available from 9 AM to 8 PM.') }}">{{ old('description', $agent->description) }}</textarea>
                                    </div>
                                </div>

                                <div class="agent-application-actions">
                                    <div>
                                        <strong>{{ __('Save account changes') }}</strong>
                                        <span>{{ __('Counter services will use the selected supported currencies.') }}</span>
                                    </div>
                                    <button type="submit" class="btn btn-agent" @disabled(! $hasCurrencyChoices)>
                                        <x-icon name="check" height="22"/> {{ __('Update Agent Account') }}
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
