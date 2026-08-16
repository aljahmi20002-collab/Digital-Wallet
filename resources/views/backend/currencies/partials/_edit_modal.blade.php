{{-- Manage Currency modal — currency-specific header is rendered inside edit.blade.php on AJAX load --}}
<div class="modal fade currency-drawer" id="edit_currency_modal" aria-hidden="true" aria-labelledby="manage_currency_drawer_title" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-body" id="edit_currency_append">
                <div class="currency-drawer__loading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">{{ __('Loading...') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
