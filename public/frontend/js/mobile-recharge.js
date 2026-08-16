/**
 * Mobile Recharge front-end behavior.
 *
 * Pulled out of the Blade template so the markup stays clean. The
 * blade exposes fee config and the default site currency through
 * data-attributes on the form root so this script stays declarative.
 */
(function () {
    'use strict';

    const root = document.querySelector('[data-mobile-recharge-form]');

    if (!root) {
        return;
    }

    const feeFixed = parseFloat(root.dataset.feeFixed || '0') || 0;
    const feePercent = parseFloat(root.dataset.feePercent || '0') || 0;
    const fallbackCurrency = root.dataset.defaultCurrency || '';

    const walletSelect = root.querySelector('.mobile-recharge-wallet');
    const amountInput = root.querySelector('.mobile-recharge-amount');
    const currencyLabel = root.querySelector('.mobile-recharge-currency');

    const fields = {
        balance: document.querySelector('.mobile-recharge-summary-balance'),
        amount: document.querySelector('.mobile-recharge-summary-amount'),
        fee: document.querySelector('.mobile-recharge-summary-fee'),
        total: document.querySelector('.mobile-recharge-summary-total'),
    };

    function selectedWallet() {
        if (!walletSelect || walletSelect.selectedOptions.length === 0) {
            return null;
        }
        return walletSelect.selectedOptions[0];
    }

    function formatMoney(value, currency) {
        const numeric = Number(value || 0);
        const cleanCurrency = (currency || '').trim();
        return cleanCurrency
            ? `${numeric.toFixed(2)} ${cleanCurrency}`
            : numeric.toFixed(2);
    }

    function refreshSummary() {
        const wallet = selectedWallet();
        const currency = wallet ? wallet.dataset.currency : fallbackCurrency;
        const balance = wallet ? Number(wallet.dataset.balance || 0) : 0;
        const amount = amountInput ? Number(amountInput.value || 0) : 0;
        const fee = amount > 0 ? feeFixed + (feePercent / 100) * amount : 0;
        const total = amount + fee;

        if (currencyLabel) {
            currencyLabel.textContent = currency;
        }

        if (fields.balance) {
            fields.balance.textContent = wallet ? formatMoney(balance, currency) : '-';
        }
        if (fields.amount) {
            fields.amount.textContent = amount > 0 ? formatMoney(amount, currency) : '-';
        }
        if (fields.fee) {
            fields.fee.textContent = amount > 0 ? formatMoney(fee, currency) : '-';
        }
        if (fields.total) {
            fields.total.textContent = amount > 0 ? formatMoney(total, currency) : '-';
        }
    }

    if (walletSelect) {
        walletSelect.addEventListener('change', refreshSummary);
    }

    if (amountInput) {
        amountInput.addEventListener('input', refreshSummary);
    }

    refreshSummary();
})();
