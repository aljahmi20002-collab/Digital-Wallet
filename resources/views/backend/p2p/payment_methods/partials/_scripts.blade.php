<script>
    "use strict";

    $(function () {
        const defaultFields = @json(\App\Models\P2P\PaymentMethod::defaultFieldDefinitions());
        const createOldFields = @json(old('form_type') === 'create_method' ? old('fields', \App\Models\P2P\PaymentMethod::defaultFieldDefinitions()) : \App\Models\P2P\PaymentMethod::defaultFieldDefinitions());
        const updateOldFields = @json(old('form_type') === 'update_method' ? old('fields', \App\Models\P2P\PaymentMethod::defaultFieldDefinitions()) : []);
        const fieldTypeLabels = {
            text: @json(__('Text')),
            number: @json(__('Number')),
            textarea: @json(__('Textarea')),
            select: @json(__('Select')),
            file: @json(__('File')),
        };
        const labels = {
            fieldLabel: @json(__('Field label')),
            fieldKey: @json(__('Field key')),
            fieldType: @json(__('Type')),
            required: @json(__('Required')),
            remove: @json(__('Remove field')),
            options: @json(__('Options (one per line)')),
            addField: @json(__('Add field')),
            dynamicFields: @json(__('Dynamic account fields')),
            dynamicFieldsHint: @json(__('Define what each trader fills in when adding this method.')),
        };
        const schemaUrlTemplate = @json(route('admin.p2p.methods.show', ['method' => '__METHOD__']));
        const $createModal = $('#p2p_add_method_modal');
        const $createForm = $('#p2p_add_method_modal form');
        const $manageModal = $('#p2p_manage_method_modal');
        const $manageForm = $('#p2p_manage_method_form');

        const escapeHtml = function (value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        };

        const normalizeFields = function (fields) {
            if (!Array.isArray(fields) || fields.length === 0) {
                return defaultFields.slice();
            }

            return fields.map(function (field, index) {
                const type = ['text', 'number', 'textarea', 'select', 'file'].indexOf(String(field && field.type ? field.type : 'text')) !== -1
                    ? String(field.type)
                    : 'text';

                return {
                    label: String(field && field.label ? field.label : ''),
                    key: String(field && field.key ? field.key : ''),
                    type: type,
                    required: Boolean(field && (field.required === true || Number(field.required || 0) === 1)),
                    options: Array.isArray(field && field.options)
                        ? field.options.join('\n')
                        : String(field && field.options ? field.options : ''),
                    sort_order: Number(field && field.sort_order ? field.sort_order : (index + 1)),
                };
            });
        };

        const fieldRowHtml = function (prefix, index, field) {
            const normalized = normalizeFields([field])[0];
            const optionsHidden = normalized.type === 'select' ? '' : ' style="display:none;"';

            return '' +
                '<div class="pm-field-row" data-index="' + index + '">' +
                    '<div class="pm-field-row__grid">' +
                        '<div class="pm-field-row__col pm-field-row__col--label">' +
                            '<input type="text" class="form-control" name="' + prefix + '[' + index + '][label]"' +
                                ' value="' + escapeHtml(normalized.label) + '"' +
                                ' placeholder="' + labels.fieldLabel + '"' +
                                ' aria-label="' + labels.fieldLabel + '" required>' +
                        '</div>' +
                        '<div class="pm-field-row__col pm-field-row__col--key">' +
                            '<input type="text" class="form-control" name="' + prefix + '[' + index + '][key]"' +
                                ' value="' + escapeHtml(normalized.key) + '"' +
                                ' placeholder="' + labels.fieldKey + '"' +
                                ' aria-label="' + labels.fieldKey + '">' +
                        '</div>' +
                        '<div class="pm-field-row__col pm-field-row__col--type">' +
                            '<select class="form-select js-p2p-field-type" name="' + prefix + '[' + index + '][type]" aria-label="' + labels.fieldType + '">' +
                                '<option value="text"' + (normalized.type === 'text' ? ' selected' : '') + '>' + fieldTypeLabels.text + '</option>' +
                                '<option value="number"' + (normalized.type === 'number' ? ' selected' : '') + '>' + fieldTypeLabels.number + '</option>' +
                                '<option value="textarea"' + (normalized.type === 'textarea' ? ' selected' : '') + '>' + fieldTypeLabels.textarea + '</option>' +
                                '<option value="select"' + (normalized.type === 'select' ? ' selected' : '') + '>' + fieldTypeLabels.select + '</option>' +
                                '<option value="file"' + (normalized.type === 'file' ? ' selected' : '') + '>' + fieldTypeLabels.file + '</option>' +
                            '</select>' +
                        '</div>' +
                        '<div class="pm-field-row__col pm-field-row__col--required">' +
                            '<label class="pm-field-row__required" aria-label="' + labels.required + '">' +
                                '<input type="hidden" name="' + prefix + '[' + index + '][required]" value="0">' +
                                '<span class="pm-switch">' +
                                    '<input class="form-check-input" type="checkbox" name="' + prefix + '[' + index + '][required]" value="1"' + (normalized.required ? ' checked' : '') + ' aria-label="' + labels.required + '">' +
                                    '<span class="pm-switch__track"></span>' +
                                '</span>' +
                                '<span class="pm-field-row__required-label">' + labels.required + '</span>' +
                            '</label>' +
                        '</div>' +
                        '<div class="pm-field-row__col pm-field-row__col--action">' +
                            '<button type="button" class="pm-field-row__remove js-p2p-remove-field" aria-label="' + labels.remove + '" title="' + labels.remove + '">' +
                                '<i class="fa-regular fa-trash-can" aria-hidden="true"></i>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="pm-field-row__options js-p2p-field-options-wrap"' + optionsHidden + '>' +
                        '<label>' + labels.options + '</label>' +
                        '<textarea name="' + prefix + '[' + index + '][options]" rows="3" placeholder="Option 1&#10;Option 2">' + escapeHtml(normalized.options) + '</textarea>' +
                    '</div>' +
                '</div>';
        };

        const ensureBuilderSection = function ($form, mode) {
            const sectionId = mode === 'create' ? 'p2p-runtime-create-fields' : 'p2p-runtime-manage-fields';
            let $section = $('#' + sectionId);
            if ($section.length) {
                return $section;
            }

            $section = $(
                '<div class="pm-field-builder" id="' + sectionId + '">' +
                    '<div class="pm-field-builder__head">' +
                        '<div>' +
                            '<div class="pm-field-builder__title">' + labels.dynamicFields + '</div>' +
                            '<div class="pm-section__hint">' + labels.dynamicFieldsHint + '</div>' +
                        '</div>' +
                        '<button type="button" class="fb-btn fb-btn--ghost fb-btn--sm js-p2p-add-field" data-mode="' + mode + '">' +
                            '<i class="fa-solid fa-plus" aria-hidden="true"></i>' +
                            '<span>' + labels.addField + '</span>' +
                        '</button>' +
                    '</div>' +
                    '<div class="pm-field-list__headers" aria-hidden="true">' +
                        '<span>' + labels.fieldLabel + '</span>' +
                        '<span>' + labels.fieldKey + '</span>' +
                        '<span>' + labels.fieldType + '</span>' +
                        '<span>' + labels.required + '</span>' +
                        '<span></span>' +
                    '</div>' +
                    '<div class="pm-field-list pm-runtime-fields-list"></div>' +
                '</div>'
            );

            const $manageAnchor = $form.find('.p2p-method-runtime-anchor').first();
            if (mode === 'manage' && $manageAnchor.length) {
                $manageAnchor.append($section);
                return $section;
            }

            const $createAnchor = $form.find('.p2p-method-runtime-anchor').first();
            if ($createAnchor.length) {
                $createAnchor.append($section);
                return $section;
            }

            $form.append($section);

            return $section;
        };

        const renderBuilder = function ($form, mode, fields) {
            const $section = ensureBuilderSection($form, mode);
            const $list = $section.find('.pm-runtime-fields-list');
            const normalized = normalizeFields(fields);

            $list.empty();
            normalized.forEach(function (field, index) {
                $list.append(fieldRowHtml('fields', index, field));
            });
        };

        const addFieldRow = function ($form, mode) {
            const $section = ensureBuilderSection($form, mode);
            const $list = $section.find('.pm-runtime-fields-list');
            const index = $list.find('.pm-field-row').length;
            $list.append(fieldRowHtml('fields', index, {
                label: '',
                key: '',
                type: 'text',
                required: false,
                options: '',
                sort_order: index + 1,
            }));
        };

        const loadMethodSchema = function (methodId) {
            if (!methodId) {
                return $.Deferred().resolve({fields: defaultFields}).promise();
            }

            const url = schemaUrlTemplate.replace('__METHOD__', String(methodId));
            return $.getJSON(url).then(null, function () {
                return {fields: defaultFields};
            });
        };

        // Logo drop-zone preview
        const bindLogoZone = function ($zone) {
            if (!$zone || !$zone.length) return;
            const $input = $zone.find('.pm-logo-zone__file');
            const $preview = $zone.find('.pm-logo-zone__preview img');
            const $previewIcon = $zone.find('.pm-logo-zone__preview i');
            const $title = $zone.find('.pm-logo-zone__title');
            const $hint = $zone.find('.pm-logo-zone__hint');
            const defaultTitle = $title.attr('data-default') || $title.text();
            const defaultHint = $hint.attr('data-default') || $hint.text();

            $zone.on('click', function (e) {
                if ($(e.target).is('button, a')) return;
                $input.trigger('click');
            });

            $input.on('change', function () {
                const file = this.files && this.files[0];
                if (!file) {
                    $title.text(defaultTitle);
                    $hint.text(defaultHint);
                    return;
                }

                $title.text(file.name);
                const sizeKb = (file.size / 1024).toFixed(1);
                $hint.text(sizeKb + ' KB');

                if (/^image\//.test(file.type)) {
                    const reader = new FileReader();
                    reader.onload = function (ev) {
                        $preview.attr('src', ev.target.result).removeClass('d-none');
                        $previewIcon.addClass('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });
        };

        bindLogoZone($('#p2p_add_method_modal .pm-logo-zone'));
        bindLogoZone($('#p2p_manage_method_modal .pm-logo-zone'));

        renderBuilder($createForm, 'create', createOldFields);

        $(document).on('click', '.js-p2p-add-field', function () {
            const mode = String($(this).data('mode') || 'create');
            addFieldRow(mode === 'manage' ? $manageForm : $createForm, mode);
        });

        $(document).on('click', '.js-p2p-remove-field', function () {
            const $row = $(this).closest('.pm-field-row');
            const $list = $row.parent();
            if ($list.find('.pm-field-row').length <= 1) {
                return;
            }
            $row.remove();
        });

        $(document).on('change', '.js-p2p-field-type', function () {
            const isSelect = String($(this).val()) === 'select';
            $(this).closest('.pm-field-row').find('.js-p2p-field-options-wrap').toggle(isSelect);
        });

        $(document).on('click', '.p2p-method-manage', function () {
            const raw = $(this).attr('data-method');
            const method = raw ? JSON.parse(raw) : {};
            const updateUrl = $(this).data('update-url');

            $('#p2p_manage_method_id').val(method.id || '');
            $('#p2p_manage_name').val(method.name || '');
            $('#p2p_manage_country').val(method.country || '');
            $('#p2p_manage_instructions').val(method.instructions || '');
            $('#p2p_manage_status').prop('checked', Number(method.status || 0) === 1).trigger('change');

            const $logoZone = $('#p2p_manage_method_modal .pm-logo-zone');
            const $previewImg = $logoZone.find('.pm-logo-zone__preview img');
            const $previewIcon = $logoZone.find('.pm-logo-zone__preview i');
            const $title = $logoZone.find('.pm-logo-zone__title');
            const $hint = $logoZone.find('.pm-logo-zone__hint');

            if (method.logo_url) {
                $previewImg.attr('src', method.logo_url).removeClass('d-none');
                $previewIcon.addClass('d-none');
                $title.text(method.name + @json(' '.__('logo')));
                $hint.text(@json(__('Click to replace')));
            } else {
                $previewImg.attr('src', '').addClass('d-none');
                $previewIcon.removeClass('d-none');
                $title.text(@json(__('No logo uploaded')));
                $hint.text(@json(__('Click or drop an image (max 2 MB)')));
            }

            $manageForm.attr('action', updateUrl || '');
            loadMethodSchema(method.id || null).done(function (payload) {
                renderBuilder($manageForm, 'manage', payload && Array.isArray(payload.fields) ? payload.fields : defaultFields);
                $manageModal.modal('show');
            });
        });

        // Live label on the status banner
        const refreshStatusBanner = function ($scope) {
            const $checkbox = $scope.find('[data-pm-status-input]');
            const $banner = $scope.find('[data-pm-status-banner]');
            if (!$checkbox.length || !$banner.length) return;
            const on = $checkbox.prop('checked');
            $banner.toggleClass('is-on', on).toggleClass('is-off', !on);
            $banner.find('[data-pm-status-label]').text(on
                ? @json(__('Active · accepting trades'))
                : @json(__('Inactive · hidden from traders'))
            );
            $banner.find('[data-pm-status-hint]').text(on
                ? @json(__('Traders can use this rail to fund or settle orders.'))
                : @json(__('No new orders use this rail. Existing offers stay locked.'))
            );
        };

        $(document).on('change', '[data-pm-status-input]', function () {
            refreshStatusBanner($(this).closest('.modal'));
        });
        refreshStatusBanner($createModal);
        refreshStatusBanner($manageModal);

        if (@json(old('form_type')) === 'create_method') {
            $createModal.modal('show');
        }

        if (@json(old('form_type')) === 'update_method' && @json(old('method_id'))) {
            const id = @json(old('method_id'));
            const template = @json(route('admin.p2p.methods.update', 0));
            const url = template.replace(/\/0$/, '/' + id);

            $('#p2p_manage_method_id').val(id);
            $manageForm.attr('action', url);

            if ($('#p2p_manage_name').val() === '') {
                $('#p2p_manage_name').val(@json(old('name')));
            }
            if ($('#p2p_manage_country').val() === '') {
                $('#p2p_manage_country').val(@json(old('country')));
            }
            if ($('#p2p_manage_instructions').val() === '') {
                $('#p2p_manage_instructions').val(@json(old('instructions')));
            }
            $('#p2p_manage_status').prop('checked', Number(@json(old('status', 0))) === 1).trigger('change');
            renderBuilder($manageForm, 'manage', updateOldFields.length ? updateOldFields : defaultFields);
            $manageModal.modal('show');
        }
    });
</script>
