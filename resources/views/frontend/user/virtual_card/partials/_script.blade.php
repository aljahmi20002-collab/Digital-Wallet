@php
    use App\Models\PaymentGateway;
    use App\Models\VirtualCardProvider;

    /*
     * Discover provider JS handlers automatically.
     * Each enabled provider can ship a file at /frontend/js/virtualCardProvider/{code}.js
     * which calls VCProviderRegistry.register(code, handler) on load.
     * If a provider has no JS file, the registry's normalized JSON fallback is used —
     * adding a new gateway requires zero edits to this partial.
     */
    $providerCodes = VirtualCardProvider::active()->pluck('code')->all();

    $stripeKey = null;
    if (in_array('stripe', $providerCodes, true)) {
        try {
            $stripeKey = PaymentGateway::getCredentials('stripe')['stripe_key'] ?? null;
        } catch (\Throwable $e) {
            $stripeKey = null;
        }
    }
@endphp

@if(in_array('stripe', $providerCodes, true))
    <script src="https://js.stripe.com/v3/"></script>
    @if($stripeKey)
        <script>window.VCStripeConfig = { publishableKey: @json($stripeKey) };</script>
    @endif
@endif

{{-- Registry first, then auto-discovered provider handlers --}}
<script src="{{ asset('frontend/js/virtualCardProvider/registry.js?v='.config('app.version')) }}"></script>

@foreach($providerCodes as $code)
    @php
        $jsPath = "frontend/js/virtualCardProvider/{$code}.js";
        $exists = file_exists(public_path($jsPath));
    @endphp
    @if($exists)
        <script src="{{ asset($jsPath) }}?v={{ config('app.version') }}"></script>
    @endif
@endforeach

{{-- Main page logic (provider-agnostic) --}}
<script src="{{ asset('frontend/js/virtual-card.js?v='.config('app.version')) }}"></script>

<script>
    "use strict";
    (function () {
        // Best-effort polyfill for input sanitiser used by the global form helpers
        if (typeof window.validateDouble !== 'function') {
            window.validateDouble = function (val) {
                if (typeof val !== 'string') return val;
                return val.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
            };
        }
    })();

    // Submit topup/withdraw modals via AJAX so we stay on the cards page.
    $(document).on('submit', '[data-vc-topup-form], [data-vc-withdraw-form]', function (e) {
        e.preventDefault();
        const $form = $(this);
        const $err = $form.find('[data-vc-modal-error]');
        const $btn = $form.find('button[type="submit"]');
        $err.addClass('d-none').text('');
        $btn.prop('disabled', true);

        $.post({
            url: $form.attr('action'),
            data: $form.serialize(),
            dataType: 'json',
        })
            .done(function () {
                location.reload();
            })
            .fail(function (xhr) {
                const msg = (xhr.responseJSON && (xhr.responseJSON.error
                    || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors).flat()[0])))
                    || 'Action failed. Please try again.';
                $err.removeClass('d-none').text(msg);
                $btn.prop('disabled', false);
            });
    });
</script>
