/**
 * DigiKash Virtual Card · Provider handler registry
 *
 * Each provider's frontend file calls window.VCProviderRegistry.register(code, handler)
 * to opt itself in. The main page asks the registry to render card details for the
 * active card. Adding a new provider means dropping a new JS file under
 * /frontend/js/virtualCardProvider/ — no edits to the main page or this file.
 */
(function () {
    "use strict";

    const handlers = {};

    const fallbackHandler = {
        renderCardDetails(ctx) {
            const url = ctx.detailsUrl;
            const $body = ctx.$modalBody;

            $body.html('<div class="d-flex justify-content-center align-items-center" style="height:240px;"><span class="spinner-border" style="color:var(--vc-brand);"></span></div>');

            $.ajax({ url, method: 'GET', dataType: 'json' })
                .done(function (resp) {
                    if (resp.error) {
                        $body.html('<div class="alert alert-danger">' + resp.error + '</div>');
                        return;
                    }
                    if (resp.html) {
                        $body.html(resp.html);
                        return;
                    }
                    if (resp.data) {
                        // Render the normalized payload using the same partial via template strings.
                        $body.html(window.VCProviderRegistry.renderDetailsPayload(resp.data));
                        return;
                    }
                    $body.html('<div class="alert alert-warning">' + (resp.message || 'No card details available.') + '</div>');
                })
                .fail(function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.error) || 'Failed to load card details.';
                    $body.html('<div class="alert alert-danger">' + msg + '</div>');
                });
        }
    };

    function escape(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function fmtMoney(n) {
        const num = Number(n) || 0;
        return '$' + num.toFixed(2);
    }

    function row(label, value) {
        return '<div class="vc-cd__row"><div class="vc-cd__label">' + escape(label) + '</div><div class="vc-cd__value">' + value + '</div></div>';
    }

    function renderDetailsPayload(d) {
        let html = '<div class="vc-cd">';

        if (d.card_holder_name) {
            html += row('Cardholder', escape(d.card_holder_name));
        }

        html += row('Card Number',
            '<span class="mono" style="font-size:15px;letter-spacing:1px;">' +
            escape(d.card_number || '**** **** **** ****') +
            '</span>');

        html += '<div class="vc-cd__grid">';
        html += '<div><div class="vc-cd__label">Expiry</div><div class="vc-cd__value mono">' + escape(d.expiry || '--/--') + '</div></div>';
        html += '<div><div class="vc-cd__label">CVV</div><div class="vc-cd__value mono">' + escape(d.cvv || '***') + '</div></div>';
        html += '<div><div class="vc-cd__label">Brand</div><div class="vc-cd__value">' + escape(d.card_brand || '—') + '</div></div>';
        html += '<div><div class="vc-cd__label">Status</div><div class="vc-cd__value"><span class="vc-pill vc-pill--green"><span class="vc-pill__dot"></span>' + escape((d.card_status || 'active')) + '</span></div></div>';
        html += '</div>';

        if (d.balance !== undefined && d.balance !== null) {
            html += row('Card Balance', fmtMoney(d.balance));
        }

        if (d.billing_street) {
            const parts = [d.billing_street];
            if (d.billing_city) parts.push(d.billing_city);
            if (d.billing_country) parts.push(d.billing_country);
            if (d.billing_zip_code) parts.push(d.billing_zip_code);
            html += row('Billing Address', escape(parts.join(', ')));
        }

        if (d.customer_email) {
            html += row('Email', escape(d.customer_email));
        }

        if (d.message) {
            html += '<div class="alert alert-warning" style="margin-top:12px;">' + escape(d.message) + '</div>';
        }

        html += '</div>';
        html += '<style>.vc-cd__row{padding:10px 0;border-bottom:1px solid var(--vc-line-2);}.vc-cd__row:last-child{border-bottom:none;}.vc-cd__label{font-size:11px;font-weight:700;letter-spacing:.8px;color:var(--vc-muted);text-transform:uppercase;}.vc-cd__value{font-size:14px;font-weight:600;color:var(--vc-ink);margin-top:4px;}.vc-cd__grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;padding:10px 0;border-bottom:1px solid var(--vc-line-2);}.vc-cd .mono{font-family:JetBrains Mono,monospace;}</style>';

        return html;
    }

    window.VCProviderRegistry = {
        register(code, handler) {
            if (!code) return;
            handlers[code.toLowerCase()] = Object.assign({}, fallbackHandler, handler || {});
        },
        get(code) {
            if (!code) return fallbackHandler;
            return handlers[code.toLowerCase()] || fallbackHandler;
        },
        renderDetailsPayload,
        fallback: fallbackHandler,
    };
})();
