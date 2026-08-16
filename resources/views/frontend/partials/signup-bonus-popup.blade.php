@php
    /**
     * @var array{name:string,amount:float,currency:string,tier:string,walletUrl:string,transactionUrl:string,acknowledgeUrl:string}|null $signupBonusPopup
     */
    if (empty($signupBonusPopup)) {
        return;
    }

    $name           = e($signupBonusPopup['name']);
    $amount         = (float) $signupBonusPopup['amount'];
    $currency       = e($signupBonusPopup['currency']);
    $tier           = e($signupBonusPopup['tier']);
    $walletUrl      = $signupBonusPopup['walletUrl'];
    $transactionUrl = $signupBonusPopup['transactionUrl'];
    $acknowledgeUrl = $signupBonusPopup['acknowledgeUrl'];

    $whole      = floor($amount);
    $cents      = str_pad((string) round(($amount - $whole) * 100), 2, '0', STR_PAD_LEFT);
    $moneyStr   = '$'.number_format((float) $whole, 0).'.'.$cents;
    $moneyChars = strlen($moneyStr);

    $heroSizeDesktop = $moneyChars > 7 ? 72 : ($moneyChars > 5 ? 82 : 92);
    $heroSizeMobile  = $moneyChars > 7 ? 52 : ($moneyChars > 5 ? 60 : 68);
@endphp

