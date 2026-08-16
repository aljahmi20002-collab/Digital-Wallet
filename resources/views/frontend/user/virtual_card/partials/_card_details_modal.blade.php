<div class="modal fade vc-modal" id="cardDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="vc-modal__icon"><i class="fa-regular fa-eye"></i></div>
                <div style="flex:1;min-width:0;">
                    <div class="vc-modal__eyebrow">{{ __('Card details') }}</div>
                    <div class="vc-modal__title">{{ __('Sensitive card information') }}</div>
                    <div class="vc-modal__subtitle">
                        {{ __('Visible only to the cardholder. Do not share these details.') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="card-details-content" style="min-height:240px;">
                <div class="d-flex justify-content-center align-items-center" style="height:240px;">
                    <span class="spinner-border" style="color:var(--vc-brand);"></span>
                </div>
            </div>
        </div>
    </div>
</div>
