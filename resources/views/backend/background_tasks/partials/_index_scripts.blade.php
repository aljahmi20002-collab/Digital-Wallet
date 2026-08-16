<script>
"use strict";

(function () {
    var modal      = document.getElementById('run-task-modal');
    var keyInput   = document.getElementById('modal-task-key');
    var labelEl    = document.getElementById('modal-task-label');
    var descEl     = document.getElementById('modal-task-desc');
    var sigEl      = document.getElementById('modal-task-sig');
    var limitField = document.getElementById('field-limit');
    var limitInput = document.getElementById('modal-limit-input');
    var renewalsField = document.getElementById('field-renewals');
    var renewalsInput = document.getElementById('modal-renewals-input');

    if (!modal) { return; }

    modal.addEventListener('show.coreui.modal', function (event) {
        var btn      = event.relatedTarget;
        var key      = btn.getAttribute('data-task-key');
        var label    = btn.getAttribute('data-task-label');
        var desc     = btn.getAttribute('data-task-desc');
        var sig      = btn.getAttribute('data-task-sig');
        var hasLimit = btn.getAttribute('data-has-limit') === '1';
        var hasRenewals = btn.getAttribute('data-has-renewals') === '1';
        var renewalsDefault = btn.getAttribute('data-renewals-default') === '1';

        keyInput.value   = key;
        labelEl.textContent = label;
        descEl.textContent  = desc;
        sigEl.textContent   = sig;

        if (hasLimit) {
            limitField.classList.remove('d-none');
            limitInput.value = 100;
            limitInput.required = true;
        } else {
            limitField.classList.add('d-none');
            limitInput.removeAttribute('required');
        }

        if (hasRenewals) {
            renewalsField.classList.remove('d-none');
            renewalsInput.checked = renewalsDefault;
        } else {
            renewalsField.classList.add('d-none');
            renewalsInput.checked = false;
        }
    });

    modal.addEventListener('hide.coreui.modal', function () {
        keyInput.value = '';
        limitField.classList.add('d-none');
        limitInput.removeAttribute('required');
        renewalsField.classList.add('d-none');
        renewalsInput.checked = false;
    });
}());
</script>
