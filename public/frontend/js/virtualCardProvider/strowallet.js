/**
 * StroWallet handler — registers itself with the provider registry.
 * Uses the standard backend GET endpoint that returns a normalized JSON
 * payload, so no provider-specific UI logic lives here.
 */
(function () {
    "use strict";

    if (!window.VCProviderRegistry) return;

    // The fallback in the registry already handles the JSON-payload flow,
    // so we re-use it. This is what makes adding a new provider cheap:
    // if your backend returns the normalized payload, you don't even need
    // a custom JS file.
    window.VCProviderRegistry.register('strowallet', {
        renderCardDetails: window.VCProviderRegistry.fallback.renderCardDetails
    });
})();
