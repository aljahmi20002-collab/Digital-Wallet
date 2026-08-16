<script>
"use strict";
document.addEventListener('DOMContentLoaded', function () {
    const FALLBACK_CURRENCY = @json(siteCurrency('code'));

    const dom = {
        paymentMethodChecks: document.querySelectorAll('.p2p-pm-check'),
        paymentMethodCountry: document.getElementById('p2pPaymentMethodsCountry'),
        paymentMethodSearch: document.getElementById('p2pPaymentMethodsSearch'),
        paymentMethodItems: document.querySelectorAll('.js-p2p-payment-method-item'),
        paymentMethodsTitle: document.getElementById('p2pPaymentMethodsTitle'),
        paymentMethodsSubtitle: document.getElementById('p2pPaymentMethodsSubtitle'),
        paymentMethodsHint: document.getElementById('p2pPaymentMethodsHint'),
        paymentMethodsSelectedCount: document.getElementById('p2pPaymentMethodsSelectedCount'),
        paymentMethodsEmptyState: document.getElementById('p2pPaymentMethodsEmptyState'),

        offerWalletSelect: document.getElementById('p2pOfferWallet'),
        amountCurrencyPrice: document.getElementById('p2pAmountCurrencyPrice'),
        amountCurrencyMin: document.getElementById('p2pAmountCurrencyMin'),
        amountCurrencyMax: document.getElementById('p2pAmountCurrencyMax'),

        promoteToggle: document.getElementById('p2pPromoteNow'),
        packageWrap: document.getElementById('p2pPromotionPackageWrap'),
        walletWrap: document.getElementById('p2pPromotionWalletWrap'),
        previewWrap: document.getElementById('p2pPromotionPreviewWrap'),
        packageSelect: document.getElementById('p2pPromotionPackage'),
        promotionWalletSelect: document.getElementById('p2pPromotionWallet'),
        sideSelect: document.querySelector('select[name="side"]'),

        previewPrice: document.getElementById('p2pPromotionPreviewPrice'),
        previewDuration: document.getElementById('p2pPromotionPreviewDuration'),
        previewSide: document.getElementById('p2pPromotionPreviewSide'),
        previewPriority: document.getElementById('p2pPromotionPreviewPriority'),
        previewFeatures: document.getElementById('p2pPromotionPreviewFeatures'),
        previewRules: document.getElementById('p2pPromotionPreviewRules'),
        previewEmpty: document.getElementById('p2pPromotionPreviewEmpty'),
        previewMeta: document.getElementById('p2pPromotionPreviewMeta'),
        previewFeaturesSection: document.getElementById('p2pPromotionPreviewFeaturesSection'),
        previewRulesSection: document.getElementById('p2pPromotionPreviewRulesSection'),

        previewPriceItem: document.getElementById('p2pPromotionPreviewPriceItem'),
        previewDurationItem: document.getElementById('p2pPromotionPreviewDurationItem'),
        previewSideItem: document.getElementById('p2pPromotionPreviewSideItem'),
        previewPriorityItem: document.getElementById('p2pPromotionPreviewPriorityItem'),
        packageHint: document.getElementById('p2pPromotionPackageHint'),
    };

    const featureMeta = [
        {
            attr: 'data-featured-listing',
            icon: 'fas fa-bolt',
            label: @json(__('Featured Listing')),
            benefit: @json(__('Your offer appears as featured so users notice it first.'))
        },
        {
            attr: 'data-highlighted-card',
            icon: 'fas fa-star',
            label: @json(__('Highlighted Card UI')),
            benefit: @json(__('Adds stronger visual styling so your listing stands out in the feed.'))
        },
        {
            attr: 'data-search-priority-boost',
            icon: 'fas fa-search-plus',
            label: @json(__('Search Priority Boost')),
            benefit: @json(__('Improves ranking position in search results.'))
        },
        {
            attr: 'data-featured-badge',
            icon: 'fas fa-check-square',
            label: @json(__('Featured')),
            benefit: @json(__('Adds a featured marker to improve offer visibility.'))
        }
    ];

    const ruleMeta = {
        autoRenewAllowed: {
            icon: 'fas fa-sync-alt',
            label: @json(__('Auto renew')),
            value: @json(__('Enabled')),
            tone: 'success'
        },
        autoRenewNotAllowed: {
            icon: 'fas fa-exclamation-triangle',
            label: @json(__('Auto renew')),
            value: @json(__('Disabled')),
            tone: 'warning'
        },
        categories: {
            icon: 'fas fa-layer-group',
            label: @json(__('Categories')),
            tone: ''
        }
    };

    const textMap = {
        minutes: @json(__('minutes')),
        hours: @json(__('hours')),
        buyOnly: @json(__('Buy Ads Only')),
        sellOnly: @json(__('Sell Ads Only')),
        bothSides: @json(__('Buy Ads + Sell Ads')),
        categories: @json(__('Categories')),
        selectPackage: @json(__('Select a package to see details')),
        showingForSide: @json(__('Showing packages for side')),
        noPackageForSide: @json(__('No package available for selected side')),
        priorityBasic: @json(__('Basic')),
        priorityMedium: @json(__('Medium')),
        priorityHigh: @json(__('High')),
        priorityVeryHigh: @json(__('Very High')),
        acceptedMethodsTitle: @json(__('Accepted Payment Methods')),
        acceptedMethodsSubtitle: @json(__('Choose which of your saved payment accounts buyers can pay to.')),
        acceptedMethodsHint: @json(__('Choose which of your saved payment accounts buyers can pay to.')),
        preferredMethodsTitle: @json(__('Preferred / Available Payment Methods')),
        preferredMethodsSubtitle: @json(__('Select the payment methods you can use to send payment from your saved accounts.')),
        preferredMethodsHint: @json(__('Select the payment methods you can use to send payment. Sellers will see these before starting a trade.'))
    };

    const getSelectedOption = function (selectEl) {
        if (!selectEl || selectEl.selectedIndex < 0) {
            return null;
        }

        return selectEl.options[selectEl.selectedIndex] || null;
    };

    const getDataAttr = function (el, attr, fallback) {
        if (!el) {
            return String(fallback || '');
        }

        const value = el.getAttribute(attr);
        if (value === null || value === '') {
            return String(fallback || '');
        }

        return String(value);
    };

    const setVisible = function (el, visible, displayValue) {
        if (!el) {
            return;
        }

        el.classList.toggle('d-none', !visible);
    };

    const setText = function (el, value) {
        if (el) {
            el.textContent = value;
        }
    };

    const escapeHtml = function (value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    const formatAmount = function (value) {
        const num = parseFloat(String(value || ''));
        if (!Number.isFinite(num)) {
            return '';
        }

        return num.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 8
        });
    };

    const formatDuration = function (minutes) {
        const value = parseInt(String(minutes || '0'), 10) || 0;
        if (value <= 0) {
            return '';
        }

        if (value >= 60) {
            const hours = value / 60;
            const pretty = Number.isInteger(hours)
                ? String(hours)
                : String(hours.toFixed(2)).replace(/\.?0+$/, '');

            return pretty + ' ' + textMap.hours;
        }

        return value + ' ' + textMap.minutes;
    };

    const humanSide = function (value) {
        if (String(value || '').toUpperCase() === 'BUY') {
            return textMap.buyOnly;
        }
        if (String(value || '').toUpperCase() === 'SELL') {
            return textMap.sellOnly;
        }

        return textMap.bothSides;
    };

    const priorityLabel = function (value) {
        const score = parseInt(String(value || '0'), 10);
        if (Number.isNaN(score) || score <= 0) {
            return '';
        }

        if (score >= 80) {
            return score + ' (' + textMap.priorityVeryHigh + ')';
        }
        if (score >= 50) {
            return score + ' (' + textMap.priorityHigh + ')';
        }
        if (score >= 20) {
            return score + ' (' + textMap.priorityMedium + ')';
        }

        return score + ' (' + textMap.priorityBasic + ')';
    };

    const parseCategories = function (raw) {
        const items = String(raw || '').split(',').map(function (item) {
            return item.trim();
        }).filter(function (item) {
            return item !== '';
        });

        if (items.length === 0 || (items.length === 1 && items[0] === 'ALL')) {
            return '';
        }

        return items.map(function (item) {
            const word = item.toLowerCase().replace(/_/g, ' ');
            return word.replace(/\b\w/g, function (char) {
                return char.toUpperCase();
            });
        }).join(', ');
    };

    const syncAmountCurrency = function () {
        const walletOption = getSelectedOption(dom.offerWalletSelect);
        const currency = getDataAttr(walletOption, 'data-currency', FALLBACK_CURRENCY);

        [dom.amountCurrencyPrice, dom.amountCurrencyMin, dom.amountCurrencyMax].forEach(function (el) {
            setText(el, currency);
        });
    };

    const updateSelectedPaymentMethodCount = function () {
        if (!dom.paymentMethodsSelectedCount) {
            return;
        }

        let selectedCount = 0;
        dom.paymentMethodChecks.forEach(function (checkbox) {
            if (checkbox.checked) {
                selectedCount += 1;
            }
        });

        dom.paymentMethodsSelectedCount.textContent = String(selectedCount);
    };

    const bindPaymentMethodSelection = function () {
        dom.paymentMethodChecks.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateSelectedPaymentMethodCount);
        });
    };

    const applyMethodFilters = function () {
        const selectedCountry = dom.paymentMethodCountry && dom.paymentMethodCountry.value
            ? String(dom.paymentMethodCountry.value)
            : '';

        const query = dom.paymentMethodSearch && dom.paymentMethodSearch.value
            ? String(dom.paymentMethodSearch.value).toLowerCase().trim()
            : '';

        let visibleCount = 0;

        dom.paymentMethodItems.forEach(function (item) {
            const methodCountry = String(item.getAttribute('data-country') || '');
            const text = String(item.getAttribute('data-search') || item.textContent || '').toLowerCase();
            const passSearch = query === '' || text.includes(query);
            let visible = false;

            if (selectedCountry === '') {
                visible = passSearch;
            } else if (selectedCountry === '__NONE__') {
                visible = methodCountry === '' && passSearch;
            } else {
                visible = methodCountry === selectedCountry && passSearch;
            }

            item.classList.toggle('d-none', !visible);

            if (visible) {
                visibleCount += 1;
            }
        });

        if (dom.paymentMethodsEmptyState) {
            dom.paymentMethodsEmptyState.classList.toggle('d-none', visibleCount > 0);
        }
    };

    const syncPaymentMethodSectionBySide = function () {
        const side = dom.sideSelect ? String(dom.sideSelect.value || '').toUpperCase() : '';
        const isSellSide = side === 'SELL';

        if (dom.paymentMethodsTitle) {
            dom.paymentMethodsTitle.textContent = isSellSide ? textMap.acceptedMethodsTitle : textMap.preferredMethodsTitle;
        }

        if (dom.paymentMethodsSubtitle) {
            dom.paymentMethodsSubtitle.textContent = isSellSide ? textMap.acceptedMethodsSubtitle : textMap.preferredMethodsSubtitle;
        }

        if (dom.paymentMethodsHint) {
            dom.paymentMethodsHint.textContent = isSellSide ? textMap.acceptedMethodsHint : textMap.preferredMethodsHint;
        }
    };

    const syncMetaItem = function (itemEl, valueEl, value, iconClass) {
        const hasValue = String(value || '').trim() !== '';
        setVisible(itemEl, hasValue);

        if (!hasValue || !valueEl) {
            return false;
        }

        if (iconClass) {
            valueEl.innerHTML = '<i class="' + iconClass + '"></i><span>' + value + '</span>';
        } else {
            valueEl.textContent = value;
        }

        return true;
    };

    const updatePromotionPreview = function () {
        const selectedPackage = getSelectedOption(dom.packageSelect);

        if (!selectedPackage || !selectedPackage.value) {
            setVisible(dom.previewEmpty, true);
            setText(dom.previewEmpty, textMap.selectPackage);

            setVisible(dom.previewMeta, false, 'grid');
            setVisible(dom.previewFeaturesSection, false);
            setVisible(dom.previewRulesSection, false);
            return;
        }

        setVisible(dom.previewEmpty, false);

        const basePrice = getDataAttr(selectedPackage, 'data-base-price', '0');
        const baseCurrency = getDataAttr(selectedPackage, 'data-base-currency', '');
        const duration = getDataAttr(selectedPackage, 'data-duration', '0');
        const currentSide = getDataAttr(selectedPackage, 'data-applies-to', 'BOTH').toUpperCase();
        const priority = getDataAttr(selectedPackage, 'data-search-priority', '0');
        const autoRenewAttr = selectedPackage.getAttribute('data-auto-renew');
        const autoRenew = String(autoRenewAttr || '0') === '1';
        const categories = getDataAttr(selectedPackage, 'data-allowed-categories', '');
        const sideIconClass = currentSide === 'SELL'
            ? 'fas fa-arrow-up'
            : (currentSide === 'BUY' ? 'fas fa-bullhorn' : 'fas fa-exchange-alt');

        const priceValue = formatAmount(basePrice);
        const durationValue = formatDuration(duration);
        const sideValue = humanSide(currentSide);
        const priorityValue = priorityLabel(priority);

        const visibleMetaCount = [
            syncMetaItem(dom.previewPriceItem, dom.previewPrice, priceValue ? (priceValue + (baseCurrency ? ' ' + baseCurrency : '')) : ''),
            syncMetaItem(dom.previewDurationItem, dom.previewDuration, durationValue, 'far fa-clock'),
            syncMetaItem(dom.previewSideItem, dom.previewSide, sideValue, sideIconClass),
            syncMetaItem(dom.previewPriorityItem, dom.previewPriority, priorityValue, 'far fa-circle')
        ].filter(Boolean).length;

        setVisible(dom.previewMeta, visibleMetaCount > 0, 'grid');

        const activeFeatures = featureMeta.filter(function (item) {
            return getDataAttr(selectedPackage, item.attr, '0') === '1';
        });

        if (activeFeatures.length > 0 && dom.previewFeatures) {
            dom.previewFeatures.innerHTML = activeFeatures.map(function (item) {
                return '<div class="p2p-promo-feature-card">'
                    + '<i class="' + item.icon + '"></i>'
                    + '<div><strong>' + item.label + '</strong><small>' + item.benefit + '</small></div>'
                    + '</div>';
            }).join('');
            setVisible(dom.previewFeaturesSection, true);
        } else {
            if (dom.previewFeatures) {
                dom.previewFeatures.innerHTML = '';
            }
            setVisible(dom.previewFeaturesSection, false);
        }

        const rules = [];
        if (autoRenewAttr !== null) {
            const autoRenewRule = autoRenew ? ruleMeta.autoRenewAllowed : ruleMeta.autoRenewNotAllowed;
            rules.push({
                icon: autoRenewRule.icon,
                label: autoRenewRule.label,
                value: autoRenewRule.value,
                tone: autoRenewRule.tone
            });
        }

        const categoryText = parseCategories(categories);
        if (categoryText !== '') {
            rules.push({
                icon: ruleMeta.categories.icon,
                label: ruleMeta.categories.label,
                value: categoryText,
                tone: ruleMeta.categories.tone
            });
        }

        if (rules.length > 0 && dom.previewRules) {
            dom.previewRules.innerHTML = rules.map(function (item) {
                const chipClass = item.tone ? ' p2p-promo-rule-chip--' + escapeHtml(item.tone) : '';

                return '<span class="p2p-promo-rule-chip' + chipClass + '">'
                    + '<i class="' + escapeHtml(item.icon) + '"></i>'
                    + '<span class="p2p-promo-rule-chip__label">' + escapeHtml(item.label) + ':</span>'
                    + '<span class="p2p-promo-rule-chip__value">' + escapeHtml(item.value) + '</span>'
                    + '</span>';
            }).join('');
            setVisible(dom.previewRulesSection, true);
        } else {
            if (dom.previewRules) {
                dom.previewRules.innerHTML = '';
            }
            setVisible(dom.previewRulesSection, false);
        }
    };

    const filterPackagesBySide = function () {
        if (!dom.packageSelect) {
            return;
        }

        const side = dom.sideSelect ? String(dom.sideSelect.value || '').toUpperCase() : '';
        let selectedStillValid = false;
        let availableCount = 0;

        Array.from(dom.packageSelect.options).forEach(function (opt, index) {
            if (index === 0) {
                return;
            }

            const appliesTo = String(opt.getAttribute('data-applies-to') || 'BOTH').toUpperCase();
            const allowed = !side || appliesTo === 'BOTH' || appliesTo === side;

            opt.disabled = !allowed;
            opt.hidden = !allowed;

            if (allowed) {
                availableCount += 1;
            }

            if (dom.packageSelect.value === opt.value && allowed) {
                selectedStillValid = true;
            }
        });

        if (dom.packageHint) {
            if (availableCount > 0) {
                dom.packageHint.classList.remove('text-danger');
                dom.packageHint.classList.add('text-muted');
                dom.packageHint.textContent = textMap.showingForSide + ': ' + humanSide(side) + ' (' + availableCount + ')';
            } else {
                dom.packageHint.classList.remove('text-muted');
                dom.packageHint.classList.add('text-danger');
                dom.packageHint.textContent = textMap.noPackageForSide + ': ' + humanSide(side);
            }
        }

        if (dom.packageSelect.value && !selectedStillValid) {
            dom.packageSelect.value = '';
        }

        updatePromotionPreview();
    };

    const syncPromotionVisibility = function () {
        const enabled = dom.promoteToggle && dom.promoteToggle.checked;

        setVisible(dom.packageWrap, enabled);
        setVisible(dom.walletWrap, enabled);
        setVisible(dom.previewWrap, enabled);

        if (dom.packageSelect) {
            dom.packageSelect.required = Boolean(enabled);
        }
        if (dom.promotionWalletSelect) {
            dom.promotionWalletSelect.required = Boolean(enabled);
        }
    };

    const bindEvents = function () {
        if (dom.offerWalletSelect) {
            dom.offerWalletSelect.addEventListener('change', syncAmountCurrency);
        }

        if (dom.paymentMethodCountry) {
            dom.paymentMethodCountry.addEventListener('change', applyMethodFilters);
        }

        if (dom.paymentMethodSearch) {
            dom.paymentMethodSearch.addEventListener('input', applyMethodFilters);
        }

        if (dom.promoteToggle) {
            dom.promoteToggle.addEventListener('change', syncPromotionVisibility);
        }

        if (dom.packageSelect) {
            dom.packageSelect.addEventListener('change', updatePromotionPreview);
        }

        if (dom.sideSelect) {
            dom.sideSelect.addEventListener('change', filterPackagesBySide);
            dom.sideSelect.addEventListener('change', syncPaymentMethodSectionBySide);
        }
    };

    const init = function () {
        bindPaymentMethodSelection();
        bindEvents();

        applyMethodFilters();
        syncAmountCurrency();

        filterPackagesBySide();
        syncPaymentMethodSectionBySide();
        updateSelectedPaymentMethodCount();
        updatePromotionPreview();
        syncPromotionVisibility();
    };

    init();
});
</script>
