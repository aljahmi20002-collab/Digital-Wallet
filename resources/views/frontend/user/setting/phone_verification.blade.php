@extends('frontend.user.setting.index')
@section('title', __('Phone Verification'))

@section('user_setting_content')
    @php
        $isVerified = $user->hasVerifiedPhone();
        $isEnabled = $user->hasEnabledPhoneVerification();
    @endphp

    @include('frontend.user.setting.partials._security_tabs')

    @if(empty($user->phone))
        <x-user-not-found
            :title="__('No phone number added')"
            :message="__('Add your phone number from profile settings before requesting a verification code.')"
            icon="fa-mobile-screen-button"
            :action-url="route('user.settings.profile')"
            :action-label="__('Update Profile')"
            action-icon="fa-user"
        />
    @else
        <section class="settings-status-card settings-status-card--{{ $isEnabled ? 'enabled' : 'disabled' }} phone-security-hero mb-3">
            <div class="settings-status-card__icon" aria-hidden="true">
                <x-icon name="phone-verification" class="settings-icon-svg" height="24" width="24" />
            </div>
            <div class="settings-status-card__body">
                <h6 class="settings-status-card__title">
                    {{ $isEnabled ? __('Phone verification is enabled') : __('Phone verification is turned off') }}
                </h6>
                <p class="settings-status-card__text">
                    {{ __('Use your verified phone as an extra protection layer for sensitive wallet actions like mobile recharge.') }}
                </p>
                <div class="phone-security-meta">
                    <span><i class="fas fa-mobile-screen-button"></i> {{ $user->phone }}</span>
                    <span><i class="fas {{ $isVerified ? 'fa-circle-check' : 'fa-circle-exclamation' }}"></i> {{ $isVerified ? __('Number verified') : __('Number not verified') }}</span>
                </div>
            </div>
            <span class="settings-badge {{ $isEnabled ? 'settings-badge--success' : 'settings-badge--danger' }}">
                <i class="fas {{ $isEnabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                {{ $isEnabled ? __('Enabled') : __('Disabled') }}
            </span>
        </section>

        <div class="phone-security-grid mb-3">
            <section class="phone-security-step {{ $isVerified ? 'is-complete' : 'needs-action' }}">
                <span class="phone-security-step__icon">
                    <i class="fas {{ $isVerified ? 'fa-circle-check' : 'fa-mobile-screen-button' }}"></i>
                </span>
                <div>
                    <h6>{{ __('Verify Number') }}</h6>
                    <p>{{ $isVerified ? __('This number has already passed code verification.') : __('Receive a short code on your saved phone number.') }}</p>
                </div>
            </section>
            <section class="phone-security-step {{ $isEnabled ? 'is-complete' : 'needs-action' }}">
                <span class="phone-security-step__icon">
                    <i class="fas {{ $isEnabled ? 'fa-shield-alt' : 'fa-shield-alt' }}"></i>
                </span>
                <div>
                    <h6>{{ __('Protection Switch') }}</h6>
                    <p>{{ $isEnabled ? __('Phone verification is active for protected wallet actions.') : __('Enter the latest code to turn protection on.') }}</p>
                </div>
            </section>
        </div>

        @if($isEnabled)
            <section class="settings-section phone-security-disable">
                <header class="settings-section__header">
                    <div>
                        <h6 class="settings-section__title">{{ __('Disable Phone Verification') }}</h6>
                        <p class="settings-section__subtitle">
                            {{ __('Your number will stay verified, but protected actions will stop requiring phone verification until you enable it again.') }}
                        </p>
                    </div>
                    <span class="settings-badge settings-badge--info">
                        <i class="fas fa-key"></i>
                        {{ __('Password Required') }}
                    </span>
                </header>
                <div class="settings-section__body">
                    @if($errors->has('password'))
                        <div class="settings-inline-alert settings-inline-alert--danger mb-3" role="alert">
                            <span class="settings-inline-alert__icon" aria-hidden="true">
                                <i class="fas fa-triangle-exclamation"></i>
                            </span>
                            <div class="settings-inline-alert__body">
                                <strong class="settings-inline-alert__title">{{ __('Could not disable') }}</strong>
                                <span class="settings-inline-alert__text">{{ $errors->first('password') }}</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('user.settings.phone.disable') }}" method="POST">
                        @csrf
                        <div class="single-input-inner style-border mb-3">
                            <label for="phone-disable-password" class="form-label">{{ __('Current Password') }}</label>
                            <input
                                type="password"
                                id="phone-disable-password"
                                name="password"
                                class="form-control"
                                autocomplete="current-password"
                                placeholder="{{ __('Enter your current password') }}"
                                required
                            >
                        </div>
                        <button type="submit" class="btn btn-danger phone-security-disable__button">
                            <i class="fas fa-toggle-off me-1"></i>
                            {{ __('Disable Phone Verification') }}
                        </button>
                    </form>
                </div>
            </section>
        @else
            <div class="row g-3 phone-security-enable">
                <div class="col-lg-5">
                    <section class="settings-section h-100">
                        <header class="settings-section__header">
                            <div>
                                <h6 class="settings-section__title">{{ __('Step 1: Send Code') }}</h6>
                                <p class="settings-section__subtitle">
                                    {{ __('Request a fresh code for :phone.', ['phone' => $user->phone]) }}
                                </p>
                            </div>
                        </header>
                        <div class="settings-section__body">
                            <form action="{{ route('user.settings.phone.send') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 phone-security-action">
                                    <i class="fas fa-paper-plane me-1"></i>
                                    {{ $isVerified ? __('Send Enable Code') : __('Send Verification Code') }}
                                </button>
                            </form>
                            <p class="phone-security-helper">
                                {{ __('Codes expire quickly, so request a new one only when you are ready to enter it.') }}
                            </p>
                        </div>
                    </section>
                </div>

                <div class="col-lg-7">
                    <section class="settings-section h-100">
                        <header class="settings-section__header">
                            <div>
                                <h6 class="settings-section__title">{{ __('Step 2: Turn On') }}</h6>
                                <p class="settings-section__subtitle">
                                    {{ __('Enter the latest code to enable phone verification for this account.') }}
                                </p>
                            </div>
                        </header>
                        <div class="settings-section__body">
                            <form action="{{ route('user.settings.phone.confirm') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="phone-verification-code" class="form-label">{{ __('Verification Code') }}</label>
                                    <input
                                        type="text"
                                        id="phone-verification-code"
                                        name="code"
                                        class="form-control"
                                        value="{{ old('code') }}"
                                        inputmode="numeric"
                                        autocomplete="one-time-code"
                                        placeholder="{{ __('Enter 6 digit code') }}"
                                        required
                                    >
                                </div>
                                <button type="submit" class="btn btn-base w-100 phone-security-action">
                                    <i class="fas fa-check me-1"></i>
                                    {{ __('Enable Phone Verification') }}
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        @endif
    @endif
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('frontend/css/mobile-recharge.css?v=' . config('app.version')) }}">
@endpush
