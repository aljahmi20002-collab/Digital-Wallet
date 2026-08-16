<script>
    "use strict";

    document.addEventListener('DOMContentLoaded', function () {
        const billingType = document.getElementById('pkg_billing_type');
        const fixedWrap = document.getElementById('pkg_fixed_price_wrap');
        const dailyWrap = document.getElementById('pkg_daily_price_wrap');
        const perTradeWrap = document.getElementById('pkg_per_trade_fee_wrap');

        const fixedInput = document.getElementById('pkg_price');
        const dailyInput = document.getElementById('pkg_daily_price');
        const perTradeInput = document.getElementById('pkg_per_trade_fee');

        const syncBilling = function () {
            const type = billingType ? billingType.value : 'FIXED';

            if (fixedWrap) fixedWrap.classList.toggle('d-none', type !== 'FIXED');
            if (dailyWrap) dailyWrap.classList.toggle('d-none', type !== 'DAILY_PRICE');
            if (perTradeWrap) perTradeWrap.classList.toggle('d-none', type !== 'PER_TRADE_FEE');

            if (fixedInput) fixedInput.required = type === 'FIXED';
            if (dailyInput) dailyInput.required = type === 'DAILY_PRICE';
            if (perTradeInput) perTradeInput.required = type === 'PER_TRADE_FEE';

            if (fixedInput) fixedInput.disabled = type !== 'FIXED';
            if (dailyInput) dailyInput.disabled = type !== 'DAILY_PRICE';
            if (perTradeInput) perTradeInput.disabled = type !== 'PER_TRADE_FEE';
        };

        if (billingType) {
            billingType.addEventListener('change', syncBilling);
        }

        syncBilling();
    });
</script>
