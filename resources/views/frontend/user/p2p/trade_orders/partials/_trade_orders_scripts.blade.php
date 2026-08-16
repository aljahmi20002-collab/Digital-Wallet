<script>
"use strict";
(function () {
    const banner = document.getElementById('p2pResumeBanner');
    const link = document.getElementById('p2pResumeLink');
    const orderText = document.getElementById('p2pResumeOrderText');
    const statusEl = document.getElementById('p2pResumeStatus');
    const updatedEl = document.getElementById('p2pResumeUpdated');
    const metaEl = document.getElementById('p2pResumeMeta');

    if (!banner || !link || !orderText || !metaEl) {
        return;
    }

    const activeStatuses = ['PENDING', 'PAID', 'DISPUTED'];
    const statusClassMap = {
        PENDING: 'is-pending',
        PAID: 'is-paid',
        DISPUTED: 'is-disputed'
    };
    const statusLabelMap = {
        PENDING: "{{ __('Pending') }}",
        PAID: "{{ __('Paid') }}",
        DISPUTED: "{{ __('Disputed') }}"
    };

    const formatNumber = function (value, maxDecimals) {
        const num = Number(value);
        if (!Number.isFinite(num)) {
            return '';
        }

        return num.toLocaleString(undefined, {
            maximumFractionDigits: maxDecimals
        });
    };

    const formatUpdated = function (timestamp) {
        const value = Number(timestamp);
        if (!Number.isFinite(value) || value <= 0) {
            return '';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '';
        }

        return "{{ __('Updated') }}: " + date.toLocaleString();
    };

    const appendMetaChip = function (iconClass, text) {
        if (!text) {
            return;
        }

        const chip = document.createElement('span');
        chip.className = 'p2p-resume-chip';

        const icon = document.createElement('i');
        icon.className = iconClass;
        icon.setAttribute('aria-hidden', 'true');

        const label = document.createElement('span');
        label.textContent = text;

        chip.appendChild(icon);
        chip.appendChild(label);
        metaEl.appendChild(chip);
    };

    const renderResumeBanner = function () {
        banner.classList.add('d-none');
        metaEl.innerHTML = '';

        if (statusEl) {
            statusEl.textContent = '';
            statusEl.classList.add('d-none');
            statusEl.classList.remove('is-pending', 'is-paid', 'is-disputed');
        }

        if (updatedEl) {
            updatedEl.textContent = '';
            updatedEl.classList.add('d-none');
        }

        try {
            const item = localStorage.getItem('p2p_last_order');
            if (!item) {
                return;
            }

            const data = JSON.parse(item);
            if (!data || !data.id || !data.url) {
                return;
            }

            const statusValue = String(data.status || '').toUpperCase();
            if (statusValue !== '' && activeStatuses.indexOf(statusValue) === -1) {
                return;
            }

            const updatedAt = Number(data.updated_at || 0);
            if (Number.isFinite(updatedAt) && updatedAt > 0 && (Date.now() - updatedAt) > (7 * 24 * 60 * 60 * 1000)) {
                return;
            }

            link.href = String(data.url);
            orderText.textContent = "{{ __('Order') }} #" + String(data.id);

            if (statusEl) {
                const statusLabel = data.status_label ? String(data.status_label) : (statusLabelMap[statusValue] || statusValue);
                if (statusLabel !== '') {
                    statusEl.textContent = statusLabel;
                    statusEl.classList.remove('d-none', 'is-pending', 'is-paid', 'is-disputed');
                    if (statusClassMap[statusValue]) {
                        statusEl.classList.add(statusClassMap[statusValue]);
                    }
                } else {
                    statusEl.classList.add('d-none');
                    statusEl.textContent = '';
                }
            }

            if (updatedEl) {
                const updatedText = formatUpdated(data.updated_at);
                updatedEl.textContent = updatedText;
                updatedEl.classList.toggle('d-none', updatedText === '');
            }

            const sideLabel = data.side_label ? String(data.side_label) : String(data.side_value || '').toUpperCase();
            appendMetaChip('fas fa-exchange-alt', sideLabel);

            const amountText = formatNumber(data.amount, 8);
            const currencyText = String(data.currency || '').toUpperCase();
            if (amountText !== '' && currencyText !== '') {
                appendMetaChip('fas fa-coins', amountText + ' ' + currencyText);
            }

            const rateText = formatNumber(data.price, 2);
            const fiatText = String(data.fiat_currency || '').toUpperCase();
            if (rateText !== '' && fiatText !== '' && currencyText !== '') {
                appendMetaChip('fas fa-chart-line', '1 ' + currencyText + ' ~ ' + rateText + ' ' + fiatText);
            }

            const paymentWindow = Number(data.payment_window_minutes || 0);
            if (Number.isFinite(paymentWindow) && paymentWindow > 0) {
                appendMetaChip('fas fa-hourglass-half', "{{ __('Release') }} ~ " + paymentWindow + " {{ __('min') }}");
            }

            banner.classList.remove('d-none');
        } catch (_) {}
    };

    renderResumeBanner();
    window.addEventListener('focus', renderResumeBanner);
    window.addEventListener('storage', function (event) {
        if (event.key === 'p2p_last_order') {
            renderResumeBanner();
        }
    });
})();

document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('p2pFeedbackModal');
    const form = document.getElementById('p2pFeedbackForm');
    const orderLabel = document.getElementById('p2pFeedbackOrderLabel');
    const ratingInput = document.getElementById('p2pFeedbackRating');
    const starsWrap = document.getElementById('p2pFeedbackStars');

    function renderStars(val) {
        if (!starsWrap) return;
        const v = Number(val || 0);
        starsWrap.querySelectorAll('.p2p-feedback-star').forEach(function (btn) {
            const n = Number(btn.getAttribute('data-value') || 0);
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.toggle('text-warning', n <= v);
                icon.classList.toggle('text-muted', n > v);
            }
        });
    }

    renderStars(Number(ratingInput ? ratingInput.value : 5));

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.p2p-rate-order-btn');
        if (!btn || !form) return;
        const url = btn.getAttribute('data-feedback-url') || '';
        const order = btn.getAttribute('data-order') || '';
        form.setAttribute('action', url);
        if (orderLabel) orderLabel.textContent = order;
        if (ratingInput) ratingInput.value = '5';
        renderStars(5);
    });

    if (starsWrap) {
        starsWrap.addEventListener('click', function (e) {
            const starBtn = e.target.closest('.p2p-feedback-star');
            if (!starBtn || !ratingInput) return;
            const v = Number(starBtn.getAttribute('data-value') || 0);
            ratingInput.value = String(v);
            renderStars(v);
        });
    }

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            if (form) form.setAttribute('action', '');
            if (orderLabel) orderLabel.textContent = '';
        });
    }

    try {
        const failedOrderId = @json((int) session('p2p_feedback_order_id'));
        if (failedOrderId && modalEl && form) {
            const template = @json(route('user.p2p.orders.feedback', 0));
            const url = template.replace(/\/0\/feedback$/, '/' + failedOrderId + '/feedback');
            form.setAttribute('action', url);
            if (orderLabel) orderLabel.textContent = '#' + failedOrderId;

            const oldRating = Number(@json(old('rating', 5)));
            if (ratingInput) ratingInput.value = String(oldRating || 5);
            renderStars(oldRating || 5);

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    } catch (_) {}
});
</script>
