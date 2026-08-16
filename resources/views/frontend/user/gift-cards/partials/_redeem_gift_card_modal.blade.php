{{--
    Gift Card · Redeem modal
    ---------------------------------------------------------------
    Triggered from the "Redeem Code" button on the Gift Cards index
    page. Posts to the same `user.gift-card.redeem` endpoint the
    standalone page used to use, so the controller stays untouched.

    Auto-opens itself when the page URL carries ?redeem=open (e.g. a
    user lands here from the public recipient page) and pre-fills the
    code field from ?code= if present.

    Icons are inline SVGs — FontAwesome occasionally fails to render
    inside a fresh modal before the icon font finishes loading, so we
    avoid the dependency entirely.
--}}
<div class="modal fade gift-card-redeem-modal"
     id="redeemGiftCardModal"
     tabindex="-1"
     aria-labelledby="redeemGiftCardModalLabel"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content shadow-lg">
            <div class="modal-header gift-card-redeem-modal__header">
                <h5 class="modal-title gift-card-redeem-modal__title" id="redeemGiftCardModalLabel">
                    <span class="gift-card-redeem-modal__title-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 12 20 22 4 22 4 12"/>
                            <rect x="2" y="7" width="20" height="5"/>
                            <line x1="12" y1="22" x2="12" y2="7"/>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                        </svg>
                    </span>
                    <span>{{ __('Redeem Gift Card') }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>

            <form id="redeemGiftCardForm"
                  method="POST"
                  action="{{ route('user.gift-card.redeem') }}">
                @csrf

                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        {{ __('Paste your gift code and we will add the value to your wallet.') }}
                    </p>

                    {{-- Code field --}}
                    <div class="mb-3">
                        <label for="redeemGiftCardCode" class="form-label fw-semibold">
                            {{ __('Gift code') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-text gift-card-redeem-modal__code-prefix" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 8a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4V8z"/>
                                    <line x1="13" y1="6" x2="13" y2="18" stroke-dasharray="2 3"/>
                                </svg>
                            </span>
                            <input type="text"
                                   id="redeemGiftCardCode"
                                   name="code"
                                   class="form-control gift-card-redeem-modal__code js-gc-redeem-code"
                                   placeholder="DKGC-XXXX-XXXX"
                                   autocomplete="off"
                                   required>
                        </div>
                        <div class="form-text mt-1 small d-flex align-items-center gap-1">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                            </svg>
                            <span>{{ __('Your code is verified securely. Never share it with anyone you do not trust.') }}</span>
                        </div>
                    </div>

                    {{-- Wallet picker --}}
                    <div class="mb-1">
                        <label class="form-label fw-semibold">{{ __('Deposit into wallet') }}</label>

                        @forelse($redeemWallets ?? collect() as $wallet)
                            @php
                                $currencyFlag = $wallet->currency?->flag;
                                $currencyCode = $wallet->currency?->code ?? '';
                            @endphp
                            <label class="gift-card-redeem-modal__wallet" for="redeemWallet-{{ $wallet->id }}">
                                <input type="radio"
                                       id="redeemWallet-{{ $wallet->id }}"
                                       name="wallet_id"
                                       value="{{ $wallet->id }}"
                                       class="form-check-input"
                                       {{ $loop->first ? 'checked' : '' }}>
                                <span class="gift-card-redeem-modal__wallet-icon" aria-hidden="true">
                                    @if($currencyFlag)
                                        <img src="{{ asset($currencyFlag) }}"
                                             alt="{{ $currencyCode }}"
                                             loading="lazy">
                                    @else
                                        <span class="gift-card-redeem-modal__wallet-icon-fallback">
                                            {{ strtoupper(substr($currencyCode, 0, 2)) }}
                                        </span>
                                    @endif
                                </span>
                                <span class="gift-card-redeem-modal__wallet-body">
                                    <span class="gift-card-redeem-modal__wallet-name">{{ $wallet->name ?? $currencyCode }}</span>
                                    <span class="gift-card-redeem-modal__wallet-balance">
                                        {{ __('Balance') }}
                                        <strong>{{ $wallet->currency?->symbol ?? '$' }}{{ number_format((float) $wallet->balance, 2) }}</strong>
                                    </span>
                                </span>
                            </label>
                        @empty
                            <div class="alert alert-warning small mb-0 d-flex align-items-center gap-2">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10"/>
                                    <line x1="12" y1="8" x2="12" y2="12"/>
                                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                                <span>{{ __('You do not have an active wallet yet — create one first.') }}</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="modal-footer gift-card-redeem-modal__footer">
                    <button type="submit"
                            class="btn btn-primary btn-sm gift-card-redeem-modal__submit"
                            @if(($redeemWallets ?? collect())->isEmpty()) disabled @endif>
                        {{-- Gift icon = thematically right for "redeem a gift". --}}
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="20 12 20 22 4 22 4 12"/>
                            <rect x="2" y="7" width="20" height="5"/>
                            <line x1="12" y1="22" x2="12" y2="7"/>
                            <path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/>
                            <path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/>
                        </svg>
                        <span>{{ __('Redeem Gift Card') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
    <style>
        /* ╔══════════════════════════════════════════════════════════════╗
           ║  Gift Card · Redeem modal                                    ║
           ╚══════════════════════════════════════════════════════════════╝
           overflow:hidden on .modal-content is what gives the header its
           rounded top corners — without it the light header background
           paints over the modal's border-radius and only the bottom
           corners look rounded. */
        .gift-card-redeem-modal .modal-content {
            border: 0;
            border-radius: 18px;
            overflow: hidden;
        }

        .gift-card-redeem-modal__header {
            background: linear-gradient(180deg, #F8FAFC 0%, #F1F5F9 100%);
            border: 0;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .gift-card-redeem-modal__title {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin: 0;
            color: #1D4ED8;
            font-weight: 800;
            font-size: 1.02rem;
            letter-spacing: -0.005em;
            line-height: 1.2;
        }

        .gift-card-redeem-modal__title-icon {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: rgba(29, 78, 216, 0.12);
            color: #1D4ED8;
        }

        .gift-card-redeem-modal__title-icon svg {
            width: 17px;
            height: 17px;
        }

        /* Bootstrap's .btn-close is the white X — works fine on the
           soft gradient header, just nudge it up to align with the
           title and give it a hover ring for clarity. */
        .gift-card-redeem-modal .btn-close {
            opacity: 0.55;
            transition: opacity 0.15s ease;
        }
        .gift-card-redeem-modal .btn-close:hover,
        .gift-card-redeem-modal .btn-close:focus {
            opacity: 1;
        }

        /* ─── Code input ────────────────────────────────────────────── */
        .gift-card-redeem-modal__code {
            font-family: ui-monospace, "SF Mono", Menlo, monospace;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .gift-card-redeem-modal__code-prefix {
            background: linear-gradient(135deg, #3B82F6, #1D4ED8);
            color: #fff;
            border: 0;
            padding: 0 14px;
            min-width: 46px;
            display: inline-grid;
            place-items: center;
        }

        .gift-card-redeem-modal__code-prefix svg {
            width: 18px;
            height: 18px;
        }

        .gift-card-redeem-modal .form-text svg {
            flex-shrink: 0;
            color: #94A3B8;
        }

        /* ─── Wallet picker (standard balanced rows) ─────────────────── */
        .gift-card-redeem-modal__wallet {
            display: grid;
            grid-template-columns: auto auto 1fr;
            align-items: center;
            gap: 11px;
            padding: 9px 13px;
            margin-bottom: 6px;
            border: 1.5px solid #E6EAF3;
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .gift-card-redeem-modal__wallet:hover {
            border-color: #BFDBFE;
        }

        .gift-card-redeem-modal__wallet:has(input:checked) {
            border-color: #1D4ED8;
            background: #EFF6FF;
        }

        .gift-card-redeem-modal__wallet input[type="radio"] {
            margin: 0;
        }

        /* Currency flag chip — image fills the box, fallback shows the
           ISO code in a soft chip when the flag asset is missing. */
        .gift-card-redeem-modal__wallet-icon {
            display: inline-grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            overflow: hidden;
            background: #F1F5F9;
            color: #1D4ED8;
            flex-shrink: 0;
        }

        .gift-card-redeem-modal__wallet-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gift-card-redeem-modal__wallet-icon-fallback {
            font-size: 0.62rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .gift-card-redeem-modal__wallet-body {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
            line-height: 1.25;
        }

        .gift-card-redeem-modal__wallet-name {
            font-size: 0.85rem;
            font-weight: 700;
            color: #0F172A;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .gift-card-redeem-modal__wallet-balance {
            font-size: 0.7rem;
            color: #64748B;
        }

        .gift-card-redeem-modal__wallet-balance strong {
            color: #0F172A;
            font-weight: 700;
        }

        /* ─── Footer ─────────────────────────────────────────────────── */
        .gift-card-redeem-modal__footer {
            border: 0;
            padding: 12px 20px 18px;
            justify-content: flex-end;
        }

        .gift-card-redeem-modal__submit {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .gift-card-redeem-modal__submit svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }
    </style>
@endpush

@push('scripts')
    <script>
        /*
         * Redeem-modal bootstrap — vanilla JS + jQuery (the project
         * doesn't ship Alpine.js, so the previous x-data binding never
         * ran). Responsibilities:
         *   • auto-format the gift code as the user types
         *   • pre-fill the code field if the URL has ?code=
         *   • auto-open the modal when the URL has ?redeem=open OR ?code=
         */
        (function () {
            'use strict';

            function formatCode(raw) {
                var s = (raw || '').toUpperCase().replace(/[^A-Z0-9-]/g, '').replace(/-/g, '');
                if (s.indexOf('DKGC') === 0) {
                    var rest = s.slice(4).slice(0, 8);
                    return ['DKGC', rest.slice(0, 4), rest.slice(4, 8)].filter(Boolean).join('-');
                }
                return s.replace(/(.{4})/g, '$1-').replace(/-$/, '').slice(0, 14);
            }

            document.addEventListener('DOMContentLoaded', function () {
                var input = document.querySelector('.js-gc-redeem-code');
                if (! input) return;

                input.addEventListener('input', function (e) {
                    var caret = e.target.selectionEnd;
                    e.target.value = formatCode(e.target.value);
                    // Keep the caret roughly where the user left it.
                    try { e.target.setSelectionRange(caret, caret); } catch (err) { /* noop */ }
                });

                var params = new URLSearchParams(window.location.search);
                var incoming = params.get('code');
                if (incoming) {
                    input.value = formatCode(incoming);
                }

                if (params.get('redeem') === 'open' || incoming) {
                    var el = document.getElementById('redeemGiftCardModal');
                    if (el && window.bootstrap && window.bootstrap.Modal) {
                        window.bootstrap.Modal.getOrCreateInstance(el).show();
                    }
                }
            });
        })();
    </script>
@endpush