<div id="signupBonusOverlay"
     class="signup-bonus-overlay"
     data-acknowledge-url="{{ $acknowledgeUrl }}"
     role="presentation">
    <div class="signup-bonus-modal"
         role="dialog"
         aria-modal="true"
         aria-labelledby="signupBonusTitle"
         aria-describedby="signupBonusBody"
         tabindex="-1"
         style="--hero-size-desktop: {{ $heroSizeDesktop }}px; --hero-size-mobile: {{ $heroSizeMobile }}px;">

        <button type="button"
                class="signup-bonus-close"
                data-signup-bonus-close
                aria-label="{{ __('Dismiss welcome bonus') }}">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
        </button>

        <div class="signup-bonus-decoration" aria-hidden="true">
            <svg class="signup-bonus-deco-bg" preserveAspectRatio="none">
                <defs>
                    <pattern id="sbDots" width="14" height="14" patternUnits="userSpaceOnUse">
                        <circle cx="1" cy="1" r="1" fill="#fff" opacity="0.08"/>
                    </pattern>
                    <radialGradient id="sbGlow1" cx="50%" cy="60%" r="50%">
                        <stop offset="0%" stop-color="#60A5FA" stop-opacity=".55"/>
                        <stop offset="60%" stop-color="#60A5FA" stop-opacity="0"/>
                    </radialGradient>
                    <radialGradient id="sbGlow2" cx="80%" cy="20%" r="40%">
                        <stop offset="0%" stop-color="#22D3EE" stop-opacity=".35"/>
                        <stop offset="100%" stop-color="#22D3EE" stop-opacity="0"/>
                    </radialGradient>
                    <radialGradient id="sbGlow3" cx="15%" cy="85%" r="40%">
                        <stop offset="0%" stop-color="#34D399" stop-opacity=".30"/>
                        <stop offset="100%" stop-color="#34D399" stop-opacity="0"/>
                    </radialGradient>
                </defs>
                <rect width="100%" height="100%" fill="url(#sbGlow1)"/>
                <rect width="100%" height="100%" fill="url(#sbGlow2)"/>
                <rect width="100%" height="100%" fill="url(#sbGlow3)"/>
                <rect width="100%" height="100%" fill="url(#sbDots)"/>
            </svg>

            <span class="signup-bonus-confetti sb-c1"></span>
            <span class="signup-bonus-confetti sb-c2"></span>
            <span class="signup-bonus-confetti sb-c3"></span>
            <span class="signup-bonus-confetti sb-c4"></span>
            <span class="signup-bonus-confetti sb-c5"></span>
            <span class="signup-bonus-confetti sb-c6"></span>
            <span class="signup-bonus-confetti sb-c7"></span>
            <span class="signup-bonus-confetti sb-c8"></span>

            <div class="signup-bonus-coin-wrap">
                <span class="signup-bonus-coin-pad"></span>
                <svg class="signup-bonus-coin" viewBox="0 0 100 100" aria-hidden="true">
                    <defs>
                        <radialGradient id="sbCoinFace" cx="35%" cy="30%" r="80%">
                            <stop offset="0%"   stop-color="#FEF3C7"/>
                            <stop offset="40%"  stop-color="#FCD34D"/>
                            <stop offset="80%"  stop-color="#F59E0B"/>
                            <stop offset="100%" stop-color="#B45309"/>
                        </radialGradient>
                        <linearGradient id="sbCoinRim" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%"   stop-color="#FBBF24"/>
                            <stop offset="100%" stop-color="#92400E"/>
                        </linearGradient>
                        <linearGradient id="sbCoinShine" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#FFFFFF" stop-opacity=".85"/>
                            <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <circle cx="50" cy="50" r="48" fill="url(#sbCoinRim)"/>
                    <circle cx="50" cy="50" r="42" fill="url(#sbCoinFace)"/>
                    <circle cx="50" cy="50" r="38" fill="none" stroke="#92400E" stroke-opacity=".35" stroke-width="1.2"/>
                    <text x="50" y="66" text-anchor="middle" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-weight="800" font-size="48" fill="#FEF3C7" opacity=".55">$</text>
                    <text x="50" y="65" text-anchor="middle" font-family="'Plus Jakarta Sans', system-ui, sans-serif" font-weight="800" font-size="48" fill="#7C2D12">$</text>
                    <ellipse cx="38" cy="32" rx="18" ry="8" fill="url(#sbCoinShine)" opacity=".55"/>
                </svg>
            </div>

            <svg class="signup-bonus-sparkle sb-s1" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 L13.6 9.4 L21 11 L13.6 12.6 L12 22 L10.4 12.6 L3 11 L10.4 9.4 Z" fill="#FCD34D"/></svg>
            <svg class="signup-bonus-sparkle sb-s2" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 L13.6 9.4 L21 11 L13.6 12.6 L12 22 L10.4 12.6 L3 11 L10.4 9.4 Z" fill="#A7F3D0"/></svg>
            <svg class="signup-bonus-sparkle sb-s3" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 L13.6 9.4 L21 11 L13.6 12.6 L12 22 L10.4 12.6 L3 11 L10.4 9.4 Z" fill="#FCD34D"/></svg>
            <svg class="signup-bonus-sparkle sb-s4" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 L13.6 9.4 L21 11 L13.6 12.6 L12 22 L10.4 12.6 L3 11 L10.4 9.4 Z" fill="#93C5FD"/></svg>
            <svg class="signup-bonus-sparkle sb-s5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 L13.6 9.4 L21 11 L13.6 12.6 L12 22 L10.4 12.6 L3 11 L10.4 9.4 Z" fill="#FFFFFF"/></svg>
            <svg class="signup-bonus-sparkle sb-s6" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2 L13.6 9.4 L21 11 L13.6 12.6 L12 22 L10.4 12.6 L3 11 L10.4 9.4 Z" fill="#A7F3D0"/></svg>
        </div>

        <div class="signup-bonus-body" id="signupBonusBody">
            <div class="signup-bonus-eyebrow">
                <span class="signup-bonus-eyebrow-dot" aria-hidden="true"></span>
                {{ $tier }}
            </div>

            <h2 id="signupBonusTitle" class="signup-bonus-title">
                {{ __('Welcome to') }} <span class="signup-bonus-brand">DigiKash</span>,<br/>
                {{ $name }}!
            </h2>

            <div class="signup-bonus-hero-wrap">
                <span class="signup-bonus-hero-glow" aria-hidden="true"></span>
                <span class="signup-bonus-hero-amount">{{ $moneyStr }}</span>
                <span class="signup-bonus-hero-currency">{{ $currency }}</span>
            </div>

            <p class="signup-bonus-subtitle">
                {{ __('Your welcome bonus has been added to your wallet. Spend it anywhere on DigiKash.') }}
            </p>

            <div class="signup-bonus-actions">
                <a href="{{ $walletUrl }}"
                   class="signup-bonus-btn signup-bonus-btn-primary"
                   data-signup-bonus-close>
                    <span class="signup-bonus-btn-spinner" aria-hidden="true"></span>
                    <svg class="signup-bonus-btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 7h15a3 3 0 0 1 3 3v7a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7z"/>
                        <path d="M3 7V5a2 2 0 0 1 2-2h11"/>
                        <circle cx="17" cy="14" r="1.4" fill="currentColor" stroke="none"/>
                    </svg>
                    <span class="signup-bonus-btn-label">{{ __('View My Wallet') }}</span>
                    <span class="signup-bonus-btn-label-loading">{{ __('Opening wallet…') }}</span>
                </a>

                <a href="{{ $transactionUrl }}"
                   class="signup-bonus-btn signup-bonus-btn-secondary"
                   data-signup-bonus-close>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 3h11l3 3v15l-3-2-2 2-2-2-2 2-2-2-3 2V3z"/>
                        <path d="M8 8h8M8 12h8M8 16h5"/>
                    </svg>
                    <span>{{ __('See Transaction') }}</span>
                </a>
            </div>
        </div>
    </div>
</div>
