<script>
    $(document).ready(function () {
        'use strict';

        const $modalForm = $('.currency-modal-form');
        const currencies = @json(getJsonData('currencies'));

        // Populate currency options based on selected type
        function populateCurrencyOptions(currencyType) {
            const selectedCurrencies = currencies[currencyType] || [];

            // Clear and populate currency dropdown
            $modalForm.find('#site_currency').empty().append(
                $('<option>', { disabled: true, selected: true }).text('{{ __('Select Currency') }}')
            );

            selectedCurrencies.forEach(currency => {
                $modalForm.find('#site_currency').append(
                    $('<option>', { value: currency.name }).text(currency.name+' ('+currency.code+')')
                );
            });
        }

        // Update currency details based on the selected currency
        function updateCurrencyDetails(selectedCurrency) {
            const allCurrencies = [...currencies['fiat'], ...currencies['crypto']];
            const currencyData = allCurrencies.find(c => c.name === selectedCurrency);

            if (currencyData) {
                $modalForm.find('#currency_code').val(currencyData.code);
                $modalForm.find('#currency_symbol').val(currencyData.symbol);
                $modalForm.find('#currency-selected').text(currencyData.code);
            }
        }

        // Event handler for currency type change
        $modalForm.on('change', '#site_currency_type', function () {
            const currencyType = $(this).val();
            populateCurrencyOptions(currencyType);
        });

        // Event handler for currency selection change
        $modalForm.on('change', '#site_currency', function () {
            const selectedCurrency = $(this).val();
            updateCurrencyDetails(selectedCurrency);
        });

        // Event handler for currency code keyup
        $modalForm.on('keyup', '#currency_code', function () {
            const selectedCurrency = $(this).val();
            $modalForm.find('#currency-selected').text(selectedCurrency);
        });

        // Call to edit form modal function
        // This function is likely customized, so ensure any specific requirements it has are met
        editFormByModal('edit_currency_modal', 'edit_currency_append', true, true);

        // ---------------------------------------------
        // Progressive Exchange Rate Loader (non-blocking)
        // ---------------------------------------------
        const $rateEls = $('.js-rate');
        if ($rateEls.length) {
            const base = @json(siteCurrency());
            const unique = (arr) => Array.from(new Set(arr));
            const codes = unique($rateEls.map(function () { return $(this).data('code'); }).get());

            // Only fetch live currencies to reduce requests
            const liveCodes = unique($rateEls.filter('[data-live="1"]').map(function () { return $(this).data('code'); }).get());

            if (liveCodes.length) {
                const chunk = (arr, size) => arr.reduce((acc, _, i) => (i % size ? acc : acc.concat([arr.slice(i, i + size)])), []);
                const chunks = chunk(liveCodes, 25);

                const endpoint = @json(route('admin.currency.rates'));

                const applyRates = (items) => {
                    items.forEach(item => {
                        const $targets = $rateEls.filter('[data-code="' + item.code + '"]');
                        if ($targets.length) {
                            $targets.text(item.rate);
                            $targets.closest('strong').find('.js-rate-spinner').addClass('d-none');
                        }
                    });
                };

                const fetchChunk = (codesChunk) => {
                    return $.ajax({
                        url: endpoint,
                        method: 'GET',
                        data: { base: base, codes: codesChunk },
                        cache: true
                    }).done(function (res) {
                        if (res && Array.isArray(res.data)) {
                            applyRates(res.data);
                        }
                    }).fail(function () {
                        // Hide spinners on failure after a short delay to avoid endless loading indicator
                        setTimeout(function () { $('.js-rate-spinner').addClass('d-none'); }, 3000);
                    });
                };

                // Stagger requests slightly to avoid burst
                let delay = 0;
                chunks.forEach(function (c) {
                    setTimeout(function () { fetchChunk(c); }, delay);
                    delay += 250;
                });

                // Final safety: hide any remaining spinners after 12s
                setTimeout(function () { $('.js-rate-spinner').addClass('d-none'); }, 12000);
            }
        }
    });
</script>