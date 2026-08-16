<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __(':sender sent you a gift', ['sender' => $giftCard->sender_name]) }} · {{ config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/gift-card.css') }}?v={{ filemtime(public_path('assets/frontend/css/gift-card.css')) }}">

    <style>body { margin: 0; padding: 0; }</style>
</head>
<body class="gc-public">

    @php
        $preset      = $giftCard->template?->preset_key ?? 'premium';
        $symbol      = $giftCard->currency?->symbol ?? '$';
        $isRedeemed  = in_array($giftCard->status, ['redeemed', 'expired', 'cancelled'], true);
        $firstName   = explode(' ', trim((string) $giftCard->sender_name))[0] ?: __('Someone');
        $initials    = strtoupper(mb_substr($giftCard->sender_name, 0, 2, 'UTF-8'));
        $amountLabel = $symbol.number_format((float) $giftCard->amount, 2);
    @endphp

    {{-- Page-level confetti — drifts down behind the card. The card
         itself has its own internal motif (confetti / snow / sparkles)
         baked into the <x-gift-card-design> component. --}}
    <div class="gc-public__confetti" aria-hidden="true">
        @for($i = 0; $i < 60; $i++)
            @php
                $left     = ($i * 13) % 100;
                $delay    = ($i % 10) * 0.2;
                $duration = 3 + ($i % 4);
                $colors   = ['#FDE68A','#FCA5A5','#A7F3D0','#BFDBFE','#FBCFE8','#FDBA74','#A5B4FC'];
                $size     = 6 + ($i % 5) * 2;
                $height   = ($i % 2) === 0 ? 4 : 9;
                $radius   = $i % 3 === 2 ? '50%' : '1.5px';
            @endphp
            <span style="left:{{ $left }}%; top:-30px; width:{{ $size }}px; height:{{ $height }}px; background:{{ $colors[$i % 7] }}; border-radius:{{ $radius }}; animation-duration:{{ $duration }}s; animation-delay:{{ $delay }}s;"></span>
        @endfor
    </div>

    @php
        /*
         * Use the site title as plain text instead of the configured logo
         * image. Logo files are user-uploaded and can be any colour /
         * variant (dark, light, multi-tone) — there's no reliable way to
         * guarantee contrast on the dark recipient page across every
         * possible upload. A typographic wordmark in white sidesteps the
         * problem entirely and stays sharp at any DPI.
         */
        $previewBrand = setting('site_title') ?: config('app.name');
    @endphp
    <header class="gc-public-header">
        <a href="{{ route('home') }}" class="gc-public__brand">
            <span class="gc-brand-name">{{ $previewBrand }}</span>
        </a>
    </header>

    <div class="gc-content">

        <div class="gc-public-pill">
            {{-- fa-sparkles is Pro-only; fa-star ships in FA Free. --}}
            <i class="fa-solid fa-star" aria-hidden="true"></i> {{ __('Just for you') }}
        </div>

        <h1>{{ __(':sender sent you a gift', ['sender' => $firstName]) }}</h1>
        <p class="gc-public-sub">{{ __('Open your gift card and add it to your DigiKash wallet in seconds.') }}</p>

        {{-- Sender chip --}}
        <div class="gc-public__sender">
            <div class="gc-public__sender-avatar">{{ $initials }}</div>
            <div class="gc-public__sender-meta">
                <div class="gc-public__sender-label">{{ __('From') }}</div>
                <div class="gc-public__sender-name">{{ $giftCard->sender_name }}</div>
            </div>
        </div>

        {{-- Gift card — rendered already-opened with the code stripe
             visible on the card itself (showCode prop). The card-rise
             animation eases it up on page load to match the target. --}}
        <div class="gc-public__card-wrap">
            <x-gift-card-design
                :preset="$preset"
                :amount="$giftCard->amount"
                :currency-symbol="$symbol"
                :recipient="$giftCard->recipient_name"
                :sender="$giftCard->sender_name"
                :code="$giftCard->code"
                :show-code="true"
                :width="420"
            />
        </div>

        @if($giftCard->message)
            <div class="gc-message-box">
                <div class="gc-message-eyebrow">✦ {{ __('A message for you') }}</div>
                <p class="gc-message-text">“{{ $giftCard->message }}”</p>
            </div>
        @endif

        <div class="gc-code-box">
            <div class="gc-code-box__meta">
                <div class="gc-code-box__label">{{ __('Gift code') }}</div>
                <div class="gc-code-box__code">{{ $giftCard->code }}</div>
            </div>
            <button type="button"
                    class="gc-code-box__copy js-gc-copy"
                    data-copy="{{ $giftCard->code }}"
                    data-copy-label="{{ __('Copy') }}"
                    data-copied-label="{{ __('Copied') }}">
                <i class="fa-solid fa-copy" aria-hidden="true"></i>
                <span class="js-gc-copy-label">{{ __('Copy') }}</span>
            </button>
        </div>

        @if($isRedeemed)
            <div class="gc-public__notice">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                {{ __('This gift card has already been :status.', ['status' => $giftCard->status]) }}
            </div>
        @elseif(! empty($isOwnCard))
            {{--
                Sender opening their own preview. Self-redeem is server-
                blocked anyway; we surface a clear note + a back link
                instead of the Redeem CTA so they don't dead-end.
            --}}
            <div class="gc-public__notice gc-public__notice--rich">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <div>
                    <div class="gc-public__notice-title">{{ __('This is your own gift card') }}</div>
                    <div class="gc-public__notice-sub">{{ __('You sent this card to :recipient. Only they can redeem it.', ['recipient' => $giftCard->recipient_name]) }}</div>
                </div>
            </div>
            <a href="{{ route('user.gift-card.index') }}" class="gc-cta-secondary">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                {{ __('Back to My Gift Cards') }}
            </a>
        @else
            @auth
                <a href="{{ route('user.gift-card.index', ['redeem' => 'open', 'code' => $giftCard->code]) }}" class="gc-cta-redeem">
                    <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                    {{ __('Redeem :amount to my wallet', ['amount' => $amountLabel]) }}
                </a>
            @else
                <a href="{{ route('user.login') }}?redirect={{ urlencode(request()->fullUrl()) }}" class="gc-cta-redeem">
                    <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                    {{ __('Redeem :amount to my wallet', ['amount' => $amountLabel]) }}
                </a>
                <a href="{{ route('user.register') }}" class="gc-cta-secondary">
                    {{ __('New to DigiKash?') }} <strong>{{ __('Sign up to redeem →') }}</strong>
                </a>
            @endauth
        @endif

        <div class="gc-public__footer">
            <span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ __('Expires') }} {{ optional($giftCard->expires_at)->format('M d, Y') ?? __('Never') }}</span>
            <span class="gc-public__footer-sep">·</span>
            <span><i class="fa-solid fa-shield" aria-hidden="true"></i> {{ __('Secured by :app escrow', ['app' => config('app.name')]) }}</span>
        </div>
    </div>

    <script>
        /*
         * Vanilla JS copy handler — no jQuery dependency so the public
         * preview stays lightweight and works even if the recipient
         * isn't logged in (where the user-layout JS bundle would
         * normally provide jQuery).
         */
        (function () {
            'use strict';

            const button = document.querySelector('.js-gc-copy');
            if (! button) return;

            const label   = button.querySelector('.js-gc-copy-label');
            const code    = button.getAttribute('data-copy') || '';
            const copyText   = button.getAttribute('data-copy-label')   || 'Copy';
            const copiedText = button.getAttribute('data-copied-label') || 'Copied';

            const flash = function () {
                if (! label) return;
                label.textContent = copiedText;
                button.classList.add('is-copied');
                setTimeout(function () {
                    label.textContent = copyText;
                    button.classList.remove('is-copied');
                }, 1500);
            };

            button.addEventListener('click', function () {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(code).then(flash).catch(function () {
                        /* fall through to textarea fallback */
                        legacyCopy(code, flash);
                    });
                    return;
                }
                legacyCopy(code, flash);
            });

            /*
             * Pre-clipboard-API fallback for older browsers — drops a
             * hidden textarea into the DOM, selects it, fires
             * execCommand('copy'), then removes the textarea.
             */
            function legacyCopy(value, done) {
                const ta = document.createElement('textarea');
                ta.value = value;
                ta.setAttribute('readonly', '');
                ta.style.position = 'absolute';
                ta.style.left = '-9999px';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); } catch (e) {}
                document.body.removeChild(ta);
                if (typeof done === 'function') done();
            }
        })();
    </script>
</body>
</html>
