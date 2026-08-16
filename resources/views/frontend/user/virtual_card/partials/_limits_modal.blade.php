<div class="modal fade vc-modal" id="vcLimitsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <form data-vc-limits-form>
                @csrf

                <div class="modal-header">
                    <div class="vc-modal__icon vc-modal__icon--amber"><i class="fa-regular fa-clock"></i></div>
                    <div style="flex:1;min-width:0;">
                        <div class="vc-modal__eyebrow">{{ __('Spend controls') }}</div>
                        <div class="vc-modal__title">
                            {{ __('Set limits for') }} <span class="mono" data-vc-modal-card-last>•••• ••••</span>
                        </div>
                        <div class="vc-modal__subtitle">
                            {{ __('Limits are saved for this card. Providers that support gateway-level enforcement will pick them up automatically.') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="vc-field">
                        <div class="vc-field__label"><span>{{ __('Spend limit type') }}</span></div>
                        <div class="vc-chips" data-vc-limit-type>
                            <button type="button" class="vc-chips__item" data-vc-limit-type-key="per_transaction">{{ __('Per transaction') }}</button>
                            <button type="button" class="vc-chips__item" data-vc-limit-type-key="daily">{{ __('Daily') }}</button>
                            <button type="button" class="vc-chips__item is-active" data-vc-limit-type-key="monthly">{{ __('Monthly') }}</button>
                            <button type="button" class="vc-chips__item" data-vc-limit-type-key="total">{{ __('Total') }}</button>
                        </div>
                        <input type="hidden" name="limit_type" value="monthly">
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="vc-field" style="margin-bottom:0;">
                            <div class="vc-field__label"><span>{{ __('Per transaction') }}</span></div>
                            <div class="vc-input-group">
                                <span class="vc-input-group__prefix" data-vc-modal-symbol>$</span>
                                <input type="number" step="0.01" min="0" class="vc-input" name="per_transaction" placeholder="0.00">
                                <span class="vc-input-group__suffix" data-vc-modal-currency>USD</span>
                            </div>
                        </div>
                        <div class="vc-field" style="margin-bottom:0;">
                            <div class="vc-field__label"><span>{{ __('Daily') }}</span></div>
                            <div class="vc-input-group">
                                <span class="vc-input-group__prefix" data-vc-modal-symbol>$</span>
                                <input type="number" step="0.01" min="0" class="vc-input" name="daily" placeholder="0.00">
                                <span class="vc-input-group__suffix" data-vc-modal-currency>USD</span>
                            </div>
                        </div>
                    </div>

                    <div class="vc-field" style="margin-top:14px;">
                        <div class="vc-field__label"><span>{{ __('Monthly') }}</span></div>
                        <div class="vc-input-group">
                            <span class="vc-input-group__prefix" data-vc-modal-symbol>$</span>
                            <input type="number" step="0.01" min="0" class="vc-input" name="monthly" placeholder="0.00">
                            <span class="vc-input-group__suffix" data-vc-modal-currency>USD</span>
                        </div>
                    </div>

                    <div class="vc-field" style="margin-top:14px;">
                        <div class="vc-field__label">
                            <span>{{ __('Alert when spend reaches') }}</span>
                            <span class="vc-field__hint mono" data-vc-alert-display>80%</span>
                        </div>
                        <input type="range" class="form-range" min="50" max="100" step="5" value="80" name="alert_at" data-vc-alert-input style="accent-color: var(--vc-brand);">
                    </div>

                    <div style="margin-top:14px;display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:12px;border:1px solid var(--vc-line-2);background:#FBFCFE;">
                        <div style="width:34px;height:34px;border-radius:9px;background:var(--vc-red-50);color:var(--vc-red);display:grid;place-items:center;flex-shrink:0;">
                            <i class="fa-regular fa-snowflake"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:13.5px;font-weight:600;">{{ __('Auto-freeze at 100%') }}</div>
                            <div style="font-size:11.5px;color:var(--vc-muted);margin-top:2px;">
                                {{ __('Block transactions once monthly limit is hit.') }}
                            </div>
                        </div>
                        <button type="button" class="vc-toggle is-on" data-vc-toggle="auto_freeze"></button>
                        <input type="hidden" name="auto_freeze" value="1">
                    </div>

                    <div data-vc-modal-error class="d-none" style="margin-top:12px;padding:10px 12px;border-radius:10px;background:var(--vc-red-50);color:#B42318;font-size:13px;"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="vc-btn vc-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="vc-btn vc-btn--secondary" data-vc-save-template>{{ __('Save as template') }}</button>
                    <button type="submit" class="vc-btn vc-btn--primary">
                        <i class="fa-regular fa-clock"></i> {{ __('Apply limits') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
