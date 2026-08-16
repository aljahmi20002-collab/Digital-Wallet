<script>
"use strict";
document.addEventListener('DOMContentLoaded', function () {
    const pageState = @json($paymentAccountPageState);
    const methods = Array.isArray(pageState.methods) ? pageState.methods : [];
    const accounts = Array.isArray(pageState.accounts) ? pageState.accounts : [];
    const createState = pageState.create || {};
    const updateState = pageState.update || {};
    const lang = pageState.lang || {};

    const createModalEl = document.getElementById('p2pPaymentAccountCreateModal');
    const editModalEl = document.getElementById('p2pPaymentAccountEditModal');
    const createMethodSelect = document.getElementById('p2pCreatePaymentMethod');
    const editMethodSelect = document.getElementById('p2pEditPaymentMethod');
    const createFieldsWrap = document.getElementById('p2pCreateDynamicFields');
    const editFieldsWrap = document.getElementById('p2pEditDynamicFields');
    const createInfo = document.getElementById('p2pCreateMethodInfo');
    const editInfo = document.getElementById('p2pEditMethodInfo');
    const createLabel = document.getElementById('p2pCreateAccountLabel');
    const editLabel = document.getElementById('p2pEditAccountLabel');
    const editAccountId = document.getElementById('p2pEditAccountId');
    const editForm = document.getElementById('p2pPaymentAccountEditForm');
    const deleteModalEl = document.getElementById('p2pPaymentAccountDeleteModal');
    const deleteConfirmButton = document.getElementById('p2pPaymentAccountDeleteConfirm');
    const deleteAccountLabel = document.getElementById('p2pDeleteAccountLabel');

    const createModal = createModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(createModalEl) : null;
    const editModal = editModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(editModalEl) : null;
    const deleteModal = deleteModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(deleteModalEl) : null;
    let pendingDeleteForm = null;

    const getMethodById = function (id) {
        return methods.find(function (method) {
            return Number(method.id) === Number(id);
        }) || null;
    };

    const getAccountById = function (id) {
        return accounts.find(function (account) {
            return Number(account.id) === Number(id);
        }) || null;
    };

    const escapeHtml = function (value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const renderMethodInfo = function (target, method) {
        if (!target) {
            return;
        }

        if (!method) {
            target.innerHTML = '';
            target.classList.add('d-none');
            return;
        }

        const instructions = String(method.instructions || '').trim();
        const info = instructions !== ''
            ? '<strong>' + escapeHtml(lang.methodInstructions) + ':</strong><br>' + escapeHtml(instructions).replace(/\n/g, '<br>')
            : escapeHtml(lang.noMethodInstructions);

        target.innerHTML = info;
        target.classList.remove('d-none');
    };

    const updateLabelPlaceholder = function (input, method) {
        if (!input) {
            return;
        }

        if (method && method.name) {
            input.placeholder = String(lang.accountLabelExample || '').replace(':method', method.name);
            return;
        }

        input.placeholder = lang.accountLabelGeneric || '';
    };

    const fieldInputMarkup = function (field, value) {
        const key = String(field && field.key ? field.key : '');
        const label = String(field && field.label ? field.label : key);
        const type = String(field && field.type ? field.type : 'text');
        const required = Boolean(field && field.required);
        const currentValue = value !== null && value !== undefined ? String(value) : '';
        const requiredAttr = required ? ' required' : '';

        if (type === 'select') {
            const options = Array.isArray(field && field.options) ? field.options : [];
            const optionsMarkup = ['<option value="">' + escapeHtml(lang.selectOption) + '</option>'];

            options.forEach(function (option) {
                const selected = currentValue === String(option) ? ' selected' : '';
                optionsMarkup.push('<option value="' + escapeHtml(option) + '"' + selected + '>' + escapeHtml(option) + '</option>');
            });

            return '' +
                '<div class="col-md-6">' +
                '<label class="form-label">' + escapeHtml(label) + (required ? ' *' : '') + '</label>' +
                '<select class="form-select" name="field_values[' + escapeHtml(key) + ']"' + requiredAttr + '>' + optionsMarkup.join('') + '</select>' +
                '</div>';
        }

        if (type === 'textarea') {
            return '' +
                '<div class="col-md-6">' +
                '<label class="form-label">' + escapeHtml(label) + (required ? ' *' : '') + '</label>' +
                '<textarea class="form-control" name="field_values[' + escapeHtml(key) + ']" rows="3"' + requiredAttr + '>' + escapeHtml(currentValue) + '</textarea>' +
                '</div>';
        }

        if (type === 'file') {
            return '' +
                '<div class="col-md-6">' +
                '<label class="form-label">' + escapeHtml(label) + (required ? ' *' : '') + '</label>' +
                '<input type="file" class="form-control" name="field_values[' + escapeHtml(key) + ']"' + requiredAttr + '>' +
                (currentValue ? '<div class="small text-muted mt-1">' + escapeHtml(currentValue) + '</div>' : '') +
                '</div>';
        }

        const inputType = type === 'number' ? 'number' : 'text';

        return '' +
            '<div class="col-md-6">' +
            '<label class="form-label">' + escapeHtml(label) + (required ? ' *' : '') + '</label>' +
            '<input type="' + inputType + '" class="form-control" name="field_values[' + escapeHtml(key) + ']" value="' + escapeHtml(currentValue) + '"' + requiredAttr + '>' +
            '</div>';
    };

    const renderFields = function (wrap, methodId, values) {
        if (!wrap) {
            return;
        }

        const method = getMethodById(methodId);
        wrap.innerHTML = '';

        if (!method || !Array.isArray(method.fields) || method.fields.length === 0) {
            wrap.innerHTML = '<div class="col-12"><div class="text-muted small">' + escapeHtml(lang.noDynamicFields) + '</div></div>';
            return;
        }

        method.fields.forEach(function (field) {
            const fieldValue = values && Object.prototype.hasOwnProperty.call(values, field.key) ? values[field.key] : '';
            wrap.insertAdjacentHTML('beforeend', fieldInputMarkup(field, fieldValue));
        });
    };

    const applyCreateState = function (payload) {
        const methodId = payload && payload.payment_method_id ? Number(payload.payment_method_id) : null;
        const method = getMethodById(methodId);

        if (createMethodSelect) {
            createMethodSelect.value = methodId ? String(methodId) : '';
        }

        if (createLabel) {
            createLabel.value = payload && payload.label ? String(payload.label) : '';
        }

        updateLabelPlaceholder(createLabel, method);
        renderMethodInfo(createInfo, method);
        renderFields(createFieldsWrap, methodId, payload && payload.field_values ? payload.field_values : {});
    };

    const applyEditState = function (account) {
        if (!account || !editForm) {
            return;
        }

        const methodId = Number(account.payment_method_id || 0);
        const method = getMethodById(methodId);
        editForm.action = String(updateState.urlTemplate || '').replace('__ACCOUNT__', String(account.id));

        if (editAccountId) {
            editAccountId.value = String(account.id || '');
        }

        if (editMethodSelect) {
            editMethodSelect.value = methodId ? String(methodId) : '';
        }

        if (editLabel) {
            editLabel.value = account.label || account.account_label || account.display_name || '';
        }

        updateLabelPlaceholder(editLabel, method);
        renderMethodInfo(editInfo, method);
        renderFields(editFieldsWrap, methodId, account.field_values || {});
    };

    const oldValueOr = function (currentValue, oldValue) {
        return oldValue !== null && oldValue !== undefined && oldValue !== '' ? oldValue : currentValue;
    };

    if (createMethodSelect) {
        createMethodSelect.addEventListener('change', function () {
            const methodId = createMethodSelect.value ? Number(createMethodSelect.value) : null;
            const method = getMethodById(methodId);

            updateLabelPlaceholder(createLabel, method);
            renderMethodInfo(createInfo, method);
            renderFields(createFieldsWrap, methodId, {});
        });
    }

    if (editMethodSelect) {
        editMethodSelect.addEventListener('change', function () {
            const methodId = editMethodSelect.value ? Number(editMethodSelect.value) : null;
            const method = getMethodById(methodId);

            updateLabelPlaceholder(editLabel, method);
            renderMethodInfo(editInfo, method);
            renderFields(editFieldsWrap, methodId, {});
        });
    }

    document.querySelectorAll('.js-edit-payment-account').forEach(function (button) {
        button.addEventListener('click', function () {
            const account = getAccountById(button.getAttribute('data-account-id'));
            applyEditState(account);

            if (editModal) {
                editModal.show();
            }
        });
    });

    document.querySelectorAll('.js-delete-payment-account-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            pendingDeleteForm = form;

            if (deleteAccountLabel) {
                deleteAccountLabel.textContent = form.getAttribute('data-account-label') || '-';
            }

            if (deleteModal) {
                deleteModal.show();
                return;
            }

            form.submit();
        });
    });

    if (deleteConfirmButton) {
        deleteConfirmButton.addEventListener('click', function () {
            if (! pendingDeleteForm) {
                return;
            }

            pendingDeleteForm.submit();
        });
    }

    if (deleteModalEl) {
        deleteModalEl.addEventListener('hidden.bs.modal', function () {
            pendingDeleteForm = null;

            if (deleteAccountLabel) {
                deleteAccountLabel.textContent = '-';
            }
        });
    }

    applyCreateState(createState.initialPayload || {});

    if (createState.shouldOpen && createModal) {
        createModal.show();
    }

    if (updateState.shouldOpen && editModal) {
        const account = getAccountById(updateState.accountId);
        const payload = updateState.payload || {};

        if (account) {
            account.payment_method_id = oldValueOr(account.payment_method_id, payload.payment_method_id);
            account.label = oldValueOr(account.label || account.account_label || account.display_name || '', payload.label);
            account.field_values = payload.field_values || account.field_values || {};
            applyEditState(account);
            editModal.show();
        }
    }
});
</script>
