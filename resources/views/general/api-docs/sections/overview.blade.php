<section id="overview" class="content-section api-hero-section">
    <div class="api-hero">
        <div class="api-hero__content">
            <span class="api-eyebrow">
                <i class="fas fa-shield-alt"></i>
                @lang('Merchant API Reference')
            </span>
            <h1>{{ setting('site_title') }} @lang('Merchant API')</h1>
            <p class="lead-text">
                @lang('Accept wallet, gateway, and hosted checkout payments with environment-aware credentials, merchant-level currency control, and dashboard-managed payment gateway rules.')
            </p>

            <div class="api-hero__actions">
                <a href="#quick-start" class="btn btn-primary btn-lg">
                    <i class="fas fa-play me-2"></i>@lang('Start Integration')
                </a>
                <a href="#initiate-payment" class="btn btn-light btn-lg">
                    <i class="fas fa-terminal me-2"></i>@lang('View Endpoint')
                </a>
            </div>
        </div>

        <div class="api-hero__panel">
            <div class="api-hero__status">
                <span class="status-dot"></span>
                @lang('Production ready')
            </div>
            <div class="api-hero__metric">
                <span>POST</span>
                <strong>/api/v1/initiate-payment</strong>
            </div>
            <div class="api-hero__metric">
                <span>GET</span>
                <strong>/api/v1/verify-payment/{trxId}</strong>
            </div>
            <div class="api-hero__tags">
                <span>@lang('Sandbox')</span>
                <span>@lang('Multi-currency')</span>
                <span>@lang('Signed API')</span>
                <span>@lang('Gateway lock')</span>
            </div>
        </div>
    </div>

    <div class="api-alert api-alert-info api-alert-modern">
        <strong><i class="fas fa-info-circle me-2"></i>@lang('Latest Merchant Flow')</strong>
        @lang('Gateways are now configured after API generation from the merchant API Config page. Checkout only shows gateways that match the merchant currency and the active receiver wallet.')
    </div>

    <div class="api-feature-grid">
        <article class="api-feature-card">
            <span class="api-feature-card__icon api-feature-card__icon--blue">
                <i class="fas fa-bolt"></i>
            </span>
            <h3>@lang('Fast Hosted Checkout')</h3>
            <p>@lang('Create a payment session, redirect the customer, then verify or receive IPN updates when the transaction completes.')</p>
        </article>

        <article class="api-feature-card">
            <span class="api-feature-card__icon api-feature-card__icon--green">
                <i class="fas fa-wallet"></i>
            </span>
            <h3>@lang('Wallet-aware Currency')</h3>
            <p>@lang('The requested currency must be enabled for the merchant and backed by an active merchant wallet.')</p>
        </article>

        <article class="api-feature-card">
            <span class="api-feature-card__icon api-feature-card__icon--amber">
                <i class="fas fa-sliders"></i>
            </span>
            <h3>@lang('Configurable Gateways')</h3>
            <p>@lang('Merchants can choose eligible payment gateways from API Config without regenerating API credentials.')</p>
        </article>
    </div>
</section>
