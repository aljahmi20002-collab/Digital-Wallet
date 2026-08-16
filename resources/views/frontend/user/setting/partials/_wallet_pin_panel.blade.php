@if($hasPin)
    <form id="wallet-pin-reset-form" action="{{ route('user.settings.wallet-pin.reset') }}" method="post" class="d-none">
        @csrf
    </form>
@endif

<section class="settings-status-card settings-status-card--{{ $hasPin ? 'enabled' : 'disabled' }} wallet-pin-status mb-3">
    <div class="settings-status-card__icon" aria-hidden="true">
        <i class="fas fa-key"></i>
    </div>
    <div class="settings-status-card__body">
        <h6 class="settings-status-card__title">
            {{ $hasPin ? __('Wallet PIN is active') : __('Wallet PIN is not set') }}
        </h6>
        <p class="settings-status-card__text">
            {{ __('Use a private 6-digit PIN to authorise wallet payments without exposing your login password.') }}
        </p>
    </div>
    <span class="settings-badge {{ $hasPin ? 'settings-badge--success' : 'settings-badge--muted' }}">
        <i class="fas {{ $hasPin ? 'fa-check' : 'fa-times-circle' }}" aria-hidden="true"></i>
        {{ $hasPin ? __('Protected') : __('Setup Required') }}
    </span>
</section>

<div class="row g-3">
    <div class="col-lg-4 order-lg-2">
        <aside class="settings-tips wallet-pin-tips">
            <div class="wallet-pin-tips__visual" aria-hidden="true">
                <span class="wallet-pin-tips__key">
                    <i class="fas fa-key"></i>
                </span>
                <span class="wallet-pin-tips__dots">
                    <span></span><span></span><span></span><span></span><span></span><span></span>
                </span>
            </div>

            <h6 class="settings-tips__title">
                <i class="fas fa-lock" aria-hidden="true"></i>
                {{ __('PIN Standards') }}
            </h6>
            <ul class="settings-tips__list">
                <li>{{ __('Use exactly 6 digits.') }}</li>
                <li>{{ __('Avoid repeated or sequential numbers.') }}</li>
                <li>{{ __('Keep this PIN separate from your login password.') }}</li>
                <li>{{ __('Reset by email if you forget it.') }}</li>
            </ul>
        </aside>
    </div>

    <div class="col-lg-8 order-lg-1">
        <form id="wallet-pin-update-form" action="{{ route('user.settings.wallet-pin.update') }}" method="post" class="wallet-pin-form">
            @csrf

            <section class="settings-section wallet-pin-card">
                <header class="settings-section__header wallet-pin-card__header">
                    <div>
                        <h6 class="settings-section__title">
                            {{ $hasPin ? __('Change Wallet PIN') : __('Set Wallet PIN') }}
                        </h6>
                        <p class="settings-section__subtitle">
                            {{ __('Confirm your current credential, then choose a private PIN for wallet checkout approvals.') }}
                        </p>
                    </div>
                    <span class="settings-badge settings-badge--info">
                        <i class="fas fa-key" aria-hidden="true"></i>
                        {{ __('6 Digits') }}
                    </span>
                </header>

                <div class="settings-section__body wallet-pin-card__body">
                    @if(isset($errors) && $errors->any())
                        <div class="settings-inline-alert settings-inline-alert--danger mb-3">
                            <span class="settings-inline-alert__icon" aria-hidden="true">
                                <i class="fas fa-triangle-exclamation"></i>
                            </span>
                            <div class="settings-inline-alert__body">
                                <strong class="settings-inline-alert__title">{{ __('Please review these fields') }}</strong>
                                <ul class="wallet-pin-errors">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div class="wallet-pin-field mb-3">
                        <label class="form-label" for="current-credential">
                            @if($hasPin)
                                {{ __('Current Wallet PIN or Login Password') }}
                            @else
                                {{ __('Login Password') }}
                            @endif
                        </label>
                        <div class="wallet-pin-input-wrap">
                            <span class="wallet-pin-input-wrap__icon" aria-hidden="true">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password"
                                   name="current_credential"
                                   id="current-credential"
                                   class="form-control"
                                   autocomplete="current-password"
                                   placeholder="{{ $hasPin ? __('Enter current PIN or password') : __('Enter login password') }}"
                                   required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="wallet-pin-field">
                                <label class="form-label" for="pin">{{ __('New 6-Digit PIN') }}</label>
                                <div class="wallet-pin-input-wrap">
                                    <span class="wallet-pin-input-wrap__icon" aria-hidden="true">
                                        <i class="fas fa-key"></i>
                                    </span>
                                    <input type="password"
                                           name="pin"
                                           id="pin"
                                           class="form-control wallet-pin-input"
                                           inputmode="numeric"
                                           autocomplete="new-password"
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           placeholder="000000"
                                           required>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="wallet-pin-field">
                                <label class="form-label" for="pin_confirmation">{{ __('Confirm New PIN') }}</label>
                                <div class="wallet-pin-input-wrap">
                                    <span class="wallet-pin-input-wrap__icon" aria-hidden="true">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <input type="password"
                                           name="pin_confirmation"
                                           id="pin_confirmation"
                                           class="form-control wallet-pin-input"
                                           inputmode="numeric"
                                           autocomplete="new-password"
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           placeholder="000000"
                                           required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <footer class="settings-actions wallet-pin-actions">
                @if($hasPin)
                    <button type="submit" form="wallet-pin-reset-form" class="wallet-pin-reset-link">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                        <span>{{ __('Email Reset Link') }}</span>
                    </button>
                @else
                    <p class="settings-actions__hint">
                        <i class="fas fa-circle-info" aria-hidden="true"></i>
                        {{ __('You can change this PIN from settings anytime.') }}
                    </p>
                @endif

                <button type="submit" class="btn btn-primary settings-actions__submit">
                    <i class="fas fa-check" aria-hidden="true"></i>
                    <span>{{ $hasPin ? __('Update PIN') : __('Set PIN') }}</span>
                </button>
            </footer>
        </form>
    </div>
</div>
