/**
 * Bitnob virtual-card frontend handler.
 *
 * The main page calls VCProviderRegistry.get('bitnob').renderCardDetails(...)
 * when the user clicks Reveal on a Bitnob card. Bitnob returns a normalized
 * payload (see BitnobCardProvider::getCardDetails) so the shared fallback
 * renderer is enough — we just register and pass through. This file exists
 * so adding gateway-specific UI later is a one-file change with no edits to
 * the main page or the registry.
 */
(function () {
    "use strict";
    if (!window.VCProviderRegistry) return;

    window.VCProviderRegistry.register('bitnob', {
        // Use the registry's fallback handler (XHR -> renderDetailsPayload)
        // and add a small "powered by Bitnob" footer so users can tell where
        // the data came from.
        renderCardDetails(ctx) {
            window.VCProviderRegistry.fallback.renderCardDetails(ctx);

            // Append a brand footer once the AJAX renders.
            setTimeout(function () {
                if (ctx.$modalBody && ctx.$modalBody.find('.vc-cd').length) {
                    ctx.$modalBody.find('.vc-cd').append(
                        '<div style="margin-top:14px;padding-top:12px;border-top:1px solid var(--vc-line-2);font-size:11px;color:var(--vc-muted);display:flex;align-items:center;gap:6px;">' +
                            '<span>Powered by</span>' +
                            '<strong style="color:var(--vc-ink);">Bitnob</strong>' +
                        '</div>'
                    );
                }
            }, 350);
        },
    });
})();
