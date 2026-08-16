<section id="quick-start" class="content-section">
    <h2>@lang('Quick Start')</h2>
    <p>@lang('Use this flow for a production-safe integration. Credentials identify the merchant, while currency and gateway availability are controlled from the merchant dashboard.')</p>

    <div class="api-step-grid">
        <article class="api-step-card">
            <span class="api-step-card__number">01</span>
            <h3>@lang('Generate API Keys')</h3>
            <p>@lang('Open Dashboard -> Merchant -> Config and copy the Merchant Key, API Key, and Client Secret for sandbox or production.')</p>
        </article>

        <article class="api-step-card">
            <span class="api-step-card__number">02</span>
            <h3>@lang('Confirm Currencies')</h3>
            <p>@lang('Make sure the merchant supports the checkout currency and the merchant owner has an active wallet for that currency.')</p>
        </article>

        <article class="api-step-card">
            <span class="api-step-card__number">03</span>
            <h3>@lang('Select Gateways')</h3>
            <p>@lang('From API Config, enable the payment gateways that should appear on hosted checkout for each eligible currency.')</p>
        </article>

        <article class="api-step-card">
            <span class="api-step-card__number">04</span>
            <h3>@lang('Sign & Create Payment')</h3>
            <p>@lang('POST to /api/v1/initiate-payment with X-Timestamp and X-Signature, redirect the customer to payment_url, then verify or listen for IPN.')</p>
        </article>
    </div>

    <div class="api-flow-strip">
        <span>@lang('API Config')</span>
        <i class="fas fa-arrow-right"></i>
        <span>@lang('Currency + Wallet')</span>
        <i class="fas fa-arrow-right"></i>
        <span>@lang('Gateway Filter')</span>
        <i class="fas fa-arrow-right"></i>
        <span>@lang('Hosted Checkout')</span>
    </div>

    <div class="text-center mt-4">
        <a href="#testing" class="btn btn-primary btn-lg">
            <i class="fas fa-vial me-2"></i>@lang('Open Testing Console')
        </a>
        <p class="text-muted mt-2">@lang('Use sandbox credentials first, then switch to production when your checkout and IPN are verified.')</p>
    </div>
</section>
