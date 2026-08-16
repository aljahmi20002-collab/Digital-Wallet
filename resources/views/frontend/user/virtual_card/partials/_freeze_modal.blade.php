<div class="modal fade vc-modal" id="vcFreezeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="vc-modal__icon vc-modal__icon--violet"><i class="fa-regular fa-snowflake"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="vc-modal__eyebrow" data-vc-freeze-mode-label>{{ __('Freeze card') }}</div>
                    <div class="vc-modal__title">
                        <span data-vc-freeze-title>{{ __('Freeze card') }}</span> <span class="mono" data-vc-modal-card-last>•••• ••••</span>
                    </div>
                    <div class="vc-modal__subtitle" data-vc-freeze-subtitle>
                        {{ __('All authorizations will decline immediately. Recurring subscriptions may fail until unfrozen.') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                {{-- Reason picker — visible only when freezing (not on unfreeze) --}}
                <div data-vc-freeze-reason-block>
                    <div class="vc-field__label" style="margin-bottom:8px;">
                        <span>{{ __('Reason') }}</span>
                    </div>
                    <div class="vc-options" data-vc-freeze-reasons>
                        <button type="button" class="vc-option is-active" data-vc-reason="lost">
                            <span class="vc-option__icon"><i class="fa-solid fa-magnifying-glass"></i></span>
                            <div class="vc-option__body">
                                <div class="vc-option__title-row">
                                    <span class="vc-option__title">{{ __('Card details misplaced') }}</span>
                                </div>
                                <div class="vc-option__sub">{{ __('Temporarily disable while you locate or rotate credentials.') }}</div>
                            </div>
                            <span class="vc-option__radio"></span>
                        </button>
                        <button type="button" class="vc-option" data-vc-reason="suspicious">
                            <span class="vc-option__icon"><i class="fa-solid fa-bolt"></i></span>
                            <div class="vc-option__body">
                                <div class="vc-option__title-row">
                                    <span class="vc-option__title">{{ __('Suspicious activity') }}</span>
                                </div>
                                <div class="vc-option__sub">{{ __('Block authorizations and open a dispute review.') }}</div>
                            </div>
                            <span class="vc-option__radio"></span>
                        </button>
                        <button type="button" class="vc-option" data-vc-reason="pause">
                            <span class="vc-option__icon"><i class="fa-regular fa-clock"></i></span>
                            <div class="vc-option__body">
                                <div class="vc-option__title-row">
                                    <span class="vc-option__title">{{ __('Pause spend') }}</span>
                                </div>
                                <div class="vc-option__sub">{{ __('Temporary halt — keep the card alive for later use.') }}</div>
                            </div>
                            <span class="vc-option__radio"></span>
                        </button>
                    </div>

                    <div class="vc-field" style="margin-top:16px;">
                        <div class="vc-field__label"><span>{{ __('Duration') }}</span></div>
                        <div class="vc-chips" data-vc-freeze-durations>
                            <button type="button" class="vc-chips__item" data-vc-duration="1h">{{ __('1 hour') }}</button>
                            <button type="button" class="vc-chips__item" data-vc-duration="24h">{{ __('24 hours') }}</button>
                            <button type="button" class="vc-chips__item" data-vc-duration="7d">{{ __('7 days') }}</button>
                            <button type="button" class="vc-chips__item is-active" data-vc-duration="indefinite">{{ __('Indefinite') }}</button>
                        </div>
                        <div style="font-size:11px;color:var(--vc-muted);margin-top:6px;">
                            {{ __('Time-bounded freezes are advisory until a scheduled job runs.') }}
                        </div>
                    </div>
                </div>

                <div class="vc-summary" style="margin-top:16px;background:var(--vc-amber-50);border-color:rgba(245,158,11,.2);" data-vc-freeze-soft-notice>
                    <div style="display:flex;gap:10px;">
                        <i class="fa-solid fa-circle-info" style="color:var(--vc-amber);font-size:18px;"></i>
                        <div style="font-size:12.5px;color:var(--vc-ink-2);line-height:1.5;">
                            <strong>{{ __('Provider note:') }}</strong>
                            <span data-vc-freeze-soft-text>{{ __('This provider does not expose a freeze API. The card will be marked frozen in DigiKash; gateway-level transactions may still succeed.') }}</span>
                        </div>
                    </div>
                </div>

                <div data-vc-modal-error class="d-none" style="margin-top:12px;padding:10px 12px;border-radius:10px;background:var(--vc-red-50);color:#B42318;font-size:13px;"></div>
            </div>

            <div class="modal-footer">
                <button type="button" class="vc-btn vc-btn--ghost" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="button" class="vc-btn vc-btn--violet" data-vc-freeze-confirm>
                    <i class="fa-regular fa-snowflake"></i>
                    <span data-vc-freeze-confirm-label>{{ __('Freeze card') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
