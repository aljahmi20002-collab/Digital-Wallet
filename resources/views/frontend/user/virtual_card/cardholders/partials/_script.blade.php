<script>
    "use strict";

    /**
     * Toggle Personal vs Business field blocks based on the
     * "Cardholder Type" select.
     */
    function toggleElement(selector, visible) {
        document.querySelectorAll(selector).forEach(function (el) {
            el.classList.toggle('d-none', !visible);
        });
    }

    function toggleCardholderTypeFields() {
        const typeSelect = document.getElementById('card_type');
        if (!typeSelect) {
            return;
        }

        const type         = typeSelect.value;
        const personalType = @json(\App\Enums\VirtualCard\CardholderType::PERSONAL->value);
        const businessType = @json(\App\Enums\VirtualCard\CardholderType::BUSINESS->value);

        toggleElement('.personal-fields',         type === personalType);
        toggleElement('#personal-details-block',  type === personalType);
        toggleElement('.business-fields',         type === businessType);
        toggleElement('#business-details-block',  type === businessType);
    }

    document.getElementById('card_type')?.addEventListener('change', toggleCardholderTypeFields);
    window.addEventListener('DOMContentLoaded', toggleCardholderTypeFields);

    // ---------------------------------------------------------------
    // Beneficial Owners — repeatable group (KYB requirement)
    // Reindex on add/remove so server-side `beneficial_owners[i][...]`
    // never has gaps or duplicates.
    // ---------------------------------------------------------------
    (function () {
        "use strict";
        const $list = $('[data-vc-ubo-list]');
        if (!$list.length) {
            return;
        }

        function reindex() {
            $list.find('[data-vc-ubo-row]').each(function (idx) {
                $(this).find('[name]').each(function () {
                    const name = $(this).attr('name') || '';
                    $(this).attr('name', name.replace(/beneficial_owners\[\d+\]/, 'beneficial_owners[' + idx + ']'));
                });
            });
        }

        $(document).on('click', '[data-vc-ubo-add]', function () {
            const $rows     = $list.find('[data-vc-ubo-row]');
            const $template = $rows.first().clone(true);
            $template.find('input, select').each(function () {
                if ($(this).is('select')) {
                    $(this).val('');
                } else if ($(this).attr('type') !== 'button') {
                    $(this).val('');
                }
            });
            $list.append($template);
            reindex();
        });

        $(document).on('click', '[data-vc-ubo-remove]', function () {
            const $row  = $(this).closest('[data-vc-ubo-row]');
            const $rows = $list.find('[data-vc-ubo-row]');
            if ($rows.length <= 1) {
                // Keep one row visible — just clear it instead of removing.
                $row.find('input, select').each(function () {
                    if ($(this).is('select')) {
                        $(this).val('');
                    } else if ($(this).attr('type') !== 'button') {
                        $(this).val('');
                    }
                });
            } else {
                $row.remove();
            }
            reindex();
        });
    })();
</script>
