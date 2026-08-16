<div class="modal fade vc-modal" id="vcTopupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('user.virtual-card.topup-store') }}" method="post" data-vc-topup-form>
                @csrf
                <input type="hidden" name="card_id" data-vc-modal-card-id>

                <div class="modal-header">
                    <div class="vc-modal__icon"><i class="fa-solid fa-arrow-down"></i></div>
                    <div style="flex:1;min-width:0;">
                        <div class="vc-modal__eyebrow">{{ __('Top up') }}</div>
                        <div class="vc-modal__title">
                            {{ __('Add funds to') }} <span class="mono" data-vc-modal-card-last>•••• ••••</span>
                        </div>
                        <div class="vc-modal__subtitle">
                            {{ __('Funds settle from the linked wallet. Provider fees are previewed below.') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="vc-field">
                        <div class="vc-field__label">
                            <span>{{ __('Amount') }}</span>
                            <span class="vc-field__hint">{{ __('Wallet:') }} <span class="mono" data-vc-wallet-balance-text>—</span></span>
                        </div>
                        <div class="vc-input-group">
                            <span class="vc-input-group__prefix" data-vc-modal-symbol>$</span>
                            <input type="text"
                                   class="vc-input"
                                   name="amount"
                                   oninput="this.value = validateDouble(this.value)"
                                   placeholder="0.00"
                                   required>
                            <span class="vc-input-group__suffix" data-vc-modal-currency>USD</span>
                        </div>
                        <div class="vc-presets" data-vc-presets>
                            @foreach([100, 500, 1000, 2500, 5000] as $p)
                                <button type="button" class="vc-presets__btn" data-amount="{{ $p }}">+{{ number_format($p) }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="vc-field" style="margin-top:18px;">
                        <div class="vc-field__label"><span>{{ __('Funding source') }}</span></div>
                        <div class="vc-options">
                            <button type="button" class="vc-option is-active" data-vc-source="wallet">
                                <span class="vc-option__icon"><i class="fa-solid fa-bolt"></i></span>
                                <div class="vc-option__body">
                                    <div class="vc-option__title-row">
                                        <span class="vc-option__title">{{ __('Digikash Wallet') }}</span>
                                        <span class="vc-option__badge">{{ __('Instant') }}</span>
                                    </div>
                                    <div class="vc-option__sub" data-vc-wallet-source-sub>{{ __('Available balance · no fee') }}</div>
                                </div>
                                <span class="vc-option__radio"></span>
                            </button>
                            <button type="button" class="vc-option" disabled>
                                <span class="vc-option__icon"><i class="fa-solid fa-building-columns"></i></span>
                                <div class="vc-option__body">
                                    <div class="vc-option__title-row">
                                        <span class="vc-option__title">{{ __('Bank · ACH') }}</span>
                                        <span class="vc-option__badge vc-option__badge--muted">{{ __('Coming soon') }}</span>
                                    </div>
                                    <div class="vc-option__sub">{{ __('1–2 business days · no fee') }}</div>
                                </div>
                                <span class="vc-option__radio"></span>
                            </button>
                            <button type="button" class="vc-option" disabled>
                                <span class="vc-option__icon"><i class="fa-regular fa-credit-card"></i></span>
                                <div class="vc-option__body">
                                    <div class="vc-option__title-row">
                                        <span class="vc-option__title">{{ __('Debit card') }}</span>
                                        <span class="vc-option__badge vc-option__badge--muted">{{ __('Coming soon') }}</span>
                                    </div>
                                    <div class="vc-option__sub">{{ __('Instant · 1.4% fee') }}</div>
                                </div>
                                <span class="vc-option__radio"></span>
                            </button>
                        </div>
                    </div>

                    <div class="vc-summary">
                        <div class="vc-summary__row">
                            <span class="label">{{ __('Top-up amount') }}</span>
                            <span class="value" data-vc-modal-amount>—</span>
                        </div>
                        <div class="vc-summary__row">
                            <span class="label">{{ __('Provider fee') }}</span>
                            <span class="value" data-vc-modal-fee>—</span>
                        </div>
                        <div class="vc-summary__divider"></div>
                        <div class="vc-summary__total">
                            <span class="label">{{ __('Total debited') }}</span>
                            <span class="value" data-vc-modal-total>—</span>
                        </div>
                    </div>

                    <div data-vc-modal-error class="d-none" style="margin-top:12px;padding:10px 12px;border-radius:10px;background:var(--vc-red-50);color:#B42318;font-size:13px;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="vc-btn vc-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="vc-btn vc-btn--primary">
                        <i class="fa-solid fa-arrow-down"></i> {{ __('Top up now') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
