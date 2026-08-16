<div class="modal fade settings-plugin-modal" id="manageModal" aria-hidden="true" aria-labelledby="pluginManageModalLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header settings-plugin-modal__header">
                <div class="settings-plugin-modal__title">
                    <span class="settings-plugin-modal__icon">
                        <x-icon name="plugin" height="20" width="20" class="settings-plugin-modal__icon-glyph"/>
                    </span>
                    <div>
                        <span class="settings-plugin-modal__eyebrow">{{ __('Plugin Configuration') }}</span>
                        <h5 class="modal-title" id="pluginManageModalLabel">{{ __('Update Integration') }}</h5>
                    </div>
                </div>
                <button type="button" class="settings-plugin-modal__close" data-coreui-dismiss="modal" aria-label="{{ __('Close') }}">
                    <x-icon name="close" height="18" width="18" class="settings-plugin-modal__close-glyph"/>
                </button>
            </div>
            <div class="modal-body settings-plugin-modal__body">
                <div class="row" id="edit-append">

                </div>
            </div>
        </div>
    </div>
</div>
