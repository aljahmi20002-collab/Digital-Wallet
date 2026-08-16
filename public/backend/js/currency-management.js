"use strict";

$(document).ready(function () {
    const configElement = document.getElementById("currency-admin-config");
    const config = configElement ? JSON.parse(configElement.textContent || "{}") : {};
    const currencies = config.currencies || {};
    const $modalForm = $(".currency-modal-form");
    const currencySelectSelector = "[data-currency-search-select]";
    const $rows = $("[data-currency-row]");
    const $buttons = $("[data-currency-filter]");
    const $search = $("#currency-admin-search");
    const $empty = $("#currency-admin-no-results");
    const $rateEls = $(".js-rate");
    const $progressBars = $("[data-currency-progress-pct]");
    const $defaultChangeModal = $("#currency_default_change_modal");
    const $defaultChangeConfirm = $("[data-default-change-confirm]");
    const $defaultChangeConfirmCheck = $("[data-default-change-confirm-check]");
    let pendingDefaultChangeForm = null;
    let currentFilter = "all";

    function unique(items) {
        return Array.from(new Set(items));
    }

    function chunk(items, size) {
        return items.reduce((acc, _, index) => {
            return index % size ? acc : acc.concat([items.slice(index, index + size)]);
        }, []);
    }

    function getCurrencyForm(element) {
        return $(element).closest(".currency-drawer__form, form");
    }

    function getSelectedOptionText(select) {
        const option = select.options[select.selectedIndex];

        return option ? option.text : (config.selectCurrencyLabel || "Select Currency");
    }

    function resetDefaultChangeModal() {
        $defaultChangeConfirmCheck.prop("checked", false);
        $defaultChangeConfirm.prop("disabled", true);
    }

    function showDefaultChangeModal(form) {
        const $form = $(form);
        const nextCode = ($form.find("#currency_code").val() || $form.data("currency-code") || "").toString().trim().toUpperCase();
        const nextName = ($form.find("#site_currency").val() || $form.data("currency-name") || nextCode || "Selected currency").toString();
        const currentCode = (config.baseCurrency || $("[data-default-change-current]").first().text() || "").toString().trim().toUpperCase();
        const nextLabel = nextCode ? `${nextName} (${nextCode})` : nextName;

        pendingDefaultChangeForm = form;
        resetDefaultChangeModal();

        $("[data-default-change-current]").text(currentCode || "Current currency");
        $("[data-default-change-next]").text(nextCode || nextName);
        $("[data-default-change-next-name]").text(nextLabel);

        if ($defaultChangeModal.length) {
            $defaultChangeModal.modal("show");
        } else if (window.confirm("Changing the default currency can affect site calculations, reports, fees, limits, and exchange rates. Continue?")) {
            confirmDefaultCurrencyChange();
        }
    }

    function shouldReviewDefaultChange(form) {
        const $form = $(form);
        const isCurrentDefault = String($form.attr("data-current-default")) === "1";
        const wantsDefault = $form.find("[data-default-currency-checkbox]").is(":checked");
        const acknowledged = $form.find("[data-default-change-acknowledged]").val() === "1";

        return wantsDefault && !isCurrentDefault && !acknowledged;
    }

    function resetDefaultChangeAcknowledgement(form) {
        const $form = $(form);

        $form.find("[data-default-change-acknowledged]").val("0");
    }

    function confirmDefaultCurrencyChange() {
        if (!pendingDefaultChangeForm) {
            return;
        }

        const form = pendingDefaultChangeForm;

        $(form).find("[data-default-change-acknowledged]").val("1");
        $defaultChangeModal.modal("hide");
        pendingDefaultChangeForm = null;

        if (typeof form.requestSubmit === "function") {
            form.requestSubmit();
            return;
        }

        form.submit();
    }

    function syncCurrencySearchLabel(select) {
        const wrapper = select.nextElementSibling;
        const label = wrapper ? wrapper.querySelector("[data-currency-search-label]") : null;

        if (label) {
            label.textContent = getSelectedOptionText(select);
        }
    }

    function renderCurrencySearchOptions(select, query) {
        const wrapper = select.nextElementSibling;
        const list = wrapper ? wrapper.querySelector("[data-currency-search-list]") : null;

        if (!list) {
            return;
        }

        const normalizedQuery = (query || "").trim().toLowerCase();
        const options = Array.from(select.options).filter((option) => {
            if (option.disabled || !option.value) {
                return false;
            }

            return !normalizedQuery || option.text.toLowerCase().includes(normalizedQuery) || option.value.toLowerCase().includes(normalizedQuery);
        });

        list.innerHTML = "";

        if (!options.length) {
            const empty = document.createElement("div");
            empty.className = "currency-search-select__empty";
            empty.textContent = config.noCurrencyResultsLabel || "No currencies found";
            list.appendChild(empty);
            return;
        }

        options.forEach((option) => {
            const item = document.createElement("button");
            item.type = "button";
            item.className = "currency-search-select__option";
            item.dataset.currencySearchValue = option.value;
            item.setAttribute("aria-selected", option.selected ? "true" : "false");
            item.textContent = option.text;
            list.appendChild(item);
        });
    }

    function closeCurrencySearch(exceptWrapper) {
        document.querySelectorAll(".currency-search-select.is-open").forEach((wrapper) => {
            if (wrapper !== exceptWrapper) {
                wrapper.classList.remove("is-open");
                wrapper.querySelector("[data-currency-search-toggle]")?.setAttribute("aria-expanded", "false");
            }
        });
    }

    function refreshCurrencySearchSelect(select) {
        renderCurrencySearchOptions(select, "");
        syncCurrencySearchLabel(select);
    }

    function enhanceCurrencySearchSelect(select) {
        if (!select || select.dataset.currencySearchEnhanced === "1") {
            return;
        }

        select.dataset.currencySearchEnhanced = "1";
        select.classList.add("currency-search-select__native");

        const wrapper = document.createElement("div");
        wrapper.className = "currency-search-select";
        wrapper.innerHTML = `
            <button type="button" class="currency-search-select__toggle" data-currency-search-toggle aria-expanded="false">
                <span data-currency-search-label>${getSelectedOptionText(select)}</span>
                <span class="currency-search-select__chevron" aria-hidden="true"></span>
            </button>
            <div class="currency-search-select__panel">
                <div class="currency-search-select__search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <input type="search" autocomplete="off" data-currency-search-input placeholder="${config.searchCurrencyLabel || "Search currency..."}">
                </div>
                <div class="currency-search-select__list" data-currency-search-list></div>
            </div>
        `;

        select.insertAdjacentElement("afterend", wrapper);
        refreshCurrencySearchSelect(select);
    }

    function enhanceCurrencySearchSelects(context) {
        $(context || document).find(currencySelectSelector).each(function () {
            enhanceCurrencySearchSelect(this);
        });
    }

    function observeCurrencyModalContent() {
        const editAppend = document.getElementById("edit_currency_append");

        if (!editAppend || typeof MutationObserver === "undefined") {
            return;
        }

        const observer = new MutationObserver(() => {
            enhanceCurrencySearchSelects(editAppend);
        });

        observer.observe(editAppend, { childList: true, subtree: true });
    }

    function populateCurrencyOptions(currencyType, form) {
        const selectedCurrencies = currencies[currencyType] || [];
        const $form = form ? $(form) : $modalForm;
        const $select = $form.find("#site_currency");

        $select.empty().append(
            $("<option>", { disabled: true, selected: true }).text(config.selectCurrencyLabel || "Select Currency")
        );

        selectedCurrencies.forEach((currency) => {
            $select.append(
                $("<option>", { value: currency.name }).text(`${currency.name} (${currency.code})`)
            );
        });

        $select.each(function () {
            refreshCurrencySearchSelect(this);
        });
    }

    function updateCurrencyDetails(selectedCurrency, form) {
        const allCurrencies = [...(currencies.fiat || []), ...(currencies.crypto || [])];
        const currencyData = allCurrencies.find((currency) => currency.name === selectedCurrency);
        const $form = form ? $(form) : $modalForm;

        if (currencyData) {
            $form.find("#currency_code").val(currencyData.code);
            $form.find("#currency_symbol").val(currencyData.symbol);
            $form.find("#currency-selected, [data-currency-selected]").text(currencyData.code);
        }
    }

    function matchesFilter($row) {
        if (currentFilter === "all") {
            return true;
        }

        if (currentFilter === "active") {
            return $row.data("status") === "active";
        }

        if (currentFilter === "live") {
            return $row.data("live") === "live";
        }

        return $row.data("type") === currentFilter;
    }

    function applyCurrencyFilters() {
        const query = ($search.val() || "").toString().trim().toLowerCase();
        let visible = 0;

        $rows.each(function (index) {
            const $row = $(this);
            const searchable = ($row.data("search") || "").toString();
            const show = matchesFilter($row) && (!query || searchable.includes(query));

            $row.toggleClass("d-none", !show);
            if (show) {
                $row[0].style.setProperty("--currency-row-delay", `${Math.min(index * 18, 260)}ms`);
                visible += 1;
            }
        });

        $buttons.attr("aria-pressed", "false");
        $buttons.filter(`[data-currency-filter="${currentFilter}"]`).attr("aria-pressed", "true");
        $empty.toggleClass("d-none", visible > 0);
    }

    function applyRates(items) {
        items.forEach((item) => {
            const $targets = $rateEls.filter(`[data-code="${item.code}"]`);

            if ($targets.length) {
                $targets.text(item.rate);
                $targets.closest("strong").find(".js-rate-spinner").addClass("d-none");
            }
        });
    }

    function fetchRates() {
        if (!$rateEls.length || !config.ratesEndpoint) {
            return;
        }

        const liveCodes = unique($rateEls.filter('[data-live="1"]').map(function () {
            return $(this).data("code");
        }).get());

        if (!liveCodes.length) {
            return;
        }

        let delay = 0;
        chunk(liveCodes, 25).forEach((codesChunk) => {
            setTimeout(() => {
                $.ajax({
                    url: config.ratesEndpoint,
                    method: "GET",
                    data: {
                        base: config.baseCurrency,
                        codes: codesChunk,
                    },
                    cache: true,
                }).done((response) => {
                    if (response && Array.isArray(response.data)) {
                        applyRates(response.data);
                    }
                }).fail(() => {
                    setTimeout(() => {
                        $(".js-rate-spinner").addClass("d-none");
                    }, 3000);
                });
            }, delay);

            delay += 250;
        });

        setTimeout(() => {
            $(".js-rate-spinner").addClass("d-none");
        }, 12000);
    }

    function hydrateProgressBars() {
        $progressBars.each(function () {
            const percent = Number(this.getAttribute("data-currency-progress-pct")) || 0;
            const clampedPercent = Math.min(100, Math.max(0, percent));

            this.style.setProperty("--currency-active-percent", `${clampedPercent}%`);
        });
    }

    $(document).on("change", ".currency-drawer__form #site_currency_type, .currency-modal-form #site_currency_type", function () {
        populateCurrencyOptions($(this).val(), getCurrencyForm(this));
    });

    $(document).on("change", ".currency-drawer__form #site_currency, .currency-modal-form #site_currency", function () {
        updateCurrencyDetails($(this).val(), getCurrencyForm(this));
        syncCurrencySearchLabel(this);
    });

    $(document).on("keyup", ".currency-drawer__form #currency_code, .currency-modal-form #currency_code", function () {
        getCurrencyForm(this).find("#currency-selected, [data-currency-selected]").text($(this).val());
    });

    $(document).on("click", "[data-currency-search-toggle]", function () {
        const wrapper = this.closest(".currency-search-select");
        const select = wrapper ? wrapper.previousElementSibling : null;
        const input = wrapper ? wrapper.querySelector("[data-currency-search-input]") : null;
        const isOpening = !wrapper.classList.contains("is-open");

        closeCurrencySearch(wrapper);
        wrapper.classList.toggle("is-open", isOpening);
        this.setAttribute("aria-expanded", isOpening ? "true" : "false");

        if (isOpening && select && input) {
            input.value = "";
            renderCurrencySearchOptions(select, "");
            input.focus();
        }
    });

    $(document).on("input", "[data-currency-search-input]", function () {
        const wrapper = this.closest(".currency-search-select");
        const select = wrapper ? wrapper.previousElementSibling : null;

        if (select) {
            renderCurrencySearchOptions(select, this.value);
        }
    });

    $(document).on("click", "[data-currency-search-value]", function () {
        const wrapper = this.closest(".currency-search-select");
        const select = wrapper ? wrapper.previousElementSibling : null;

        if (!select) {
            return;
        }

        select.value = this.dataset.currencySearchValue;
        select.dispatchEvent(new Event("change", { bubbles: true }));
        wrapper.classList.remove("is-open");
        wrapper.querySelector("[data-currency-search-toggle]")?.setAttribute("aria-expanded", "false");
    });

    $(document).on("click", function (event) {
        if (!event.target.closest(".currency-search-select")) {
            closeCurrencySearch();
        }
    });

    $(document).on("click", "[data-role-toggle]", function () {
        const item = this.closest("[data-role-item]");

        if (item) {
            item.classList.toggle("is-open");
        }
    });

    $(document).on("change", "[data-role-status]", function () {
        const item = this.closest("[data-role-item]");
        const state = item ? item.querySelector("[data-role-state]") : null;

        if (!state) {
            return;
        }

        state.classList.toggle("is-active", this.checked);
        state.textContent = this.checked ? (config.activeLabel || "ACTIVE") : (config.inactiveLabel || "INACTIVE");
    });

    $(document).on("change", "[data-default-currency-checkbox]", function () {
        resetDefaultChangeAcknowledgement(getCurrencyForm(this));
    });

    $(document).on("submit", "[data-default-currency-form]", function (event) {
        if (!shouldReviewDefaultChange(this)) {
            return;
        }

        event.preventDefault();
        showDefaultChangeModal(this);
    });

    $defaultChangeConfirmCheck.on("change", function () {
        $defaultChangeConfirm.prop("disabled", !this.checked);
    });

    $defaultChangeConfirm.on("click", function () {
        if ($defaultChangeConfirm.prop("disabled")) {
            return;
        }

        confirmDefaultCurrencyChange();
    });

    $defaultChangeModal.on("hidden.coreui.modal hidden.bs.modal", resetDefaultChangeModal);

    $buttons.on("click", function () {
        currentFilter = $(this).data("currency-filter");
        $buttons.removeClass("active");
        $(this).addClass("active");
        applyCurrencyFilters();
    });

    $search.on("input", applyCurrencyFilters);

    hydrateProgressBars();
    editFormByModal("edit_currency_modal", "edit_currency_append", true, true);
    enhanceCurrencySearchSelects(document);
    observeCurrencyModalContent();
    applyCurrencyFilters();
    fetchRates();

    document.addEventListener("shown.coreui.modal", function (event) {
        enhanceCurrencySearchSelects(event.target);
    });
});
