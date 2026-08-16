@php
    $kycState = $kycNoticeCard['state'];
    $isKycApproved = $kycNoticeCard['is_approved'];
    $dismissKey = $kycNoticeCard['dismiss_key'];
    $kycNotice = $kycNoticeCard['notice'];
@endphp

<div class="main-notice-card kyc-notice-card kyc-notice-card--{{ $kycState }} mt-3"
     aria-live="polite"
    @if($dismissKey) hidden @endif
    @if($dismissKey) data-kyc-verified-notice data-kyc-dismiss-key="{{ $dismissKey }}" @endif>
    <div class="kyc-notice-card__icon-box" aria-hidden="true">
        <i class="fas {{ $kycNotice['icon'] }}"></i>
    </div>

    <div class="kyc-notice-card__content">
        <h6 class="kyc-notice-card__title">{{ $kycNotice['title'] }}</h6>
        <p class="kyc-notice-card__text">{{ $kycNotice['message'] }}</p>
    </div>

    <div class="kyc-notice-card__progress" aria-label="{{ $kycNotice['step'] }}">
        @if($isKycApproved)
            <span class="kyc-notice-card__status-pill">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span>{{ $kycNotice['step'] }}</span>
            </span>
        @else
            <span class="kyc-notice-card__step">{{ $kycNotice['step'] }}</span>
            <span class="kyc-notice-card__track" aria-hidden="true">
                <span class="kyc-notice-card__bar"></span>
            </span>
        @endif
    </div>

    <div class="kyc-notice-card__action">
        <a href="{{ route('user.settings.kyc.verify') }}" class="kyc-notice-card__btn">
            <i class="fas {{ $kycNotice['cta_icon'] }}" aria-hidden="true"></i>
            <span>{{ $kycNotice['cta'] }}</span>
        </a>

        @if($dismissKey)
            <button type="button"
                    class="kyc-notice-card__dismiss"
                    data-kyc-notice-dismiss
                    aria-label="{{ __('Hide verified KYC notice') }}">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        @endif
    </div>
</div>
