@extends('frontend.user.setting.index')
@section('title', __('2FA Security'))

@section('user_setting_content')
    @php
        $isTwoFactorEnabled = (bool) auth()->user()->two_factor_enabled;
    @endphp

    @include('frontend.user.setting.partials._security_tabs')

    <section class="settings-status-card settings-status-card--{{ $isTwoFactorEnabled ? 'enabled' : 'disabled' }} mb-3">
        <div class="settings-status-card__icon" aria-hidden="true">
            <x-icon name="shield" class="settings-icon-svg" height="18" width="18" />
        </div>
        <div class="settings-status-card__body">
            <h6 class="settings-status-card__title">
                @if($isTwoFactorEnabled)
                    {{ __('Two-Factor Authentication is enabled') }}
                @else
                    {{ __('Two-Factor Authentication is not active') }}
                @endif
            </h6>
            <p class="settings-status-card__text">
                @if($isTwoFactorEnabled)
                    {{ __('Your account is protected by an authenticator code in addition to your password.') }}
                @else
                    {{ __('Add a second layer of protection by linking an authenticator app to your account.') }}
                @endif
            </p>
        </div>
        <span class="settings-badge {{ $isTwoFactorEnabled ? 'settings-badge--success' : 'settings-badge--muted' }}">
            <x-icon
                :name="$isTwoFactorEnabled ? 'check' : 'x-circle'"
                class="settings-icon-svg"
                height="14"
                width="14"
            />
            {{ $isTwoFactorEnabled ? __('Active') : __('Inactive') }}
        </span>
    </section>

    @if($isTwoFactorEnabled)
        <section class="settings-section">
            <header class="settings-section__header">
                <div>
                    <h6 class="settings-section__title">{{ __('Disable Two-Factor Authentication') }}</h6>
                    <p class="settings-section__subtitle">
                        {{ __('Confirm your password to remove 2FA from your account.') }}
                    </p>
                </div>
            </header>

            <div class="settings-section__body">
                <div class="settings-inline-alert settings-inline-alert--danger mb-3">
                    <span class="settings-inline-alert__icon" aria-hidden="true">
                        <x-icon name="warning" class="settings-icon-svg" height="16" width="16" />
                    </span>
                    <div class="settings-inline-alert__body">
                        <strong class="settings-inline-alert__title">{{ __('Security will be reduced') }}</strong>
                        <span class="settings-inline-alert__text">
                            {{ __('Disabling 2FA removes an additional protection layer. We recommend keeping it enabled whenever possible.') }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('user.settings.2fa.disable') }}" method="POST" class="needs-validation">
                    @csrf
                    <div class="mb-3">
                        <label for="twofa-disable-password" class="form-label">{{ __('Current Password') }}</label>
                        <input type="password" id="twofa-disable-password" name="password"
                               class="form-control" placeholder="{{ __('Enter your current password') }}" required
                               autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-danger w-100">
                        <x-icon name="security-off" class="settings-icon-svg me-1" height="16" width="16" />
                        {{ __('Disable 2FA') }}
                    </button>
                </form>
            </div>
        </section>
    @else
        <section class="settings-section">
            <header class="settings-section__header">
                <div>
                    <h6 class="settings-section__title">{{ __('Set up an Authenticator App') }}</h6>
                    <p class="settings-section__subtitle">
                        {{ __('Follow these steps to link your preferred authenticator app.') }}
                    </p>
                </div>
            </header>

            <div class="settings-section__body">
                <ol class="settings-steps">
                    <li class="settings-steps__item">
                        <span class="settings-steps__number">1</span>
                        <div class="settings-steps__body">
                            <strong class="settings-steps__title">{{ __('Install an authenticator app') }}</strong>
                            <p class="settings-steps__text">
                                {{ __('Download Google Authenticator, Authy or any TOTP-compatible app from your app store.') }}
                            </p>
                        </div>
                    </li>
                    <li class="settings-steps__item">
                        <span class="settings-steps__number">2</span>
                        <div class="settings-steps__body">
                            <strong class="settings-steps__title">{{ __('Scan the QR code or enter the key') }}</strong>
                            <div class="settings-2fa-scan">
                                <div class="settings-2fa-scan__qr">
                                    <img src="{{ $qrCode }}"
                                         alt="{{ __('2FA QR Code') }}"
                                         class="settings-2fa-scan__qr-image" loading="lazy">
                                </div>
                                <div class="settings-2fa-scan__manual">
                                    <div class="settings-2fa-scan__row">
                                        <span class="settings-2fa-scan__label">{{ __('Account') }}</span>
                                        <span class="settings-2fa-scan__value">{{ setting('site_title') }}</span>
                                    </div>
                                    <div class="settings-2fa-scan__row">
                                        <span class="settings-2fa-scan__label">{{ __('Secret Key') }}</span>
                                        <code class="settings-2fa-scan__secret" data-copy>{{ $secret }}</code>
                                        <button type="button" class="btn btn-sm btn-light settings-2fa-scan__copy"
                                                data-copy-target="{{ $secret }}"
                                                data-copy-default-label="{{ __('Copy secret') }}"
                                                data-copy-success-label="{{ __('Secret copied') }}"
                                                data-copy-failed-message="{{ __('Unable to copy the secret key. Please copy it manually.') }}"
                                                title="{{ __('Copy secret') }}"
                                                aria-label="{{ __('Copy secret') }}">
                                            <span class="settings-2fa-scan__copy-icon settings-2fa-scan__copy-icon--default" data-copy-icon-default aria-hidden="true">
                                                <x-icon name="clipboard" class="settings-icon-svg" height="16" width="16" />
                                            </span>
                                            <span class="settings-2fa-scan__copy-icon settings-2fa-scan__copy-icon--success d-none" data-copy-icon-success aria-hidden="true">
                                                <x-icon name="check" class="settings-icon-svg" height="16" width="16" />
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="settings-steps__item">
                        <span class="settings-steps__number">3</span>
                        <div class="settings-steps__body">
                            <strong class="settings-steps__title">{{ __('Enter the 6-digit code to verify') }}</strong>
                            <p class="settings-steps__text">
                                {{ __('Your authenticator app generates a new code every 30 seconds.') }}
                            </p>

                            <form action="{{ route('user.settings.2fa.enable') }}" method="POST" class="mt-3 needs-validation">
                                @csrf
                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    <input type="text"
                                           name="verification_code"
                                           class="form-control settings-2fa-code"
                                           placeholder="------"
                                           inputmode="numeric"
                                           pattern="[0-9]{6}"
                                           maxlength="6"
                                           autocomplete="one-time-code"
                                           required>
                                    <button type="submit" class="btn btn-primary">
                                        <span aria-hidden="true">
                                            <x-icon name="shield" class="settings-icon-svg" height="18" width="18" />
                                        </span>
                                        {{ __('Enable 2FA') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </li>
                </ol>
            </div>
        </section>
    @endif
@endsection

@push('scripts')
    <script src="{{ asset('frontend/js/settings-2fa-security.js') }}"></script>
@endpush
