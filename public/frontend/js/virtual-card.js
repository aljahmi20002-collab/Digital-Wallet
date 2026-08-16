/**
 * DigiKash · Virtual Cards page (provider-agnostic)
 *
 * Uses data-* attributes on each .vc-mini button as the source of truth for
 * a card's provider, capabilities, controls and limits — no JS knows about
 * any specific gateway. Adding a new provider is a backend-only change.
 */
(function () {
    "use strict";

    if (!document.querySelector('[data-vc-page]')) return;

    const $page = $('[data-vc-page]');
    let activeBtn = $page.find('[data-vc-card-id]').first();
    if (!activeBtn.length) return;

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function fmtMoney(symbol, amount) {
        const num = Number(amount) || 0;
        return (symbol || '$') + num.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function getActiveData() {
        return {
            id:              activeBtn.data('vc-card-id'),
            provider:        activeBtn.data('vc-provider'),
            providerName:    activeBtn.data('vc-provider-name'),
            providerLabel:   activeBtn.data('vc-provider-label'),
            providerColor:   activeBtn.data('vc-provider-color'),
            providerCardId:  activeBtn.data('vc-provider-card-id'),
            label:           activeBtn.data('vc-label'),
            cardholderName:  activeBtn.data('vc-cardholder-name'),
            last4:           activeBtn.data('vc-last4'),
            brand:           activeBtn.data('vc-brand'),
            network:         activeBtn.data('vc-network'),
            exp:             activeBtn.data('vc-exp'),
            status:          activeBtn.data('vc-status'),
            theme:           activeBtn.data('vc-theme'),
            currency:        activeBtn.data('vc-currency'),
            currencySymbol:  activeBtn.data('vc-currency-symbol') || '$',
            walletBalance:   Number(activeBtn.data('vc-wallet-balance')) || 0,
            cardBalance:     Number(activeBtn.data('vc-card-balance')) || 0,
            topupUrl:        activeBtn.data('vc-topup-url'),
            withdrawUrl:     activeBtn.data('vc-withdraw-url'),
            freezeUrl:       activeBtn.data('vc-freeze-url'),
            unfreezeUrl:     activeBtn.data('vc-unfreeze-url'),
            limitsUrl:       activeBtn.data('vc-limits-url'),
            controlsUrl:     activeBtn.data('vc-controls-url'),
            detailsUrl:      activeBtn.data('vc-details-url'),
            caps:            activeBtn.data('vc-caps') || {},
            controls:        activeBtn.data('vc-controls') || {},
            limits:          activeBtn.data('vc-limits') || {},
        };
    }

    /* ------------------------------------------------------------------
     *  Card hero render
     *  Provider/network agnostic: any new gateway whose `network` value
     *  isn't recognised here falls back to the brand text — no UI break.
     * ------------------------------------------------------------------ */
    function networkLogoHtml(brand) {
        const key = String(brand || '').toLowerCase().replace(/[\s_-]/g, '');
        switch (key) {
            case 'mastercard':
            case 'mc':
                return '<svg viewBox="0 0 36 22" width="36" height="22" aria-label="Mastercard"><circle cx="13" cy="11" r="9" fill="#EB001B"/><circle cx="23" cy="11" r="9" fill="#F79E1B" opacity="0.92"/></svg>';
            case 'visa':
                return '<svg viewBox="0 0 60 20" width="48" height="16" aria-label="Visa"><text x="0" y="16" font-family="Inter" font-weight="800" font-size="18" font-style="italic" fill="#fff" letter-spacing="-0.5">VISA</text></svg>';
            case 'amex':
            case 'americanexpress':
                return '<svg viewBox="0 0 60 22" width="50" height="18" aria-label="American Express"><rect x="0" y="0" width="60" height="22" rx="3" fill="#2E77BC"/><text x="30" y="15" text-anchor="middle" font-family="Inter" font-weight="800" font-size="9" fill="#fff" letter-spacing="0.6">AMEX</text></svg>';
            case 'discover':
                return '<svg viewBox="0 0 70 18" width="56" height="16" aria-label="Discover"><text x="0" y="14" font-family="Inter" font-weight="800" font-size="13" fill="#fff" letter-spacing="-0.3">DISC</text><circle cx="50" cy="9" r="6" fill="#FF6000"/></svg>';
            case 'jcb':
                return '<svg viewBox="0 0 40 18" width="36" height="16" aria-label="JCB"><text x="0" y="14" font-family="Inter" font-weight="800" font-size="13" fill="#fff" letter-spacing="0.4">JCB</text></svg>';
            case 'unionpay':
                return '<svg viewBox="0 0 70 18" width="58" height="16" aria-label="UnionPay"><text x="0" y="14" font-family="Inter" font-weight="800" font-size="11" fill="#fff" letter-spacing="0.2">UNIONPAY</text></svg>';
            case 'rupay':
                return '<svg viewBox="0 0 60 18" width="48" height="16" aria-label="RuPay"><text x="0" y="14" font-family="Inter" font-weight="800" font-size="13" fill="#fff" letter-spacing="-0.2">RuPay</text></svg>';
            case '':
                return '';
            default:
                return '<span class="vcard__network">' + escapeHtml(brand) + '</span>';
        }
    }

    function arcsSvg(uniqueId) {
        const id = 'arc-' + uniqueId;
        return (
            '<svg class="vcard__arcs" viewBox="0 0 380 230" preserveAspectRatio="none">' +
                '<defs>' +
                    '<linearGradient id="' + id + '" x1="0" x2="1">' +
                        '<stop offset="0" stop-color="#fff" stop-opacity="0"/>' +
                        '<stop offset="0.5" stop-color="#fff" stop-opacity="0.6"/>' +
                        '<stop offset="1" stop-color="#fff" stop-opacity="0"/>' +
                    '</linearGradient>' +
                '</defs>' +
                '<path d="M -40 200 Q 190 60 420 200" stroke="url(#' + id + ')" stroke-width="1" fill="none"/>' +
                '<path d="M -40 220 Q 190 80 420 220" stroke="url(#' + id + ')" stroke-width="1" fill="none"/>' +
                '<path d="M -40 240 Q 190 100 420 240" stroke="url(#' + id + ')" stroke-width="1" fill="none"/>' +
            '</svg>'
        );
    }

    function buildCardHtml(d) {
        const isFrozen = (d.status || 'active') !== 'active';
        const themeAttr = escapeHtml(d.theme || 'midnight');
        const frozenPill = isFrozen
            ? '<span class="vc-pill vcard__frozen-pill"><span class="vc-pill__dot"></span>' +
                escapeHtml(String(d.status).toUpperCase()) +
              '</span>'
            : '';
        // Per-provider brand color is genuinely data-driven (an arbitrary admin-set
        // hex), so it is delivered via a single CSS custom property. CSS owns the
        // visual rules; JS only forwards the value through the variable.
        const tintStyle = d.providerColor
            ? ' style="--vc-brand-tint: ' + escapeHtml(d.providerColor) + ';"'
            : '';
        const tintClass = d.providerColor ? ' has-brand-tint' : '';
        const providerPill = d.providerLabel
            ? '<span class="vcard__provider-pill">' + escapeHtml(d.providerLabel) + '</span>'
            : '';

        // FRONT face — the existing card layout
        const frontFace =
            '<div class="vcard vcard--front' + tintClass + ' ' + (isFrozen ? 'vcard--frozen' : '') + '" data-theme="' + themeAttr + '"' + tintStyle + '>' +
                arcsSvg(d.id || 'card') +
                frozenPill +
                '<div class="vcard__top">' +
                    '<div>' +
                        '<div class="vcard__brand">' +
                            '<span class="vcard__brand-mark"></span>' +
                            '<span>Digikash</span>' +
                            providerPill +
                        '</div>' +
                        '<div class="vcard__label">VIRTUAL · ' + escapeHtml(d.label || '') + '</div>' +
                    '</div>' +
                    '<div>' + networkLogoHtml(d.brand || d.network) + '</div>' +
                '</div>' +
                '<div class="vcard__middle">' +
                    '<div class="row-icons">' +
                        '<span class="vcard__chip"></span>' +
                        '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="rgba(255,255,255,0.85)" stroke-width="2" stroke-linecap="round"><path d="M5 12c2-2 4-3 7-3s5 1 7 3"/><path d="M8 15c1-1 2-1.5 4-1.5s3 0.5 4 1.5"/><circle cx="12" cy="18" r="1" fill="rgba(255,255,255,0.85)"/></svg>' +
                    '</div>' +
                    '<div class="vcard__number">•••• •••• •••• ' + escapeHtml(d.last4 || '••••') + '</div>' +
                '</div>' +
                '<div class="vcard__bottom">' +
                    '<div>' +
                        '<div class="vcard__meta-label">Cardholder</div>' +
                        '<div class="vcard__meta-value">' + escapeHtml(String(d.cardholderName || d.label || '').toUpperCase()) + '</div>' +
                    '</div>' +
                    '<div class="vcard__bottom-right">' +
                        '<div class="vcard__meta-label">Valid Thru</div>' +
                        '<div class="vcard__meta-value mono">' + escapeHtml(d.exp || '—') + '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        // BACK face — magnetic stripe + signature panel + sensitive fields.
        // Filled by `populateCardBack(card)` after AJAX, until then it shows
        // masked placeholders so the user gets immediate visual feedback.
        const backFace =
            '<div class="vcard vcard--back' + tintClass + '" data-theme="' + themeAttr + '"' + tintStyle + '>' +
                '<div class="vcard__magstripe"></div>' +
                '<div class="vcard__back-body">' +
                    '<div class="vcard__back-row">' +
                        '<div class="vcard__back-pan mono" data-vc-back-pan>' + escapeHtml((d.fullPan ? d.fullPan : '•••• •••• •••• ' + (d.last4 || '••••'))) + '</div>' +
                        '<div class="vcard__back-cvv mono" data-vc-back-cvv>' + escapeHtml(d.cvv || '•••') + '</div>' +
                    '</div>' +
                    '<div class="vcard__back-label">CVV · CARD VERIFICATION VALUE</div>' +
                    '<div class="vcard__back-fineprint">For authorized use only. Issued by Digikash Wallet Solution. Visit digikash.io/support for assistance.</div>' +
                '</div>' +
            '</div>';

        return (
            '<div class="vcard-flip" data-vc-flip>' +
                '<div class="vcard-flip__inner">' +
                    frontFace +
                    backFace +
                '</div>' +
            '</div>'
        );
    }

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
        });
    }

    function refreshHero() {
        const d = getActiveData();

        $('[data-vc-hero-card]').html(buildCardHtml(d));
        // Hero title row: show cardholder name (richer than wallet code).
        // Falls back to label so legacy data with no cardholder still reads OK.
        $('[data-vc-hero-label]').text(d.cardholderName || d.label || '—');
        $('[data-vc-hero-status-label]').text((d.status || 'active').toUpperCase());

        const $statusPill = $('[data-vc-hero-status]');
        $statusPill.removeClass('vc-pill--green vc-pill--blue vc-pill--amber vc-pill--red');
        if (d.status === 'active')        $statusPill.addClass('vc-pill--green');
        else if (d.status === 'inactive') $statusPill.addClass('vc-pill--blue');
        else if (d.status === 'pending')  $statusPill.addClass('vc-pill--amber');
        else                              $statusPill.addClass('vc-pill--red');

        $('[data-vc-hero-provider]').text(d.providerName || d.provider || '—');
        $('[data-vc-hero-balance]').text(fmtMoney(d.currencySymbol, d.walletBalance));

        // "Monthly spend" line: card balance is what's been topped on this
        // card in the current period. Wallet balance is the cap. Showing
        // "$spent / $limit" matches the reference UI.
        $('[data-vc-hero-card-balance]').text(fmtMoney(d.currencySymbol, d.cardBalance) + ' / ' + fmtMoney(d.currencySymbol, d.walletBalance));

        const cap = d.walletBalance > 0 ? Math.min(100, (d.cardBalance / d.walletBalance) * 100) : 0;
        $('[data-vc-hero-progress]').attr('data-front-progress-pct', String(Math.round(cap)));

        $('[data-vc-hero-pan]').text('•••• •••• •••• ' + (d.last4 || '••••'));
        $('[data-vc-hero-exp]').text((d.exp || '—') + ' · •••');

        const hardCaps = {
            topup: true,
            withdraw: true,
        };
        const caps = d.caps || {};
        $page.find('[data-cap]').each(function () {
            const cap = $(this).data('cap');
            const supported = !!caps[cap];

            if (hardCaps[cap]) {
                $(this)
                    .toggleClass('d-none', !supported)
                    .attr('aria-disabled', supported ? 'false' : 'true')
                    .attr('title', supported ? '' : 'Provider API does not support this action');

                return;
            }

            $(this).removeClass('d-none').attr(
                'title',
                supported ? '' : 'Soft mode — provider has no gateway API for this'
            );
        });

        // Freeze toggle label flips based on current status
        const isFrozen = d.status !== 'active';
        $('[data-vc-freeze-label]').text(isFrozen ? 'Unfreeze' : 'Freeze');
        $('[data-vc-freeze-label-secondary]').text(isFrozen ? 'Unfreeze card' : 'Freeze card');

        // Provider feature summary
        renderCapList(caps);

        // Controls panel
        renderControls(d);
    }

    function renderCapList(caps) {
        const labels = {
            issue:        ['Issue cards', 'fa-credit-card'],
            card_details: ['Reveal card details', 'fa-eye'],
            topup:        ['Top up', 'fa-arrow-down'],
            withdraw:     ['Withdraw', 'fa-arrow-up'],
            freeze:       ['Freeze / Unfreeze', 'fa-snowflake'],
            limits:       ['Spend limits', 'fa-clock'],
            controls:     ['Card controls', 'fa-toggle-on'],
        };

        let html = '';
        Object.keys(labels).forEach(function (key) {
            const enabled = !!caps[key];
            const [label, icon] = labels[key];
            html += '<div class="vc-control-row vc-control-row--compact ' + (enabled ? 'is-on' : '') + '">' +
                '<div class="vc-control-row__icon"><i class="fa-solid ' + icon + '"></i></div>' +
                '<div class="vc-control-row__copy">' +
                    '<div class="vc-control-row__title">' + label + '</div>' +
                    '<div class="vc-control-row__sub">' + (enabled ? 'Available on this provider' : 'Not exposed by this provider') + '</div>' +
                '</div>' +
                '<span class="vc-pill ' + (enabled ? 'vc-pill--green' : '') + '">' +
                    '<span class="vc-pill__dot"></span>' + (enabled ? 'Allowed' : 'Off') +
                '</span>' +
            '</div>';
        });

        $('[data-vc-cap-list]').html(html);
        const d = getActiveData();
        $('[data-vc-provider-summary]').text((d.providerName || d.provider || '—') + ' · feature support for the selected card');
    }

    function renderControls(d) {
        const $panel = $('[data-vc-controls-panel]');
        const supportsControls = !!(d.caps && d.caps.controls);

        $panel.removeClass('d-none'); // always show; if not supported, mark disabled
        $('[data-vc-controls-target]').text('•••• ' + d.last4);

        const c = d.controls || {};
        ['online', 'atm', 'intl', 'contactless'].forEach(function (key) {
            const $row = $panel.find('[data-vc-control="' + key + '"]');
            const $tog = $row.find('[data-vc-toggle="' + key + '"]');
            const on = !!c[key];
            $tog.toggleClass('is-on', on);
            $row.toggleClass('is-on', on);
            $tog.prop('disabled', false);
        });

        $('[data-vc-controls-state]').text(supportsControls ? 'Saved' : 'Soft mode');
    }

    /* ------------------------------------------------------------------
     *  Switcher click
     * ------------------------------------------------------------------ */
    $page.on('click', '[data-vc-card-id]', function () {
        $page.find('[data-vc-card-id]').removeClass('is-active');
        activeBtn = $(this).addClass('is-active');
        refreshHero();
    });

    /* ------------------------------------------------------------------
     *  Reveal card details — flips the card to its back face and pulls
     *  the sensitive PAN/CVV via the existing card-details endpoint.
     *  The flipped state is held on the .vcard-flip wrapper so the
     *  same click toggles back to the front view.
     * ------------------------------------------------------------------ */
    function flipActiveCard(toFlipped) {
        const $flip = $('[data-vc-hero-card] [data-vc-flip]');
        if (!$flip.length) return;
        const isFlipped = $flip.hasClass('is-flipped');
        const target = (typeof toFlipped === 'boolean') ? toFlipped : !isFlipped;
        $flip.toggleClass('is-flipped', target);
    }

    function fillBackFace(payload) {
        const $flip = $('[data-vc-hero-card] [data-vc-flip]');
        if (!$flip.length) return;
        const pan = payload.pan || payload.number || payload.card_number;
        const cvv = payload.cvv || payload.cvc || payload.security_code;
        if (pan)  $flip.find('[data-vc-back-pan]').text(formatPan(String(pan)));
        if (cvv)  $flip.find('[data-vc-back-cvv]').text(String(cvv));

        // If the provider tells us this is a sandbox/test-mode card, badge
        // the front of the card and surface a one-shot toast so the user
        // doesn't try to use it at a real merchant.
        const isTest = !!(payload.test_mode || payload.is_test || payload.testmode);
        $('[data-vc-hero-card] .vcard--front').each(function () {
            $(this).find('.vcard__test-badge').remove();
            if (isTest) {
                $(this).prepend('<span class="vcard__test-badge" title="Stripe test-mode card — works only in Stripe\'s test environment.">TEST</span>');
            }
        });
        if (isTest && payload.notice && window.notifyEvs && !$flip.data('vcTestNoticeShown')) {
            $flip.data('vcTestNoticeShown', true);
            window.notifyEvs('warning', payload.notice);
        }
    }

    function formatPan(pan) {
        const digits = String(pan).replace(/\D+/g, '');
        if (digits.length < 8) return pan;
        return digits.replace(/(\d{4})/g, '$1 ').trim();
    }

    $page.on('click', '[data-vc-action="reveal"]', function (e) {
        e.preventDefault();
        const d = getActiveData();
        const $flip = $('[data-vc-hero-card] [data-vc-flip]');

        // Toggle: if already flipped, just flip back without re-fetching.
        if ($flip.hasClass('is-flipped')) {
            flipActiveCard(false);
            return;
        }

        // Optimistic flip — user gets immediate visual feedback while we
        // fetch the sensitive details. The back face shows masked dots
        // until the AJAX completes.
        flipActiveCard(true);

        if (!d.detailsUrl) return;

        $.ajax({
            url: d.detailsUrl,
            method: 'GET',
            dataType: 'json',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .done(function (resp) {
                const payload = (resp && resp.data) || resp || {};
                fillBackFace(payload);
            })
            .fail(function (xhr) {
                if (window.notifyEvs) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.error)
                        || 'Could not load sensitive details — try again.';
                    window.notifyEvs('error', msg);
                }
            });
    });

    $page.on('click', '[data-vc-action="reveal-demo"]', function (e) {
        e.preventDefault();
        flipActiveCard();
    });

    // Clicking the card visual itself toggles the flip — same UX as the
    // reference HTML. Stop propagation so the click doesn't bubble.
    $page.on('click', '[data-vc-flip]', function (e) {
        // Ignore clicks on inner buttons/links (they have their own handlers)
        if ($(e.target).closest('button, a, input, select').length) return;
        e.preventDefault();
        $(this).toggleClass('is-flipped');
    });

    /* ------------------------------------------------------------------
     *  Copy card number
     *  ----------------------------------------------------------------
     *  navigator.clipboard.writeText only works on HTTPS / localhost.
     *  We try it first, then fall back to a hidden textarea + execCommand
     *  so the button works on plain http://digikash.test too. Either way
     *  the user gets a visible toast confirming the copy.
     * ------------------------------------------------------------------ */
    function copyTextToClipboard(text) {
        // Prefer the modern API where it's available.
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        // Legacy fallback — works in non-secure contexts.
        return new Promise(function (resolve, reject) {
            try {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.setAttribute('readonly', '');
                ta.style.position = 'fixed';
                ta.style.top = '0';
                ta.style.left = '0';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                ta.setSelectionRange(0, text.length);
                const ok = document.execCommand('copy');
                document.body.removeChild(ta);
                ok ? resolve() : reject(new Error('execCommand returned false'));
            } catch (e) {
                reject(e);
            }
        });
    }

    /**
     * Resolve the real (un-masked) card number for copy/clipboard. We
     * never want to copy the dot-mask shown on the front of the card.
     *
     * 1. If the flip back is already populated with a real PAN, reuse it.
     * 2. Otherwise hit the same `card-details` endpoint Reveal uses.
     * 3. Last-ditch fallback: copy the masked PAN and warn the user so
     *    they at least get something + a clear message.
     */
    function resolveRealPan() {
        const $back = $('[data-vc-hero-card] [data-vc-back-pan]');
        const cached = ($back.text() || '').replace(/[^0-9 ]/g, '').trim();
        if (cached && /\d{8,}/.test(cached.replace(/\s+/g, ''))) {
            return Promise.resolve(cached);
        }

        const d = getActiveData();
        if (!d.detailsUrl) return Promise.reject(new Error('No details endpoint configured for this card.'));

        return $.ajax({
            url: d.detailsUrl,
            method: 'GET',
            dataType: 'json',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(function (resp) {
            const payload = (resp && resp.data) || resp || {};
            const pan = payload.pan || payload.number || payload.card_number;
            if (!pan) {
                return Promise.reject(new Error('Provider did not return a card number.'));
            }
            // Cache it onto the back face so a follow-up click is free.
            $back.text(formatPan(String(pan)));
            return formatPan(String(pan));
        });
    }

    $page.on('click', '[data-vc-action="copy"]', function () {
        const $btn = $(this);
        if ($btn.prop('disabled')) return;
        $btn.prop('disabled', true);
        const reEnable = function () { $btn.prop('disabled', false); };

        Promise.resolve(resolveRealPan())
            .then(function (pan) {
                return copyTextToClipboard(pan).then(function () {
                    if (window.notifyEvs) {
                        window.notifyEvs('success', 'Card number copied to clipboard.');
                    }
                });
            })
            .catch(function (err) {
                // Last-ditch fallback so the click is never a complete no-op.
                const masked = ($('[data-vc-hero-pan]').text() || '').trim();
                if (masked) {
                    copyTextToClipboard(masked).catch(function () {});
                }
                if (window.notifyEvs) {
                    const msg = (err && err.message) || 'Could not load card number — please flip the card and copy manually.';
                    window.notifyEvs('error', msg);
                }
            })
            .then(reEnable, reEnable);
    });

    /* ------------------------------------------------------------------
     *  Modal helpers
     * ------------------------------------------------------------------ */
    function fillModalCardLabels(d) {
        $('.vc-modal [data-vc-modal-card-last]').text('•••• ' + d.last4);
        $('.vc-modal [data-vc-modal-card-id]').val(d.id);
        $('.vc-modal [data-vc-modal-symbol]').text(d.currencySymbol);
        $('.vc-modal [data-vc-modal-currency]').text(d.currency || '');
        $('.vc-modal [data-vc-modal-error]').addClass('d-none').text('');
    }

    /* ------------------------------------------------------------------
     *  Top up / Withdraw modals
     * ------------------------------------------------------------------ */
    function bindAmountSummary(modalSelector) {
        const $modal = $(modalSelector);
        const $amount = $modal.find('input[name="amount"]');
        const $sumAmt = $modal.find('[data-vc-modal-amount]');
        const $sumFee = $modal.find('[data-vc-modal-fee]');
        const $sumTot = $modal.find('[data-vc-modal-total]');

        function recalc() {
            const d = getActiveData();
            const amt = parseFloat($amount.val()) || 0;
            // Fee unknown without backend lookup; keep zero — server enforces real fee.
            const fee = 0;
            const total = amt + fee;
            $sumAmt.text(amt > 0 ? fmtMoney(d.currencySymbol, amt) : '—');
            $sumFee.text(amt > 0 ? fmtMoney(d.currencySymbol, fee) : '—');
            $sumTot.text(amt > 0 ? fmtMoney(d.currencySymbol, total) : '—');
        }
        $amount.off('input.vcsum').on('input.vcsum', recalc);
        recalc();
    }

    $page.on('click', '[data-vc-action="topup"]', function () {
        const d = getActiveData();
        if (!d.caps.topup) {
            if (window.notifyEvs) {
                window.notifyEvs('error', (d.providerName || 'This provider') + ' does not support top-up via API.');
            }
            return;
        }
        fillModalCardLabels(d);
        $('#vcTopupModal [data-vc-wallet-balance-text]').text(fmtMoney(d.currencySymbol, d.walletBalance));
        bindAmountSummary('#vcTopupModal');
        new bootstrap.Modal(document.getElementById('vcTopupModal')).show();
    });

    $page.on('click', '[data-vc-action="withdraw"]', function () {
        const d = getActiveData();
        if (!d.caps.withdraw) {
            if (window.notifyEvs) {
                window.notifyEvs('error', (d.providerName || 'This provider') + ' does not support withdrawal via API.');
            }
            return;
        }
        fillModalCardLabels(d);
        $('#vcWithdrawModal [data-vc-card-balance-text]').text(fmtMoney(d.currencySymbol, d.cardBalance));
        bindAmountSummary('#vcWithdrawModal');
        new bootstrap.Modal(document.getElementById('vcWithdrawModal')).show();
    });

    /* ------------------------------------------------------------------
     *  Freeze / Unfreeze
     * ------------------------------------------------------------------ */
    $page.on('click', '[data-vc-action="freeze"]', function () {
        const d = getActiveData();
        const isFrozen = d.status !== 'active';
        $('#vcFreezeModal [data-vc-freeze-mode-label]').text(isFrozen ? 'Unfreeze card' : 'Freeze card');
        $('#vcFreezeModal [data-vc-freeze-title]').text(isFrozen ? 'Unfreeze card' : 'Freeze card');
        $('#vcFreezeModal [data-vc-freeze-confirm-label]').text(isFrozen ? 'Unfreeze' : 'Freeze');
        $('#vcFreezeModal [data-vc-freeze-subtitle]').text(isFrozen
            ? 'Re-enable transactions on this card. Recurring billers can resume.'
            : 'All authorizations will decline immediately. Recurring subscriptions may fail until unfrozen.');

        // Show soft-mode notice if provider lacks gateway-level freeze
        $('#vcFreezeModal [data-vc-freeze-soft-notice]').toggleClass('d-none', Boolean(d.caps.freeze));

        fillModalCardLabels(d);
        new bootstrap.Modal(document.getElementById('vcFreezeModal')).show();
    });

    $('#vcFreezeModal').on('click', '[data-vc-freeze-confirm]', function () {
        const d = getActiveData();
        const isFrozen = d.status !== 'active';
        const url = isFrozen ? d.unfreezeUrl : d.freezeUrl;
        const $btn = $(this);
        const $err = $('#vcFreezeModal [data-vc-modal-error]');
        $err.addClass('d-none').text('');
        $btn.prop('disabled', true);

        $.ajax({
            url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            dataType: 'json'
        })
            .done(function (resp) {
                bootstrap.Modal.getInstance(document.getElementById('vcFreezeModal')).hide();
                location.reload();
            })
            .fail(function (xhr) {
                $err.removeClass('d-none').text((xhr.responseJSON && xhr.responseJSON.error) || 'Failed to update card.');
                $btn.prop('disabled', false);
            });
    });

    /* ------------------------------------------------------------------
     *  Limits modal
     * ------------------------------------------------------------------ */
    $page.on('click', '[data-vc-action="limits"]', function () {
        const d = getActiveData();
        if (!d.caps.limits) {
            // Allow opening the modal in soft mode — values still get saved
        }
        fillModalCardLabels(d);

        const limits = d.limits || {};
        const $form = $('#vcLimitsModal [data-vc-limits-form]');
        $form.find('[name="per_transaction"]').val(limits.per_transaction || '');
        $form.find('[name="daily"]').val(limits.daily || '');
        $form.find('[name="monthly"]').val(limits.monthly || '');
        $form.find('[name="alert_at"]').val(limits.alert_at || 80);
        $('#vcLimitsModal [data-vc-alert-display]').text((limits.alert_at || 80) + '%');
        const auto = limits.auto_freeze !== false;
        $form.find('[name="auto_freeze"]').val(auto ? '1' : '0');
        $form.find('[data-vc-toggle="auto_freeze"]').toggleClass('is-on', auto);

        new bootstrap.Modal(document.getElementById('vcLimitsModal')).show();
    });

    $('#vcLimitsModal').on('input', '[data-vc-alert-input]', function () {
        $('#vcLimitsModal [data-vc-alert-display]').text(this.value + '%');
    });

    $('#vcLimitsModal').on('click', '[data-vc-toggle="auto_freeze"]', function () {
        const $t = $(this);
        const on = !$t.hasClass('is-on');
        $t.toggleClass('is-on', on);
        $t.closest('form').find('[name="auto_freeze"]').val(on ? '1' : '0');
    });

    $('#vcLimitsModal').on('submit', '[data-vc-limits-form]', function (e) {
        e.preventDefault();
        const d = getActiveData();
        const $err = $('#vcLimitsModal [data-vc-modal-error]');
        $err.addClass('d-none').text('');

        const $form = $(this);
        const payload = {
            _token:          csrf,
            per_transaction: $form.find('[name="per_transaction"]').val() || null,
            daily:           $form.find('[name="daily"]').val() || null,
            monthly:         $form.find('[name="monthly"]').val() || null,
            alert_at:        $form.find('[name="alert_at"]').val(),
            auto_freeze:     $form.find('[name="auto_freeze"]').val(),
        };

        $.post({ url: d.limitsUrl, data: payload, dataType: 'json' })
            .done(function () {
                activeBtn.data('vc-limits', payload);
                bootstrap.Modal.getInstance(document.getElementById('vcLimitsModal')).hide();
            })
            .fail(function (xhr) {
                $err.removeClass('d-none').text((xhr.responseJSON && xhr.responseJSON.error) || 'Failed to save limits.');
            });
    });

    /* ------------------------------------------------------------------
     *  Controls panel toggles
     * ------------------------------------------------------------------ */
    let controlsTimer = null;
    $page.on('click', '[data-vc-toggle]', function (e) {
        if (this.closest('#vcLimitsModal')) return; // limits-modal toggle handled separately
        const $t = $(this);
        const key = $t.data('vc-toggle');
        const d = getActiveData();
        if (!d.caps.controls) {
            // Soft mode: still let users toggle and persist (will simply be advisory)
        }
        const on = !$t.hasClass('is-on');
        $t.toggleClass('is-on', on);
        $t.closest('.vc-control-row').toggleClass('is-on', on);

        const newControls = Object.assign({}, d.controls, { [key]: on });
        activeBtn.data('vc-controls', newControls);

        clearTimeout(controlsTimer);
        controlsTimer = setTimeout(function () {
            $('[data-vc-controls-state]').text('Saving…');
            $.post({
                url: d.controlsUrl,
                data: {
                    _token:      csrf,
                    online:      newControls.online      ? 1 : 0,
                    atm:         newControls.atm         ? 1 : 0,
                    intl:        newControls.intl        ? 1 : 0,
                    contactless: newControls.contactless ? 1 : 0,
                },
                dataType: 'json',
            })
                .done(function () {
                    $('[data-vc-controls-state]').text(d.caps.controls ? 'Saved' : 'Soft mode');
                })
                .fail(function () {
                    $('[data-vc-controls-state]').text('Save failed');
                });
        }, 250);
    });

    /* ------------------------------------------------------------------
     *  Topup amount preset chips
     * ------------------------------------------------------------------ */
    $('#vcTopupModal').on('click', '[data-vc-presets] [data-amount]', function () {
        const amt = $(this).data('amount');
        const $input = $('#vcTopupModal input[name="amount"]');
        $input.val(amt).trigger('input');
    });

    /* ------------------------------------------------------------------
     *  Topup funding source picker (visual; only wallet is functional)
     * ------------------------------------------------------------------ */
    $('#vcTopupModal').on('click', '.vc-option:not([disabled])', function () {
        $('#vcTopupModal .vc-option').removeClass('is-active');
        $(this).addClass('is-active');
    });

    /* ------------------------------------------------------------------
     *  Freeze modal — reason + duration capture
     * ------------------------------------------------------------------ */
    let freezeReason   = 'lost';
    let freezeDuration = 'indefinite';

    $('#vcFreezeModal').on('click', '[data-vc-reason]', function () {
        $('#vcFreezeModal [data-vc-reason]').removeClass('is-active');
        $(this).addClass('is-active');
        freezeReason = $(this).data('vc-reason');
    });
    $('#vcFreezeModal').on('click', '[data-vc-duration]', function () {
        $('#vcFreezeModal [data-vc-duration]').removeClass('is-active');
        $(this).addClass('is-active');
        freezeDuration = $(this).data('vc-duration');
    });

    // Override: when freezing, send reason+duration; when unfreezing, send empty body
    $('#vcFreezeModal').off('click', '[data-vc-freeze-confirm]');
    $('#vcFreezeModal').on('click', '[data-vc-freeze-confirm]', function () {
        const d = getActiveData();
        const isFrozen = d.status !== 'active';
        const url = isFrozen ? d.unfreezeUrl : d.freezeUrl;
        const $btn = $(this);
        const $err = $('#vcFreezeModal [data-vc-modal-error]');
        $err.addClass('d-none').text('');
        $btn.prop('disabled', true);

        const payload = isFrozen ? {} : {
            reason: freezeReason,
            duration: freezeDuration,
        };

        $.ajax({
            url, method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf },
            data: payload,
            dataType: 'json',
        })
            .done(function () {
                bootstrap.Modal.getInstance(document.getElementById('vcFreezeModal')).hide();
                location.reload();
            })
            .fail(function (xhr) {
                $err.removeClass('d-none').text((xhr.responseJSON && xhr.responseJSON.error) || 'Failed to update card.');
                $btn.prop('disabled', false);
            });
    });

    // Hide reason/duration block on unfreeze flows
    $('#vcFreezeModal').on('show.bs.modal', function () {
        const d = getActiveData();
        const isFrozen = d.status !== 'active';
        $('[data-vc-freeze-reason-block]').toggleClass('d-none', isFrozen);
    });

    /* ------------------------------------------------------------------
     *  Limits modal — type chip group + save template
     * ------------------------------------------------------------------ */
    $('#vcLimitsModal').on('click', '[data-vc-limit-type-key]', function () {
        $('#vcLimitsModal [data-vc-limit-type-key]').removeClass('is-active');
        $(this).addClass('is-active');
        const key = $(this).data('vc-limit-type-key');
        $('#vcLimitsModal [name="limit_type"]').val(key);
    });

    $('#vcLimitsModal').on('click', '[data-vc-save-template]', function () {
        const $form = $('#vcLimitsModal [data-vc-limits-form]');
        const tpl = {
            per_transaction: $form.find('[name="per_transaction"]').val() || null,
            daily:           $form.find('[name="daily"]').val() || null,
            monthly:         $form.find('[name="monthly"]').val() || null,
            alert_at:        $form.find('[name="alert_at"]').val(),
            auto_freeze:     $form.find('[name="auto_freeze"]').val(),
            limit_type:      $form.find('[name="limit_type"]').val(),
        };
        try {
            localStorage.setItem('vc:limits-template', JSON.stringify(tpl));
            $(this).text('Saved ✓');
            setTimeout(() => $(this).text('Save as template'), 1400);
        } catch (e) {
            // localStorage may be unavailable — silently no-op
        }
    });

    // Pre-fill from template when opening if no per-card limits exist
    $('#vcLimitsModal').on('show.bs.modal', function () {
        const d = getActiveData();
        if (!d.limits || Object.keys(d.limits).length === 0) {
            try {
                const tpl = JSON.parse(localStorage.getItem('vc:limits-template') || 'null');
                if (tpl) {
                    const $form = $('#vcLimitsModal [data-vc-limits-form]');
                    Object.keys(tpl).forEach(function (k) {
                        const $f = $form.find('[name="' + k + '"]');
                        if ($f.length && tpl[k] != null) $f.val(tpl[k]);
                    });
                    if (tpl.alert_at) $('#vcLimitsModal [data-vc-alert-display]').text(tpl.alert_at + '%');
                    if (tpl.limit_type) {
                        $('#vcLimitsModal [data-vc-limit-type-key]').removeClass('is-active');
                        $('#vcLimitsModal [data-vc-limit-type-key="' + tpl.limit_type + '"]').addClass('is-active');
                    }
                }
            } catch (e) {}
        }
    });

    /* ------------------------------------------------------------------
     *  Spending bar chart + period switch
     * ------------------------------------------------------------------ */
    let spendChart = null;
    function renderSpendChart(period) {
        if (!window.ApexCharts || !window.VCPageData) return;
        const data = window.VCPageData.spend.series || [];
        const slice = period === '7d' ? data.slice(-7) : (period === '90d' ? data : data.slice(-30));
        const todayDate = (data.length ? data[data.length - 1].date : null);
        const colors = slice.map(p => p.date === todayDate ? '#3B6FE0' : '#D9E2F3');

        const opts = {
            chart: { type: 'bar', height: 110, toolbar: { show: false }, sparkline: { enabled: false }, animations: { enabled: false } },
            series: [{ name: 'Spend', data: slice.map(p => Number(p.value.toFixed(2))) }],
            xaxis: {
                categories: slice.map(p => p.label),
                labels: { show: false },
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: { show: false },
            grid: { show: false, padding: { top: 0, bottom: 0, left: 0, right: 0 } },
            plotOptions: { bar: { columnWidth: '60%', borderRadius: 3, distributed: true } },
            dataLabels: { enabled: false },
            legend: { show: false },
            tooltip: {
                custom: function ({ seriesIndex, dataPointIndex, w }) {
                    const sym = (window.VCPageData && window.VCPageData.currencySymbol) || '$';
                    const v = w.globals.series[seriesIndex][dataPointIndex];
                    const lbl = w.globals.labels[dataPointIndex];
                    return '<div style="padding:6px 9px;font-size:11px;background:var(--vc-ink);color:#fff;border-radius:5px;">' +
                           '<div style="opacity:.6;">' + lbl + '</div>' +
                           '<div style="font-weight:700;">' + sym + Number(v).toFixed(2) + '</div></div>';
                }
            },
            colors: colors,
            states: { hover: { filter: { type: 'darken', value: 0.85 } } },
        };

        const target = document.querySelector('[data-vc-spend-chart]');
        if (!target) return;

        if (spendChart) {
            spendChart.updateOptions(opts);
        } else {
            spendChart = new ApexCharts(target, opts);
            spendChart.render();
        }

        const total = (window.VCPageData.spend.totals || {})[period] || 0;
        const sym = window.VCPageData.currencySymbol || '$';
        $('[data-vc-spend-total]').text(sym + Number(total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
        $('[data-vc-spend-period-label]').text(period === '7d' ? 'Daily · last 7 days' : (period === '90d' ? 'Daily · last 90 days' : 'Daily · last 30 days'));
    }

    $('[data-vc-spend-period]').on('click', 'button[data-period]', function () {
        $('[data-vc-spend-period] button').removeClass('is-active');
        $(this).addClass('is-active');
        renderSpendChart($(this).data('period'));
    });

    /* ------------------------------------------------------------------
     *  Provider mix donut
     * ------------------------------------------------------------------ */
    function renderProviderDonut() {
        if (!window.ApexCharts || !window.VCPageData) return;
        const items = window.VCPageData.providerMix.items || [];
        const target = document.querySelector('[data-vc-mix-donut]');
        if (!target || items.length === 0) return;

        const opts = {
            chart: { type: 'donut', height: 130, width: 130, animations: { enabled: false } },
            series: items.map(i => Number(i.value)),
            labels: items.map(i => i.name),
            colors: items.map(i => i.color),
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { width: 0 },
            plotOptions: { pie: { donut: { size: '72%' }, expandOnClick: false } },
            tooltip: {
                custom: function ({ seriesIndex, w }) {
                    const sym = (window.VCPageData && window.VCPageData.currencySymbol) || '$';
                    const lbl = w.globals.labels[seriesIndex];
                    const v = w.globals.series[seriesIndex];
                    return '<div style="padding:6px 9px;font-size:11px;background:var(--vc-ink);color:#fff;border-radius:5px;">' +
                           '<div style="opacity:.6;">' + lbl + '</div>' +
                           '<div style="font-weight:700;">' + sym + Number(v).toFixed(2) + '</div></div>';
                }
            }
        };
        const chart = new ApexCharts(target, opts);
        chart.render();
    }

    /* ------------------------------------------------------------------
     *  Mini-card switcher: paint a real Visa / Mastercard / etc. SVG into
     *  every [data-vc-mini-network] slot. Falls back to the brand text when
     *  the brand is not in the recognised list — so a brand new provider
     *  never breaks the switcher.
     * ------------------------------------------------------------------ */
    function paintMiniNetworkMarks() {
        $page.find('[data-vc-card-id]').each(function () {
            const $btn = $(this);
            const brand = $btn.data('vc-brand') || $btn.data('vc-network') || '';
            const html = networkLogoHtml(brand);
            $btn.find('[data-vc-mini-network]').html(html || ('<span class="vc-mini__network-text">' + escapeHtml(String(brand).toUpperCase()) + '</span>'));
        });
    }

    /* ------------------------------------------------------------------
     *  Boot
     * ------------------------------------------------------------------ */
    paintMiniNetworkMarks();
    refreshHero();
    renderSpendChart('30d');
    renderProviderDonut();
})();
