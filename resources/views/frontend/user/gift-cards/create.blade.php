@extends('frontend.layouts.user.index')
@section('title', __('Create Gift Card'))
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/gift-card.css') }}?v={{ filemtime(public_path('assets/frontend/css/gift-card.css')) }}">
@endpush
@section('content')
    @php
        /*
         * Build serialisable payloads for the JS layer. Templates and wallets
         * are also rendered server-side below — these arrays exist so the
         * client-side preview/summary logic can resolve a selected template's
         * preset_key or a wallet's currency symbol without hitting the DOM
         * for every read.
         */
        $templatesPayload = $templates->map(fn ($t) => [
            'id'             => $t->id,
            'name'           => $t->name,
            'category'       => $t->category,
            'preset_key'     => $t->preset_key ?: 'premium',
            'default_amount' => $t->default_amount !== null ? (float) $t->default_amount : null,
        ])->values();

        /*
         * Per-wallet fee + limit payload. Each wallet carries the currency
         * role row already (eager-loaded in the controller) — we pull the
         * gift_card role first, falling back to the voucher role for
         * wallets that haven't been configured with a dedicated gift_card
         * role yet (matches the controller's own resolveRole() logic).
         *
         * The frontend uses these to render an *accurate* order summary
         * instead of the previous hardcoded 1.5% placeholder. Backend
         * still recalculates at submit (single source of truth) — the
         * payload is for live preview only.
         */
        $walletsPayload = $wallets->map(function ($w) {
            $role = $w->currency?->roles?->firstWhere('role_name', \App\Constants\CurrencyRole::GIFT_CARD)
                ?? $w->currency?->roles?->firstWhere('role_name', \App\Constants\CurrencyRole::VOUCHER);

            return [
                'id'        => $w->id,
                'name'      => $w->name,
                'balance'   => (float) $w->balance,
                'currency'  => $w->currency->code ?? 'USD',
                'symbol'    => $w->currency->symbol ?? '$',
                'fee'       => (float) ($role->fee ?? 0),
                'fee_type'  => $role->fee_type ?? 'percent',
                'min_limit' => $role->min_limit !== null ? (float) $role->min_limit : null,
                'max_limit' => $role->max_limit !== null ? (float) $role->max_limit : null,
                'role_name' => $role->role_name ?? null,
            ];
        })->values();

        $defaultSender = auth()->user()?->name ?? '';
    @endphp

    <div class="gc-create" data-default-sender="{{ $defaultSender }}">

        {{--
            Page header — uses the project's shared <x-user-feature-header>
            component so the look matches voucher / payment-links / send-money
            and every other user-facing feature page in the app.
        --}}
        <div class="single-form-card">
            <x-user-feature-header
                :title="__('Create a Gift Card')"
                :subtitle="__('Pick a design, set the amount, personalize, and send.')"
                icon="fa-solid fa-gift"
            >
                <a class="btn btn-light-success btn-sm" href="{{ route('user.gift-card.index') }}">
                    <i class="fa-solid fa-arrow-left"></i> {{ __('Back to Gift Cards') }}
                </a>
            </x-user-feature-header>
        </div>

        {{-- Stepper --}}
        <div class="gc-create__stepper-card">
            <div class="gc-stepper">
                @foreach([__('Design'), __('Amount'), __('Recipient'), __('Review')] as $idx => $label)
                    @php $n = $idx + 1; @endphp
                    <button type="button"
                            class="step js-gc-step-jump {{ $n === 1 ? 'current' : '' }}"
                            data-step="{{ $n }}">
                        <div class="n">
                            <span class="n-num">{{ $n }}</span>
                            <i class="fa-solid fa-check n-check"></i>
                        </div>
                        <div class="lbl"><b>{{ __('Step :n', ['n' => $n]) }}</b>{{ $label }}</div>
                    </button>
                    @if($n < 4)
                        <div class="bar" data-bar="{{ $n }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        @if($wallets->isEmpty())
            <div class="single-form-card mt-3">
                <div class="card-main">
                    <x-user-not-found
                        :title="__('No eligible wallet')"
                        :message="__('You need at least one wallet whose currency has the gift card or voucher role enabled. Ask the admin to enable it, or top up your wallet first.')"
                        :eyebrow="__('Action required')"
                        icon="fa-wallet"
                        :action-url="route('user.wallet.index')"
                        :action-label="__('Go to Wallets')"
                        action-icon="fa-arrow-right"
                    />
                </div>
            </div>
        @else

        <form action="{{ route('user.gift-card.store') }}"
              method="POST"
              id="gc-create-form"
              class="gc-create__form">
            @csrf
            {{-- Hidden inputs the form actually submits — kept in sync by JS. --}}
            <input type="hidden" name="gift_card_template_id" id="gc-input-template" value="{{ $templates->first()?->id }}">
            <input type="hidden" name="wallet_id"             id="gc-input-wallet"   value="{{ $wallets->first()?->id }}">
            <input type="hidden" name="amount"                id="gc-input-amount"   value="{{ $templates->first()?->default_amount ?: 50 }}">
            <input type="hidden" name="recipient_mode"        id="gc-input-mode"     value="email">
            <input type="hidden" name="recipient_name"        id="gc-input-rname"    value="">
            <input type="hidden" name="recipient_email"       id="gc-input-remail"   value="">
            <input type="hidden" name="sender_name"           id="gc-input-sender"   value="{{ $defaultSender }}">
            <input type="hidden" name="message"               id="gc-input-message"  value="">
            <input type="hidden" name="schedule"              id="gc-input-schedule" value="0">
            <input type="hidden" name="scheduled_at"          id="gc-input-schedat"  value="">
            <input type="hidden" name="terms"                 id="gc-input-terms"    value="1">

            <div class="row g-3 mt-1 align-items-start">
                <div class="col-lg-8">
                    <div class="single-form-card">
                        <div class="card-main">

                            {{-- ─── STEP 1: Templates ────────────────────────── --}}
                            <div class="gc-step-panel is-active" data-panel="1">
                                <div class="gc-step-panel__head">
                                    <div>
                                        <h2 class="gc-step-panel__title">{{ __('Choose a design') }}</h2>
                                        <p class="gc-step-panel__sub">{{ __('The look the recipient sees when they open the card.') }}</p>
                                    </div>
                                    <div class="gc-step-panel__meta">
                                        <span class="js-gc-template-count">{{ $templates->count() }}</span> {{ __('designs') }}
                                    </div>
                                </div>

                                <div class="gc-chip-row js-gc-cat-row mb-3">
                                    @foreach($categories as $cat)
                                        <button type="button"
                                                class="gc-chip js-gc-cat-chip {{ $cat === 'All' ? 'is-active' : '' }}"
                                                data-cat="{{ $cat }}">{{ $cat }}</button>
                                    @endforeach
                                </div>

                                <div class="gc-template-grid">
                                    @foreach($templates as $i => $t)
                                        <button type="button"
                                                class="gc-template-card js-gc-template {{ $i === 0 ? 'is-selected' : '' }}"
                                                data-id="{{ $t->id }}"
                                                data-preset="{{ $t->preset_key ?: 'premium' }}"
                                                data-cat="{{ $t->category }}"
                                                data-name="{{ $t->name }}"
                                                data-default-amount="{{ $t->default_amount !== null ? (float) $t->default_amount : '' }}">
                                            <div class="inner">
                                                <div class="gc-template-card__art">
                                                    <x-gift-card-design :preset="$t->preset_key" :amount="$t->default_amount ?: 50" :recipient="__('Recipient')" sender="{{ $defaultSender ?: 'You' }}" :width="216"/>
                                                </div>
                                                <div class="gc-template-card__meta">
                                                    <div>
                                                        <div class="name">{{ $t->name }}</div>
                                                        <div class="cat">{{ $t->category }}</div>
                                                    </div>
                                                    <div class="tick">
                                                        <i class="fa-solid fa-check"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- ─── STEP 2: Amount & wallet ──────────────────── --}}
                            <div class="gc-step-panel" data-panel="2">
                                <div class="gc-step-panel__head">
                                    <div>
                                        <h2 class="gc-step-panel__title">{{ __('Amount & wallet') }}</h2>
                                        <p class="gc-step-panel__sub">{{ __('Funds are reserved from your wallet when you create the gift card.') }}</p>
                                    </div>
                                </div>

                                <label class="gc-field-label mt-2">{{ __('Pay from wallet') }}</label>
                                <div class="gc-wallet-grid">
                                    @foreach($wallets as $i => $w)
                                        <button type="button"
                                                class="gc-wallet-card js-gc-wallet {{ $i === 0 ? 'is-selected' : '' }}"
                                                data-id="{{ $w->id }}"
                                                data-name="{{ $w->name }}"
                                                data-balance="{{ (float) $w->balance }}"
                                                data-currency="{{ $w->currency->code ?? 'USD' }}"
                                                data-symbol="{{ $w->currency->symbol ?? '$' }}">
                                            <div class="gc-wallet-card__head">
                                                <span class="gc-wallet-card__name">{{ $w->name }}</span>
                                                <span class="gc-wallet-card__tick"><i class="fa-solid fa-check"></i></span>
                                            </div>
                                            <div class="gc-wallet-card__lbl">{{ __('Available balance') }}</div>
                                            <div class="gc-wallet-card__bal gc-money">
                                                {{ $w->currency->symbol ?? '$' }}{{ number_format((float) $w->balance, 2) }}
                                            </div>
                                        </button>
                                    @endforeach
                                </div>

                                <label class="gc-field-label mt-4">{{ __('Gift card amount') }}</label>
                                <div class="gc-amount-input">
                                    <span class="gc-amount-input__symbol js-gc-amount-symbol">$</span>
                                    <input type="text"
                                           inputmode="decimal"
                                           class="form-control gc-money js-gc-amount"
                                           value="{{ $templates->first()?->default_amount ?: 50 }}"
                                           placeholder="0.00">
                                    <span class="gc-amount-input__currency js-gc-amount-currency">USD</span>
                                </div>
                                <div class="gc-field-hint">{{ __('Funds reserved from') }} <span class="js-gc-wallet-name">—</span></div>
                                <div class="gc-field-hint js-gc-amount-limits"></div>

                                <label class="gc-field-label mt-3">{{ __('Quick amounts') }}</label>
                                <div class="gc-chip-row">
                                    @foreach([25, 50, 100, 200] as $v)
                                        <button type="button" class="gc-chip js-gc-quick {{ $v == 100 ? 'is-active' : '' }}" data-amount="{{ $v }}">
                                            <span class="js-gc-quick-symbol">$</span>{{ $v }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- ─── STEP 3: Recipient ────────────────────────── --}}
                            <div class="gc-step-panel" data-panel="3">
                                <div class="gc-step-panel__head">
                                    <div>
                                        <h2 class="gc-step-panel__title">{{ __('Who is it for?') }}</h2>
                                        <p class="gc-step-panel__sub">{{ __('We will deliver the card by email, with a private redeem link.') }}</p>
                                    </div>
                                </div>

                                <div class="gc-mode-toggle">
                                    <button type="button" class="js-gc-mode is-active" data-mode="user">
                                        <i class="fa-solid fa-user"></i> {{ __('Existing Digikash user') }}
                                    </button>
                                    <button type="button" class="js-gc-mode" data-mode="email">
                                        <i class="fa-solid fa-envelope"></i> {{ __('Send via email') }}
                                    </button>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="gc-field-label">{{ __('Recipient name') }}</label>
                                        <input type="text" class="form-control js-gc-rname" placeholder="{{ __('Recipient name') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="gc-field-label">{{ __('Recipient email') }}</label>
                                        <input type="email" class="form-control js-gc-remail" placeholder="recipient@example.com">
                                    </div>
                                </div>

                                <label class="gc-field-label mt-3">
                                    <span>{{ __('Personal message') }}</span>
                                    <span class="gc-field-label__opt"><span class="js-gc-msg-count">0</span>/200</span>
                                </label>
                                <textarea class="form-control js-gc-message" rows="3" maxlength="200" placeholder="{{ __('Happy birthday! Treat yourself to something nice.') }}"></textarea>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <label class="gc-field-label">{{ __('Sender name (shown on card)') }}</label>
                                        <input type="text" class="form-control js-gc-sender" value="{{ $defaultSender }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="gc-field-label">{{ __('Delivery') }} <span class="gc-field-label__opt">{{ __('optional') }}</span></label>
                                        <div class="gc-chip-row">
                                            <button type="button" class="gc-chip js-gc-schedule is-active" data-schedule="0">
                                                <i class="fa-solid fa-paper-plane"></i> {{ __('Send now') }}
                                            </button>
                                            <button type="button" class="gc-chip js-gc-schedule" data-schedule="1">
                                                <i class="fa-solid fa-calendar"></i> {{ __('Schedule') }}
                                            </button>
                                        </div>
                                        <input type="datetime-local" class="form-control mt-2 js-gc-schedat" style="display:none;">
                                    </div>
                                </div>
                            </div>

                            {{-- ─── STEP 4: Review ───────────────────────────── --}}
                            <div class="gc-step-panel" data-panel="4">
                                <div class="gc-step-panel__head">
                                    <div>
                                        <h2 class="gc-step-panel__title">{{ __('Review & send') }}</h2>
                                        <p class="gc-step-panel__sub">{{ __('Confirm everything below — once sent, the recipient gets an email immediately.') }}</p>
                                    </div>
                                </div>

                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <div class="gc-review-card">
                                            <div class="ic"><i class="fa-solid fa-paintbrush"></i></div>
                                            <div>
                                                <div class="lbl">{{ __('Design') }}</div>
                                                <div class="val js-review-design">—</div>
                                                <div class="meta js-review-design-cat">—</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="gc-review-card">
                                            <div class="ic"><i class="fa-solid fa-wallet"></i></div>
                                            <div>
                                                <div class="lbl">{{ __('Amount') }}</div>
                                                <div class="val js-review-amount">—</div>
                                                <div class="meta">{{ __('From') }} <span class="js-review-wallet">—</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="gc-review-card">
                                            <div class="ic"><i class="fa-solid fa-user"></i></div>
                                            <div>
                                                <div class="lbl">{{ __('Recipient') }}</div>
                                                <div class="val js-review-rname">—</div>
                                                <div class="meta js-review-remail">—</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="gc-review-card">
                                            <div class="ic"><i class="fa-solid fa-paper-plane"></i></div>
                                            <div>
                                                <div class="lbl">{{ __('Delivery') }}</div>
                                                <div class="val js-review-delivery">{{ __('Send immediately') }}</div>
                                                <div class="meta">{{ __('By email') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="gc-review-message">
                                    <div class="gc-field-label">{{ __('Personal message') }}</div>
                                    <div class="gc-review-message__body js-review-message">{{ __('No message added.') }}</div>
                                </div>

                                <div class="gc-terms-card">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <div>
                                        <div class="gc-terms-card__title">{{ __('Cards expire after 12 months') }}</div>
                                        <div class="gc-terms-card__sub">{{ __('Unredeemed funds return to your wallet.') }}</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Footer navigation --}}
                            <div class="gc-step-nav">
                                <button type="button" class="btn btn-light js-gc-prev" disabled>
                                    <i class="fa-solid fa-arrow-left"></i> {{ __('Back') }}
                                </button>
                                <div class="gc-step-nav__indicator">
                                    {{ __('Step') }} <span class="js-gc-step-current">1</span> / 4
                                </div>
                                <button type="button" class="btn btn-base js-gc-next">
                                    {{ __('Continue') }} <i class="fa-solid fa-arrow-right"></i>
                                </button>
                                <button type="submit" class="btn btn-base js-gc-submit" style="display:none;">
                                    <i class="fa-solid fa-gift"></i> {{ __('Send Gift Card') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ─── Sidebar: Live preview + Order summary ──────────── --}}
                <div class="col-lg-4">
                    <div class="gc-sticky-side">
                        <div class="single-form-card mb-3">
                            <div class="card-main">
                                <div class="gc-side-eyebrow">{{ __('Live preview') }}</div>
                                <div class="gc-live-preview js-gc-preview"></div>
                                <div class="gc-msg-bubble js-gc-msg-bubble" style="display:none;"></div>
                            </div>
                        </div>

                        <div class="single-form-card">
                            <div class="card-main">
                                <div class="gc-side-title">{{ __('Order summary') }}</div>
                                <ul class="summery-list list-unstyled">
                                    <li class="d-flex justify-content-between">
                                        <span>{{ __('Gift card value') }}</span>
                                        <span class="gc-money js-sum-value">—</span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span class="js-sum-fee-label">{{ __('Service fee') }}</span>
                                        <span class="gc-money js-sum-fee">—</span>
                                    </li>
                                    <li class="d-flex justify-content-between">
                                        <span>{{ __('Delivery') }}</span>
                                        <span>{{ __('Email') }}</span>
                                    </li>
                                    <li class="d-flex justify-content-between summary-total">
                                        <strong>{{ __('Payable from') }} <span class="js-sum-wallet">—</span></strong>
                                        <strong class="gc-money js-sum-total">—</strong>
                                    </li>
                                </ul>
                                <div class="gc-secure-hint"><i class="fa-solid fa-lock"></i> {{ __('Secure transaction via DigiKash') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        'use strict';

        /**
         * Gift Card create wizard — vanilla JS + jQuery.
         *
         * The user frontend layout doesn't ship Alpine.js, so this module
         * drives the entire 4-step flow with imperative DOM updates and
         * data-attribute lookups. It:
         *
         *   • Renders the same rich gift-card preview the recipient sees
         *     (matches the x-gift-card-design Blade component).
         *   • Keeps a tiny `state` object in sync with hidden form inputs
         *     so a normal POST submit sends everything the backend expects.
         *   • Drives the 4-step panels via .is-active classes.
         *   • Computes the order summary client-side (the backend
         *     recalculates the real fee at submit time anyway).
         */
        (function ($) {
            const root = document.querySelector('.gc-create');
            if (! root) return;

            // Bail out if the user has no eligible wallets — the form
            // won't even be on the page in that case.
            const form = document.getElementById('gc-create-form');
            if (! form) return;

            // ─── Constants mirroring the gift-card-design preset definitions ──
            const PRESET_DEFS = {
                birthday:    { bg: 'linear-gradient(135deg, #FB7185 0%, #F472B6 45%, #C026D3 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.22)', motif: 'confetti', ribbon: 'Happy Birthday' },
                holiday:     { bg: 'linear-gradient(135deg, #064E3B 0%, #065F46 40%, #0F766E 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.18)', motif: 'snow',     ribbon: "Season's Greetings" },
                thankyou:    { bg: 'linear-gradient(135deg, #FDE68A 0%, #FBBF24 55%, #D97706 100%)', ink: '#3F2A05', chip: 'rgba(63,42,5,.10)',    motif: 'rays',     ribbon: 'With Gratitude' },
                anniversary: { bg: 'linear-gradient(135deg, #4C1D95 0%, #7E22CE 45%, #BE185D 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.20)', motif: 'hearts',   ribbon: 'Happy Anniversary' },
                congrats:    { bg: 'linear-gradient(135deg, #1E3A8A 0%, #3B82F6 45%, #06B6D4 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.20)', motif: 'sparkles', ribbon: 'Congratulations' },
                premium:     { bg: 'linear-gradient(135deg, #0B1330 0%, #14245F 45%, #1E3A8A 100%)', ink: '#FFFFFF', chip: 'rgba(255,255,255,.14)', motif: 'mesh',     ribbon: 'A Gift For You' },
            };
            const TEMPLATES = @json($templatesPayload);
            const WALLETS   = @json($walletsPayload);
            const RECIPIENT_PLACEHOLDER = @json(__('Recipient'));
            const SEND_IMMEDIATE = @json(__('Send immediately'));
            const NO_MESSAGE = @json(__('No message added.'));

            // ─── State ────────────────────────────────────────────────
            const state = {
                step: 1,
                templateId: TEMPLATES[0]?.id || null,
                walletId: WALLETS[0]?.id || null,
                // Seed amount from the first template's admin-set default
                // amount. When the admin hasn't set one we fall back to $50
                // so the live preview + summary visually match the server-
                // rendered thumbnail (which also falls back to $50 via the
                // `$t->default_amount ?: 50` expression in the Blade).
                amount: String(TEMPLATES[0]?.default_amount || 50),
                mode: 'user',
                rname: '',
                remail: '',
                sender: root.dataset.defaultSender || '',
                message: '',
                schedule: 0,
                scheduleAt: '',
                category: 'All',
            };

            const $form = $(form);

            // ─── Helpers ──────────────────────────────────────────────
            const activeTemplate = () => TEMPLATES.find(t => t.id == state.templateId) || TEMPLATES[0];
            const activeWallet   = () => WALLETS.find(w => w.id == state.walletId) || WALLETS[0];
            const fmt = (n, s) => (s || '$') + Number(n || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            const escapeHtml = (str) => String(str).replace(/[&<>"']/g, (c) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[c]));

            // ─── Motif SVG (mirrors x-gift-card-design Blade component) ──
            function renderMotif(motif, uid) {
                const wrap = 'position:absolute;inset:0;width:100%;height:100%;pointer-events:none';
                if (motif === 'confetti') {
                    const cols = ['#FDE68A','#FCA5A5','#A7F3D0','#BFDBFE','#FBCFE8'];
                    let r = '';
                    for (let i = 0; i < 28; i++) {
                        const x = (i * 47) % 400, y = (i * 31) % 252;
                        const s = (i % 3) * 4 + 6, rot = (i * 23) % 360;
                        r += `<rect x="${x}" y="${y}" width="${s}" height="${s*0.5}" fill="${cols[i%5]}" opacity=".55" transform="rotate(${rot} ${x} ${y})" rx="1"/>`;
                    }
                    return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${r}</svg>`;
                }
                if (motif === 'snow') {
                    let r = '';
                    for (let i = 0; i < 40; i++) {
                        const x = (i * 41) % 400, y = (i * 19) % 252;
                        r += `<circle cx="${x}" cy="${y}" r="${(i % 3) + 1.2}" fill="#fff" opacity="${(0.25 + (i % 3) * 0.15).toFixed(2)}"/>`;
                    }
                    return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${r}</svg>`;
                }
                if (motif === 'rays') {
                    let lines = '';
                    for (let i = 0; i < 8; i++) lines += `<line x1="80" y1="0" x2="${i*60}" y2="252" stroke="#fff" stroke-opacity="0.06" stroke-width="1"/>`;
                    return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">
                        <defs><radialGradient id="gcp-ray-${uid}" cx="20%" cy="0%" r="80%">
                            <stop offset="0%" stop-color="#fff" stop-opacity=".55"/>
                            <stop offset="100%" stop-color="#fff" stop-opacity="0"/>
                        </radialGradient></defs>
                        <rect width="400" height="252" fill="url(#gcp-ray-${uid})"/>${lines}</svg>`;
                }
                if (motif === 'hearts') {
                    let r = '';
                    for (let i = 0; i < 14; i++) {
                        const x = (i * 53) % 400, y = (i * 37) % 252, s = 8 + (i % 3) * 4;
                        r += `<circle cx="${x}" cy="${y + s * 0.3}" r="${(s * 0.4).toFixed(2)}" fill="#fff" opacity="0.10"/>`;
                    }
                    return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${r}</svg>`;
                }
                if (motif === 'sparkles') {
                    let r = '';
                    for (let i = 0; i < 18; i++) {
                        const x = (i * 41) % 400, y = (i * 23) % 252, s = 4 + (i % 3) * 3;
                        r += `<g opacity="0.4" transform="translate(${x} ${y})">
                            <path d="M0 -${s} L${(s*0.3).toFixed(2)} -${(s*0.3).toFixed(2)} L${s} 0 L${(s*0.3).toFixed(2)} ${(s*0.3).toFixed(2)} L0 ${s} L-${(s*0.3).toFixed(2)} ${(s*0.3).toFixed(2)} L-${s} 0 L-${(s*0.3).toFixed(2)} -${(s*0.3).toFixed(2)} Z" fill="#FDE68A"/>
                        </g>`;
                    }
                    return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">${r}</svg>`;
                }
                if (motif === 'mesh') {
                    return `<svg style="${wrap}" viewBox="0 0 400 252" preserveAspectRatio="none">
                        <defs>
                            <radialGradient id="gcp-m1-${uid}" cx="85%" cy="20%" r="50%"><stop offset="0%" stop-color="#60A5FA" stop-opacity=".55"/><stop offset="100%" stop-color="#60A5FA" stop-opacity="0"/></radialGradient>
                            <radialGradient id="gcp-m2-${uid}" cx="10%" cy="90%" r="55%"><stop offset="0%" stop-color="#FBBF24" stop-opacity=".30"/><stop offset="100%" stop-color="#FBBF24" stop-opacity="0"/></radialGradient>
                            <pattern id="gcp-dots-${uid}" width="14" height="14" patternUnits="userSpaceOnUse"><circle cx="1" cy="1" r="1" fill="#fff" opacity="0.08"/></pattern>
                        </defs>
                        <rect width="400" height="252" fill="url(#gcp-m1-${uid})"/>
                        <rect width="400" height="252" fill="url(#gcp-m2-${uid})"/>
                        <rect width="400" height="252" fill="url(#gcp-dots-${uid})"/>
                    </svg>`;
                }
                return '';
            }

            function renderCard(opts) {
                const def = PRESET_DEFS[opts.preset] || PRESET_DEFS.premium;
                const w = opts.width || 320;
                const h = Math.round(w / 1.586);
                const symbol = opts.symbol || '$';
                const ink = def.ink;
                const isLightInk = ink === '#FFFFFF';
                const chipBorder = isLightInk ? 'rgba(255,255,255,.30)' : 'rgba(63,42,5,.18)';
                const uid = (opts.preset || 'premium') + '-' + (Math.random()*1e6|0);
                const motif = renderMotif(def.motif, uid);
                const ribbon = def.ribbon;
                const recipient = opts.recipient || RECIPIENT_PLACEHOLDER;
                const sender = opts.sender || 'You';
                const amount = symbol + Number(opts.amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

                return `<div style="width:${w}px; height:${h}px; position:relative; background:${def.bg}; color:${ink}; border-radius:${w>=320?18:12}px; overflow:hidden; box-shadow:inset 0 0 0 1px rgba(255,255,255,.10); font-family:'Plus Jakarta Sans', system-ui, sans-serif;">
                    ${motif}
                    <div style="position:absolute; top:${(w*0.045).toFixed(1)}px; left:${(w*0.045).toFixed(1)}px; display:flex; align-items:center; gap:${(w*0.018).toFixed(1)}px; font-weight:800; letter-spacing:-.02em; font-size:${(w*0.044).toFixed(1)}px; line-height:1;">
                        <div style="width:${(w*0.062).toFixed(1)}px; height:${(w*0.062).toFixed(1)}px; border-radius:${(w*0.014).toFixed(1)}px; background:rgba(255,255,255,.22); border:1px solid rgba(255,255,255,.35); display:grid; place-items:center;">
                            <svg viewBox="0 0 24 24" width="${(w*0.04).toFixed(1)}" height="${(w*0.04).toFixed(1)}" fill="none"><path d="M4 7h12a4 4 0 0 1 0 8H4V7z" fill="${ink}"/></svg>
                        </div>
                        DigiKash
                    </div>
                    <div style="position:absolute; top:${(w*0.05).toFixed(1)}px; right:${(w*0.05).toFixed(1)}px; background:${def.chip}; border:1px solid ${chipBorder}; padding:${(w*0.014).toFixed(1)}px ${(w*0.028).toFixed(1)}px; border-radius:999px; font-size:${(w*0.028).toFixed(1)}px; font-weight:700; letter-spacing:.08em; text-transform:uppercase;">${escapeHtml(ribbon)}</div>
                    <div style="position:absolute; left:${(w*0.05).toFixed(1)}px; right:${(w*0.05).toFixed(1)}px; top:38%;">
                        <div style="font-size:${(w*0.028).toFixed(1)}px; letter-spacing:.16em; text-transform:uppercase; font-weight:700; opacity:.78; margin-bottom:${(w*0.01).toFixed(1)}px;">{{ __('Gift Card Value') }}</div>
                        <div style="font-size:${(w*0.16).toFixed(1)}px; font-weight:800; letter-spacing:-.03em; line-height:.95; ${isLightInk ? 'text-shadow:0 2px 12px rgba(0,0,0,.18);' : ''} font-variant-numeric:tabular-nums;">${amount}</div>
                    </div>
                    <div style="position:absolute; left:${(w*0.05).toFixed(1)}px; right:${(w*0.05).toFixed(1)}px; bottom:${(w*0.05).toFixed(1)}px; display:flex; justify-content:space-between; align-items:flex-end; gap:${(w*0.04).toFixed(1)}px;">
                        <div style="min-width:0; flex:1;">
                            <div style="font-size:${(w*0.028).toFixed(1)}px; letter-spacing:.12em; text-transform:uppercase; font-weight:700; opacity:.72;">{{ __('TO') }}</div>
                            <div style="font-size:${(w*0.045).toFixed(1)}px; font-weight:700; margin-top:${(w*0.005).toFixed(1)}px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(recipient)}</div>
                        </div>
                        <div style="min-width:0; flex:1; text-align:right;">
                            <div style="font-size:${(w*0.028).toFixed(1)}px; letter-spacing:.12em; text-transform:uppercase; font-weight:700; opacity:.72;">{{ __('FROM') }}</div>
                            <div style="font-size:${(w*0.045).toFixed(1)}px; font-weight:700; margin-top:${(w*0.005).toFixed(1)}px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">${escapeHtml(sender)}</div>
                        </div>
                    </div>
                </div>`;
            }

            // ─── Sync ─────────────────────────────────────────────────
            function syncHiddenInputs() {
                $('#gc-input-template').val(state.templateId);
                $('#gc-input-wallet').val(state.walletId);
                $('#gc-input-amount').val(state.amount);
                $('#gc-input-mode').val(state.mode);
                $('#gc-input-rname').val(state.rname);
                $('#gc-input-remail').val(state.remail);
                $('#gc-input-sender').val(state.sender);
                $('#gc-input-message').val(state.message);
                $('#gc-input-schedule').val(state.schedule);
                $('#gc-input-schedat').val(state.scheduleAt);
            }

            /*
             * The card uses absolute pixel sizes for its internal text /
             * motif / footer — so we cannot CSS-scale it. Instead we ask
             * the actual container how wide it is, clamp the result to a
             * sensible range, and render the card at that exact size.
             * Result: it never overflows the sidebar and always looks
             * crisp regardless of viewport.
             */
            function getPreviewCardWidth() {
                const $container = $('.js-gc-preview');
                const w = $container.innerWidth() || 320;
                // Leave a tiny horizontal margin so the soft drop-shadow
                // isn't clipped, and never go below ~220 (any smaller and
                // the inner typography starts looking cramped).
                return Math.max(220, Math.min(320, Math.floor(w - 16)));
            }

            function renderPreview() {
                const tpl = activeTemplate();
                const wlt = activeWallet();
                $('.js-gc-preview').html(renderCard({
                    preset:    tpl?.preset_key || 'premium',
                    amount:    state.amount,
                    symbol:    wlt?.symbol || '$',
                    recipient: state.rname,
                    sender:    state.sender || 'You',
                    width:     getPreviewCardWidth(),
                }));
                const $msg = $('.js-gc-msg-bubble');
                if (state.message) {
                    $msg.text('“' + state.message + '”').show();
                } else {
                    $msg.hide();
                }
            }

            /*
             * Compute the real fee for the active wallet. Matches the
             * server-side Wallet::calculateFeeByRole() math:
             *   - fee_type 'fixed'   => flat fee (e.g. €5 regardless of amount)
             *   - fee_type 'percent' => amount × (fee/100)
             * Default fee row shape: { fee: 0, fee_type: 'percent' } so a
             * wallet without a configured role pays no fee in the preview
             * (and the backend will validate-reject it anyway).
             */
            function computeFee(wallet, amount) {
                if (! wallet || ! amount) return 0;
                const f = parseFloat(wallet.fee) || 0;
                if (wallet.fee_type === 'fixed') return +f.toFixed(2);
                return +((amount * f) / 100).toFixed(2);
            }

            function renderSummary() {
                const wlt = activeWallet();
                const symbol = wlt?.symbol || '$';
                const amount = parseFloat(state.amount) || 0;
                const fee = computeFee(wlt, amount);
                const total = +(amount + fee).toFixed(2);

                // Show "Service fee" line with a hint about how it's calculated
                // so the user understands the number on the right.
                let feeLabel = '{{ __('Service fee') }}';
                if (wlt) {
                    feeLabel = wlt.fee_type === 'fixed'
                        ? '{{ __('Service fee') }} (' + symbol + Number(wlt.fee).toFixed(2) + ' {{ __('flat') }})'
                        : '{{ __('Service fee') }} (' + Number(wlt.fee) + '%)';
                }

                $('.js-sum-value').text(fmt(amount, symbol));
                $('.js-sum-fee').text(fmt(fee, symbol));
                $('.js-sum-fee-label').text(feeLabel);
                $('.js-sum-total').text(fmt(total, symbol));
                $('.js-sum-wallet').text(wlt?.name || '—');
                $('.js-gc-wallet-name').text(wlt?.name || '—');
                $('.js-gc-amount-symbol').text(symbol);
                $('.js-gc-amount-currency').text(wlt?.currency || '');
                $('.js-gc-quick-symbol').text(symbol);

                // Limit hint under the amount input — surfaces the
                // currency-role min/max so users don't hit a server
                // rejection at submit time. Plain if/else (the prior
                // chained ternary was missing a colon inside its
                // parens, which produced a JS parse error that took
                // the whole IIFE down with it — no preview, no
                // selection, no summary updates).
                const $limitHint = $('.js-gc-amount-limits');
                if (wlt && (wlt.min_limit != null || wlt.max_limit != null)) {
                    const min = wlt.min_limit != null ? symbol + Number(wlt.min_limit).toFixed(2) : null;
                    const max = wlt.max_limit != null ? symbol + Number(wlt.max_limit).toFixed(2) : null;
                    let range;
                    if (min && max) {
                        range = min + ' – ' + max;
                    } else if (min) {
                        range = '{{ __('Min') }} ' + min;
                    } else {
                        range = '{{ __('Max') }} ' + max;
                    }
                    $limitHint.text('{{ __('Allowed range:') }} ' + range);
                } else {
                    $limitHint.text('');
                }
            }

            function renderReview() {
                const tpl = activeTemplate();
                const wlt = activeWallet();
                $('.js-review-design').text(tpl?.name || '—');
                $('.js-review-design-cat').text(tpl?.category || '—');
                $('.js-review-amount').text(fmt(state.amount, wlt?.symbol || '$'));
                $('.js-review-wallet').text(wlt?.name || '—');
                $('.js-review-rname').text(state.rname || '—');
                $('.js-review-remail').text(state.remail || '—');
                $('.js-review-delivery').text(state.schedule == 1 && state.scheduleAt ? state.scheduleAt : SEND_IMMEDIATE);
                $('.js-review-message').text(state.message || NO_MESSAGE);
            }

            function updateStepUI() {
                $('.gc-step-panel').removeClass('is-active');
                $('.gc-step-panel[data-panel="' + state.step + '"]').addClass('is-active');

                $('.js-gc-step-jump').each(function () {
                    const s = parseInt($(this).data('step'), 10);
                    $(this).removeClass('current done');
                    if (s === state.step) $(this).addClass('current');
                    else if (s < state.step) $(this).addClass('done');
                });
                $('.gc-stepper .bar').each(function () {
                    const s = parseInt($(this).data('bar'), 10);
                    $(this).toggleClass('done', s < state.step);
                });

                $('.js-gc-step-current').text(state.step);
                $('.js-gc-prev').prop('disabled', state.step === 1);
                if (state.step === 4) {
                    $('.js-gc-next').hide();
                    $('.js-gc-submit').show();
                    renderReview();
                } else {
                    $('.js-gc-next').show();
                    $('.js-gc-submit').hide();
                }
            }

            function refreshAll() {
                syncHiddenInputs();
                renderPreview();
                renderSummary();
                if (state.step === 4) renderReview();
            }

            // ─── Event wiring ─────────────────────────────────────────
            $('.js-gc-step-jump').on('click', function () {
                const s = parseInt($(this).data('step'), 10);
                // Only allow jumping to already-completed steps or one ahead.
                if (s <= state.step + 1) {
                    state.step = Math.min(4, Math.max(1, s));
                    updateStepUI();
                }
            });

            $('.js-gc-next').on('click', function () {
                if (state.step < 4) { state.step++; updateStepUI(); }
            });
            $('.js-gc-prev').on('click', function () {
                if (state.step > 1) { state.step--; updateStepUI(); }
            });

            // Template selection + category filter.
            //
            // When a design has an admin-configured default amount we
            // prefill the amount field with it. When the admin hasn't
            // set one we fall back to $50 — which is exactly the value
            // baked into the server-rendered thumbnail — so the live
            // preview and the picked thumbnail always show the same
            // number. User-typed overrides win (the _userTouchedAmount
            // latch flips once the user touches the amount input).
            $('.js-gc-template').on('click', function () {
                state.templateId = $(this).data('id');
                $('.js-gc-template').removeClass('is-selected');
                $(this).addClass('is-selected');

                if (! state._userTouchedAmount) {
                    const tplDefaultRaw = parseFloat($(this).data('default-amount'));
                    const nextAmount = (isFinite(tplDefaultRaw) && tplDefaultRaw > 0)
                        ? tplDefaultRaw
                        : 50; // fallback matches the thumbnail's `?: 50`
                    state.amount = String(nextAmount);
                    $('.js-gc-amount').val(state.amount);
                    $('.js-gc-quick').removeClass('is-active');
                    $('.js-gc-quick[data-amount="' + nextAmount + '"]').addClass('is-active');
                }

                refreshAll();
            });
            $('.js-gc-cat-chip').on('click', function () {
                state.category = $(this).data('cat');
                $('.js-gc-cat-chip').removeClass('is-active');
                $(this).addClass('is-active');
                let visible = 0;
                $('.js-gc-template').each(function () {
                    const matches = state.category === 'All' || $(this).data('cat') === state.category;
                    $(this).toggle(matches);
                    if (matches) visible++;
                });
                $('.js-gc-template-count').text(visible);
            });

            // Wallet selection
            $('.js-gc-wallet').on('click', function () {
                state.walletId = $(this).data('id');
                $('.js-gc-wallet').removeClass('is-selected');
                $(this).addClass('is-selected');
                refreshAll();
            });

            // Amount + quick amounts.
            // The `_userTouchedAmount` latch tells the template-selection
            // handler to stop overwriting the amount from the new template's
            // default once the user has personally chosen a value here.
            $('.js-gc-amount').on('input', function () {
                const v = $(this).val().replace(/[^0-9.]/g, '');
                $(this).val(v);
                state.amount = v;
                state._userTouchedAmount = true;
                refreshAll();
                $('.js-gc-quick').removeClass('is-active');
                $('.js-gc-quick[data-amount="' + parseFloat(v) + '"]').addClass('is-active');
            });
            $('.js-gc-quick').on('click', function () {
                const v = String($(this).data('amount'));
                state.amount = v;
                state._userTouchedAmount = true;
                $('.js-gc-amount').val(v);
                $('.js-gc-quick').removeClass('is-active');
                $(this).addClass('is-active');
                refreshAll();
            });

            // Recipient mode toggle
            $('.js-gc-mode').on('click', function () {
                state.mode = $(this).data('mode');
                $('.js-gc-mode').removeClass('is-active');
                $(this).addClass('is-active');
                syncHiddenInputs();
            });

            // Recipient & sender & message
            $('.js-gc-rname').on('input', function () { state.rname = $(this).val(); refreshAll(); });
            $('.js-gc-remail').on('input', function () { state.remail = $(this).val(); syncHiddenInputs(); });
            $('.js-gc-sender').on('input', function () { state.sender = $(this).val(); refreshAll(); });
            $('.js-gc-message').on('input', function () {
                state.message = $(this).val().slice(0, 200);
                $('.js-gc-msg-count').text(state.message.length);
                refreshAll();
            });

            // Schedule toggle
            $('.js-gc-schedule').on('click', function () {
                state.schedule = parseInt($(this).data('schedule'), 10);
                $('.js-gc-schedule').removeClass('is-active');
                $(this).addClass('is-active');
                $('.js-gc-schedat').toggle(state.schedule === 1);
                syncHiddenInputs();
            });
            $('.js-gc-schedat').on('change', function () { state.scheduleAt = $(this).val(); syncHiddenInputs(); });

            /*
             * Form submit guard + loading state.
             *
             *   1. Sync hidden inputs FIRST so even if a guard tries to
             *      bail out, the form data is already in the DOM (some
             *      pre-fill cases let the user click Send right away
             *      without touching the recipient fields — those need
             *      state.rname / state.remail synced before the guard).
             *   2. Run guards. On fail, show a notify toast + jump to
             *      the offending step + abort the submit. The notify
             *      toast is the user's confirmation that their click
             *      registered.
             *   3. On pass, swap the submit button to a "Sending…"
             *      loading state (disabled + spinner) so they can see
             *      the click was accepted, and to prevent double-click
             *      racing into the prevent.duplicate middleware lock.
             *   4. The browser then POSTs the form to the controller.
             *      After redirect, the destination page's
             *      _notify_evs.blade.php partial fires the success
             *      (or validation-error) toast.
             */
            let submitting = false;
            $form.on('submit', function (e) {
                if (submitting) {
                    // Already in flight — ignore subsequent clicks.
                    e.preventDefault();
                    return;
                }

                // Sync first so the guards read the most recent state.
                syncHiddenInputs();

                if (! state.templateId) {
                    e.preventDefault();
                    notifyEvs('error', '{{ __('Please choose a design.') }}');
                    state.step = 1; updateStepUI();
                    return;
                }
                if (! state.walletId || ! state.amount) {
                    e.preventDefault();
                    notifyEvs('error', '{{ __('Please enter the amount and select a wallet.') }}');
                    state.step = 2; updateStepUI();
                    return;
                }
                if (! state.rname || ! state.remail) {
                    e.preventDefault();
                    notifyEvs('error', '{{ __('Please enter the recipient name and email.') }}');
                    state.step = 3; updateStepUI();
                    return;
                }

                // NOTE: we deliberately do NOT show a "Sending…" toast
                // here. The browser navigates away to the destination
                // page within ~100ms, which kills any in-flight toast
                // before it has a chance to animate in. The loading
                // button state + page transition is enough visual
                // feedback; the real success/error toast appears on
                // the destination page where it actually persists.

                // Lock + visual loading state. We capture the original
                // button HTML so it can be restored if the browser ever
                // bfcaches the page on back-navigation.
                submitting = true;
                const $btn = $('.js-gc-submit');
                $btn.data('original-html', $btn.html());
                $btn.prop('disabled', true).html(
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>'
                    + '{{ __('Sending…') }}'
                );
            });

            // Restore the button if the user navigates back (bfcache) so
            // they can retry without seeing a permanently-disabled button.
            $(window).on('pageshow', function (event) {
                if (event.originalEvent && event.originalEvent.persisted) {
                    submitting = false;
                    const $btn = $('.js-gc-submit');
                    const original = $btn.data('original-html');
                    if (original) $btn.html(original);
                    $btn.prop('disabled', false);
                }
            });

            // Window resize → re-render preview so the card adapts to the
            // new sidebar width. Debounced so we don't thrash on drag.
            let resizeTimer = null;
            $(window).on('resize', function () {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(renderPreview, 120);
            });

            // ─── Initial paint ────────────────────────────────────────
            // Use requestAnimationFrame so the browser has laid out the
            // sidebar before we measure its width. Without this the very
            // first render can read width=0 and clamp to the minimum.
            updateStepUI();
            window.requestAnimationFrame(refreshAll);

        })(jQuery);
    </script>
@endpush
