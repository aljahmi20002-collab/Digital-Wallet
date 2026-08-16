<script>
"use strict";
(function(){
    const modalEl = document.getElementById('p2pOrderModal');
    if (!modalEl) return;
    modalEl.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;
        const offerId = btn.getAttribute('data-offer-id');
        const side = btn.getAttribute('data-side');
        const min = btn.getAttribute('data-min');
        const max = btn.getAttribute('data-max');
        const price = btn.getAttribute('data-price');
        const currency = btn.getAttribute('data-currency');

        document.getElementById('p2p_offer_id').value = offerId;
        const amtInput = document.getElementById('p2p_amount');
        amtInput.min = min || 0;
        if (max) amtInput.max = max;
        document.getElementById('p2p_currency').textContent = currency;
        document.getElementById('p2p_minmax').textContent = `${min} - ${max || '∞'} ${currency}`;
        document.getElementById('p2p_info').textContent = `${side} @ ${price} ${currency}`;
    });
})();
</script>
