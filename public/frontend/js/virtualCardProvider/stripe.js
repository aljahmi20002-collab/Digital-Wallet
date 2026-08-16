/**
 * Stripe Issuing handler — registers itself with the provider registry.
 * Adding a new provider follows the same pattern: register a handler keyed
 * by the provider's `code` column on virtual_card_providers.
 */
(function () {
    "use strict";

    if (!window.VCProviderRegistry) return;

    function fetchEphemeralKey(stripeCardId, nonce) {
        return fetch('/api/stripe/ephemeral-key', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ card_id: stripeCardId, nonce })
        }).then(r => r.json());
    }

    async function renderCardDetails(ctx) {
        const $body = ctx.$modalBody;
        const providerCardId = ctx.providerCardId;
        const stripeKey = window.VCStripeConfig && window.VCStripeConfig.publishableKey;

        if (!stripeKey || typeof Stripe !== 'function') {
            $body.html('<div class="alert alert-danger">Stripe SDK not configured.</div>');
            return;
        }

        $body.html('<div class="d-flex justify-content-center align-items-center" style="height:240px;"><span class="spinner-border" style="color:var(--vc-brand);"></span></div>');

        try {
            const stripe = Stripe(stripeKey);
            const nonceResult = await stripe.createEphemeralKeyNonce({ issuingCard: providerCardId });
            const nonce = nonceResult.nonce;

            const data = await fetchEphemeralKey(providerCardId, nonce);
            if (data.error || !data.ephemeralKeySecret || !data.stripeCardId) {
                $body.html('<div class="alert alert-danger">Unable to retrieve card details.</div>');
                return;
            }

            const elements = stripe.elements();
            const cardNumber = elements.create('issuingCardNumberDisplay', {
                issuingCard: data.stripeCardId,
                nonce,
                ephemeralKeySecret: data.ephemeralKeySecret,
                style: { base: { fontSize: '16px', color: '#0B1220', fontFamily: 'JetBrains Mono, monospace' } }
            });
            const cardExpiry = elements.create('issuingCardExpiryDisplay', {
                issuingCard: data.stripeCardId,
                nonce,
                ephemeralKeySecret: data.ephemeralKeySecret,
                style: { base: { fontSize: '14px', color: '#0B1220', fontFamily: 'JetBrains Mono, monospace' } }
            });
            const cardCvc = elements.create('issuingCardCvcDisplay', {
                issuingCard: data.stripeCardId,
                nonce,
                ephemeralKeySecret: data.ephemeralKeySecret,
                style: { base: { fontSize: '14px', color: '#0B1220', fontFamily: 'JetBrains Mono, monospace' } }
            });

            $body.html(
                '<div class="vc-cd">' +
                  '<div class="vc-cd__row"><div class="vc-cd__label">Card Number</div><div class="vc-cd__value mono"><div id="vc-stripe-pan"></div></div></div>' +
                  '<div class="vc-cd__grid">' +
                    '<div><div class="vc-cd__label">Expiry</div><div class="vc-cd__value mono"><div id="vc-stripe-exp"></div></div></div>' +
                    '<div><div class="vc-cd__label">CVC</div><div class="vc-cd__value mono"><div id="vc-stripe-cvc"></div></div></div>' +
                  '</div>' +
                '</div>' +
                '<style>.vc-cd__row{padding:10px 0;border-bottom:1px solid var(--vc-line-2);}.vc-cd__label{font-size:11px;font-weight:700;letter-spacing:.8px;color:var(--vc-muted);text-transform:uppercase;}.vc-cd__value{margin-top:4px;}.vc-cd__grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;padding:10px 0;}.vc-cd .mono{font-family:JetBrains Mono,monospace;}</style>'
            );

            cardNumber.mount('#vc-stripe-pan');
            cardExpiry.mount('#vc-stripe-exp');
            cardCvc.mount('#vc-stripe-cvc');
        } catch (err) {
            $body.html('<div class="alert alert-danger">Failed to load card details: ' + (err.message || 'Unknown error') + '</div>');
        }
    }

    window.VCProviderRegistry.register('stripe', { renderCardDetails });
})();
