<div class="modal fade vc-modal" id="vcWithdrawModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('user.virtual-card.withdraw-store') }}" method="post" data-vc-withdraw-form>
                @csrf
                <input type="hidden" name="card_id" data-vc-modal-card-id>

                <div class="modal-header">
                    <div class="vc-modal__icon" style="background:var(--vc-green-50);color:var(--vc-green);">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="vc-modal__eyebrow">{{ __('Withdraw') }}</div>
                        <div class="vc-modal__title">
                            {{ __('Withdraw from') }} <span class="mono" data-vc-modal-card-last>•••• ••••</span>
                        </div>
                        <div class="vc-modal__subtitle">
                            {{ __('Move card balance back to the linked wallet.') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="vc-field">
                        <div class="vc-field__label">
                            <span>{{ __('Amount') }}</span>
                            <span class="vc-field__hint">{{ __('Card balance:') }} <span class="mono" data-vc-card-balance-text>—</span></span>
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
                    </div>

                    <div class="vc-summary">
                        <div class="vc-summary__row">
                            <span class="label">{{ __('Withdraw amount') }}</span>
                            <span class="value" data-vc-modal-amount>—</span>
                        </div>
                        <div class="vc-summary__row">
                            <span class="label">{{ __('Provider fee') }}</span>
                            <span class="value" data-vc-modal-fee>—</span>
                        </div>
                        <div class="vc-summary__divider"></div>
                        <div class="vc-summary__total">
                            <span class="label">{{ __('Wallet receives') }}</span>
                            <span class="value" data-vc-modal-total>—</span>
                        </div>
                    </div>

                    <div data-vc-modal-error class="d-none" style="margin-top:12px;padding:10px 12px;border-radius:10px;background:var(--vc-red-50);color:#B42318;font-size:13px;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="vc-btn vc-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="vc-btn vc-btn--primary" style="background:var(--vc-green);box-shadow:none;">
                        <i class="fa-solid fa-arrow-up"></i> {{ __('Withdraw now') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
