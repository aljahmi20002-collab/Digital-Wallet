<script>
"use strict";
document.addEventListener('DOMContentLoaded', function () {
    try {
        const status = @json($order->status->value);
        const active = ['PENDING','PAID','DISPUTED'];
        const fiatCurrency = @json(siteCurrency('code') ?? $order->wallet->currency->code);
        const payload = {
            id: @json($order->id),
            url: @json(route('user.p2p.orders.show', $order)),
            status: status,
            status_label: @json($order->status->label()),
            side_value: @json($order->offer->side->value),
            side_label: @json($order->offer->side->label()),
            amount: @json((float) $order->amount),
            currency: @json($order->wallet->currency->code),
            price: @json((float) $order->price),
            fiat_currency: fiatCurrency,
            payment_window_minutes: @json((int) ($order->offer->payment_window_minutes ?? 0)),
            updated_at: Date.now()
        };
        if (active.includes(status)) {
            localStorage.setItem('p2p_last_order', JSON.stringify(payload));
        } else {
            localStorage.removeItem('p2p_last_order');
        }
    } catch (_) {}

    try {
        const url = new URL(window.location.href);
        if (url.searchParams.get('created') === '1') {
            const key = `p2p_info_shown_{{ $order->id }}`;
            if (!sessionStorage.getItem(key)) {
                const infoModal = new bootstrap.Modal(document.getElementById('p2pInfoModal'));
                infoModal.show();
                sessionStorage.setItem(key, '1');
            }
        }
    } catch (_) {}

    const $ = window.jQuery;
    const statusUrl = @json(route('user.p2p.orders.status', $order));
    const badge = document.getElementById('p2pStatusBadge');
    const actionPaid = document.getElementById('p2pActionPaid');
    const actionRelease = document.getElementById('p2pActionRelease');
    const actionCancel = document.getElementById('p2pActionCancel');

    function setBadge(payload) {
        if (!payload || !payload.display_badge_class || !payload.display_label) return;
        badge.className = payload.display_badge_class;
        badge.textContent = payload.display_label;
    }

    function setActions(flags) {
        if (actionPaid) actionPaid.classList.toggle('d-none', !flags.can_mark_paid);
        if (actionRelease) actionRelease.classList.toggle('d-none', !flags.can_release);
        if (actionCancel) actionCancel.classList.toggle('d-none', !flags.can_cancel);
    }

    function poll() {
        $.getJSON(statusUrl).done(function (res) {
            setBadge(res);
            setActions({
                can_mark_paid: !!res.can_mark_paid,
                can_release: !!res.can_release,
                can_cancel: !!res.can_cancel
            });
            if (res.display_status === 'PENDING' && res.expires_at) {
                const now = new Date(res.now).getTime();
                const exp = new Date(res.expires_at).getTime();
                const left = Math.max(0, Math.floor((exp - now) / 1000));
                const mm = String(Math.floor(left / 60)).padStart(2, '0');
                const ss = String(left % 60).padStart(2, '0');
                badge.title = `@lang('Expires in') ${mm}:${ss}`;
            } else {
                badge.removeAttribute('title');
            }
        }).always(function(){
            setTimeout(poll, 5000);
        });
    }
    if ($ && typeof $.getJSON === 'function') {
        poll();
    }

    (function () {
        const ratingInput = document.getElementById('p2pFeedbackRating');
        const starsWrap = document.getElementById('p2pFeedbackStars');
        if (!ratingInput || !starsWrap) return;

        function renderStars(val) {
            const v = Number(val || 0);
            starsWrap.querySelectorAll('.p2p-feedback-star').forEach(function (btn) {
                const n = Number(btn.getAttribute('data-value') || 0);
                const icon = btn.querySelector('i');
                if (!icon) return;
                icon.classList.toggle('text-warning', n <= v);
                icon.classList.toggle('text-muted', n > v);
            });
        }

        renderStars(Number(ratingInput.value || 5));

        starsWrap.addEventListener('click', function (e) {
            const starBtn = e.target.closest('.p2p-feedback-star');
            if (!starBtn) return;
            const v = Number(starBtn.getAttribute('data-value') || 0);
            ratingInput.value = String(v);
            renderStars(v);
        });
    })();

    try {
        const failedOrderId = @json((int) session('p2p_feedback_order_id'));
        if (failedOrderId && failedOrderId === @json((int) $order->id)) {
            const modalEl = document.getElementById('p2pFeedbackModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            }
        }
    } catch (_) {}
});
</script>
