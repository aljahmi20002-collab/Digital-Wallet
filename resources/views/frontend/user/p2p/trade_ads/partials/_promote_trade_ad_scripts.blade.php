<script>
"use strict";
(function () {
    const txtHours = @json(__('hours'));
    const txtMinutes = @json(__('minutes'));
    const txtCalculating = @json(__('Calculating...'));
    const txtFailed = @json(__('Failed to calculate price.'));
    const txtExchangeRate = @json(__('Exchange rate:'));

    const packageSelect = document.getElementById('p2pPromotionPackage');
    const walletSelect = document.getElementById('p2pPromotionWallet');
    const basePriceEl = document.getElementById('p2pPromotionBasePrice');
    const payEl = document.getElementById('p2pPromotionPayAmount');
    const durationEl = document.getElementById('p2pPromotionDuration');
    const appliesToEl = document.getElementById('p2pPromotionAppliesTo');
    const priorityEl = document.getElementById('p2pPromotionPriority');
    const featuresEl = document.getElementById('p2pPromotionFeatures');
    const rulesEl = document.getElementById('p2pPromotionRules');
    const emptyEl = document.getElementById('p2pPromotionEmpty');
    const metaEl = document.getElementById('p2pPromotionMeta');
    const featuresSectionEl = document.getElementById('p2pPromotionFeaturesSection');
    const rulesSectionEl = document.getElementById('p2pPromotionRulesSection');
    const hintWrapEl = document.getElementById('p2pPromotionQuoteHintWrap');
    const basePriceItemEl = document.getElementById('p2pPromotionBasePriceItem');
    const payItemEl = document.getElementById('p2pPromotionPayAmountItem');
    const durationItemEl = document.getElementById('p2pPromotionDurationItem');
    const appliesToItemEl = document.getElementById('p2pPromotionAppliesToItem');
    const priorityItemEl = document.getElementById('p2pPromotionPriorityItem');
    const hintEl = document.getElementById('p2pPromotionQuoteHint');
    const submitBtn = document.getElementById('p2pPromotionSubmit');

    const featureLabels = {
        featuredListing: @json(__('Featured Listing')),
        highlightedCard: @json(__('Highlighted Card UI')),
        searchPriorityBoost: @json(__('Search Priority Boost')),
        featuredBadge: @json(__('Featured'))
    };

    const textMap = {
        buyOnly: @json(__('Buy Ads Only')),
        sellOnly: @json(__('Sell Ads Only')),
        both: @json(__('Both')),
        none: @json(__('None')),
        notSelected: @json(__('Not selected')),
        autoRenewAllowed: @json(__('Auto renew allowed')),
        autoRenewNotAllowed: @json(__('Auto renew disabled')),
        categories: @json(__('Categories')),
        selectPackage: @json(__('Select a package to see details')),
        selectWalletForQuote: @json(__('Select wallet to see payable amount')),
    };

    const setVisible = function (el, visible, displayValue) {
        if (!el) {
            return;
        }

        el.classList.toggle('d-none', !visible);
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
        const duration = parseInt(String(minutes || '0'), 10) || 0;
        if (duration <= 0) {
            return '';
        }

        if (duration >= 60) {
            const hours = duration / 60;
            const pretty = Number.isInteger(hours)
                ? String(hours)
                : String(hours.toFixed(2)).replace(/\.?0+$/, '');

            return pretty + ' ' + txtHours;
        }

        return duration + ' ' + txtMinutes;
    };

    const humanAppliesTo = function (value) {
        if (value === 'BUY') {
            return textMap.buyOnly;
        }
        if (value === 'SELL') {
            return textMap.sellOnly;
        }

        return textMap.both;
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

    const updateSummaryFromOption = function () {
        const pkgOpt = packageSelect ? packageSelect.options[packageSelect.selectedIndex] : null;
        const walOpt = walletSelect ? walletSelect.options[walletSelect.selectedIndex] : null;

        submitBtn.disabled = true;
        hintEl.textContent = '';

        if (!pkgOpt || !pkgOpt.value) {
            setVisible(emptyEl, true);
            if (emptyEl) {
                emptyEl.textContent = textMap.selectPackage;
            }

            setVisible(metaEl, false, 'grid');
            setVisible(featuresSectionEl, false);
            setVisible(rulesSectionEl, false);
            setVisible(hintWrapEl, false);
            return;
        }

        setVisible(emptyEl, false);

        const baseCurrency = String(pkgOpt.getAttribute('data-base-currency') || '');
        const basePrice = String(pkgOpt.getAttribute('data-base-price') || '');
        const durationRaw = String(pkgOpt.getAttribute('data-duration') || '0');
        const appliesTo = String(pkgOpt.getAttribute('data-applies-to') || 'BOTH');
        const priority = String(pkgOpt.getAttribute('data-search-priority') || '0');
        const autoRenewAttr = pkgOpt.getAttribute('data-auto-renew');
        const autoRenew = String(autoRenewAttr || '0') === '1';
        const categories = String(pkgOpt.getAttribute('data-allowed-categories') || '');

        const priceValue = formatAmount(basePrice);
        const durationValue = formatDuration(durationRaw);
        const appliesToValue = humanAppliesTo(appliesTo);
        const priorityInt = parseInt(priority, 10);
        const priorityValue = Number.isNaN(priorityInt) || priorityInt <= 0 ? '' : String(priorityInt);

        let visibleMetaCount = 0;
        const syncMetaItem = function (itemEl, valueEl, value) {
            const show = String(value || '').trim() !== '';
            setVisible(itemEl, show);

            if (show && valueEl) {
                valueEl.textContent = value;
                visibleMetaCount += 1;
            }
        };

        syncMetaItem(basePriceItemEl, basePriceEl, priceValue ? (priceValue + (baseCurrency ? ' ' + baseCurrency : '')) : '');
        syncMetaItem(durationItemEl, durationEl, durationValue);
        syncMetaItem(appliesToItemEl, appliesToEl, appliesToValue);
        syncMetaItem(priorityItemEl, priorityEl, priorityValue);
        setVisible(payItemEl, false);
        setVisible(metaEl, visibleMetaCount > 0, 'grid');

        const featureItems = [
            { attr: 'data-featured-listing', label: featureLabels.featuredListing },
            { attr: 'data-highlighted-card', label: featureLabels.highlightedCard },
            { attr: 'data-search-priority-boost', label: featureLabels.searchPriorityBoost },
            { attr: 'data-featured-badge', label: featureLabels.featuredBadge }
        ];

        const activeFeatures = featureItems.filter(function (item) {
            return String(pkgOpt.getAttribute(item.attr) || '0') === '1';
        });

        if (activeFeatures.length > 0 && featuresEl) {
            featuresEl.innerHTML = activeFeatures.map(function (item) {
                return '<span class="p2p-promo-chip"><i class="fas fa-check-circle"></i>' + item.label + '</span>';
            }).join('');
            setVisible(featuresSectionEl, true);
        } else {
            if (featuresEl) {
                featuresEl.innerHTML = '';
            }
            setVisible(featuresSectionEl, false);
        }

        const rules = [];
        if (autoRenewAttr !== null) {
            rules.push(autoRenew ? textMap.autoRenewAllowed : textMap.autoRenewNotAllowed);
        }

        const categoryText = parseCategories(categories);
        if (categoryText !== '') {
            rules.push(textMap.categories + ': ' + categoryText);
        }

        if (rules.length > 0 && rulesEl) {
            rulesEl.textContent = rules.join(' | ');
            setVisible(rulesSectionEl, true);
        } else {
            if (rulesEl) {
                rulesEl.textContent = '';
            }
            setVisible(rulesSectionEl, false);
        }

        if (!walOpt || !walOpt.value) {
            setVisible(hintWrapEl, true);
            hintEl.classList.remove('text-danger');
            hintEl.classList.add('text-muted');
            hintEl.textContent = textMap.selectWalletForQuote;
            return;
        }

        setVisible(payItemEl, true);
        payEl.textContent = txtCalculating;
        setVisible(hintWrapEl, false);

        fetch(@json(route('user.p2p.offers.promotion.quote', $offer)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                package_id: pkgOpt.value,
                wallet_id: walOpt.value
            })
        })
        .then(function (resp) {
            return resp.json().then(function (data) {
                return { ok: resp.ok, status: resp.status, data: data };
            });
        })
        .then(function (res) {
            if (!res.ok || !res.data || !res.data.success) {
                const msg = (res.data && res.data.message) ? String(res.data.message) : txtFailed;
                setVisible(payItemEl, false);
                hintEl.classList.remove('text-muted');
                hintEl.classList.add('text-danger');
                hintEl.textContent = msg;
                setVisible(hintWrapEl, true);
                submitBtn.disabled = true;
                return;
            }

            const q = res.data.data;
            const paidAmount = formatAmount(String(q.paid_amount || '0'));
            payEl.textContent = paidAmount + ' ' + String(q.paid_currency);
            hintEl.classList.remove('text-danger');
            hintEl.classList.add('text-muted');
            hintEl.textContent = txtExchangeRate + ' ' + String(q.exchange_rate);
            setVisible(hintWrapEl, true);
            submitBtn.disabled = false;
        })
        .catch(function () {
            setVisible(payItemEl, false);
            hintEl.classList.remove('text-muted');
            hintEl.classList.add('text-danger');
            hintEl.textContent = txtFailed;
            setVisible(hintWrapEl, true);
            submitBtn.disabled = true;
        });
    };

    if (packageSelect) {
        packageSelect.addEventListener('change', updateSummaryFromOption);
    }
    if (walletSelect) {
        walletSelect.addEventListener('change', updateSummaryFromOption);
    }

    updateSummaryFromOption();
})();
</script>
