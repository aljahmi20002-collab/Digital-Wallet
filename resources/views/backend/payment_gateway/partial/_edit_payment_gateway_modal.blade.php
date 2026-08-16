<div class="modal fade pgm-modal" id="edit-payment-gateway-modal" tabindex="-1" role="dialog" aria-labelledby="edit-payment-gateway-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content pgm-modal__content">
            <div class="modal-header pgm-modal__header">
                <div class="pgm-modal__title">
                    <span class="pgm-modal__icon">
                        <x-icon name="payment" height="20" width="20"/>
                    </span>
                    <div>
                        <h2 class="modal-title" id="edit-payment-gateway-title">{{ __('Gateway Credentials') }}</h2>
                        <p>{{ __('Update secure keys, webhook settings, and availability.') }}</p>
                    </div>
                </div>
                <button type="button" class="pgm-modal__close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <div class="modal-body pgm-modal__body">
                <div id="edit-payment-gateway-append"></div>
            </div>
        </div>
    </div>
</div>
