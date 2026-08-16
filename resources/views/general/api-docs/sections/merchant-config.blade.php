<section id="merchant-config" class="content-section">
    <div class="api-section-kicker">
        <span><i class="fas fa-sliders-h"></i>{{ __('Latest Merchant Update') }}</span>
        <strong>{{ __('API Config controls checkout after keys are generated') }}</strong>
    </div>

    <h2>{{ __('Merchant Setup & API Config') }}</h2>
    <p>{{ __('The merchant API is no longer a single-currency, fixed-gateway flow. A merchant can now support multiple currency rails, keep separate sandbox and production credentials, and choose eligible gateways from the API Config page without regenerating API keys.') }}</p>

    <div class="api-config-surface">
        <div class="api-config-surface__main">
            <span class="api-eyebrow api-eyebrow--light">
                <i class="fas fa-cog"></i>
                {{ __('Merchant Dashboard') }}
            </span>
            <h3>{{ __('One integration, dashboard-controlled checkout') }}</h3>
            <p>{{ __('Your code sends amount, currency, redirects, IPN URL, and optional gateway keywords. The dashboard decides which currency rails, wallets, and payment gateways are actually available for that merchant.') }}</p>
            <div class="api-inline-kpis">
                <span><i class="fas fa-flask"></i>{{ __('Sandbox keys') }}</span>
                <span><i class="fas fa-rocket"></i>{{ __('Production keys') }}</span>
                <span><i class="fas fa-wallet"></i>{{ __('Wallet rails') }}</span>
                <span><i class="fas fa-credit-card"></i>{{ __('Gateway controls') }}</span>
            </div>
        </div>
        <div class="api-config-surface__aside">
            <div>
                <span>{{ __('Default mode') }}</span>
                <strong>{{ __('Sandbox') }}</strong>
            </div>
            <div>
                <span>{{ __('Live access') }}</span>
                <strong>{{ __('Requires approval') }}</strong>
            </div>
            <div>
                <span>{{ __('Gateway source') }}</span>
                <strong>{{ __('API Config') }}</strong>
            </div>
        </div>
    </div>

    <div class="api-control-grid">
        <article class="api-control-card">
            <i class="fas fa-user-check"></i>
            <h3>{{ __('Merchant Review Status') }}</h3>
            <p>{{ __('Pending merchants can test in sandbox. Production API access and live checkout are available only after admin approval.') }}</p>
        </article>
        <article class="api-control-card">
            <i class="fas fa-coins"></i>
            <h3>{{ __('Currency Rails') }}</h3>
            <p>{{ __('Each request currency_code must exist in the merchant-supported currency list. Unsupported currencies are rejected before checkout opens.') }}</p>
        </article>
        <article class="api-control-card">
            <i class="fas fa-wallet"></i>
            <h3>{{ __('Receiver Wallet Match') }}</h3>
            <p>{{ __('The merchant owner must have an active wallet for the requested currency, otherwise the API returns a wallet availability error.') }}</p>
        </article>
        <article class="api-control-card">
            <i class="fas fa-credit-card"></i>
            <h3>{{ __('Payment Gateway Controls') }}</h3>
            <p>{{ __('Only active automatic gateways that match the requested currency and merchant selection can appear on hosted checkout.') }}</p>
        </article>
    </div>

    <h3>{{ __('Runtime Decision Order') }}</h3>
    <ol class="api-lifecycle">
        <li>
            <span>01</span>
            <div>
                <strong>{{ __('Authenticate the environment') }}</strong>
                <p>{{ __('X-Environment decides which merchant key, API key, and API secret are used.') }}</p>
            </div>
        </li>
        <li>
            <span>02</span>
            <div>
                <strong>{{ __('Check merchant availability') }}</strong>
                <p>{{ __('Rejected or disabled merchants are blocked. Production also requires approved merchant status.') }}</p>
            </div>
        </li>
        <li>
            <span>03</span>
            <div>
                <strong>{{ __('Validate requested currency') }}</strong>
                <p>{{ __('currency_code must match a merchant-supported rail such as USD, EUR, or BDT.') }}</p>
            </div>
        </li>
        <li>
            <span>04</span>
            <div>
                <strong>{{ __('Confirm receiver wallet') }}</strong>
                <p>{{ __('The merchant owner needs an active wallet for that same currency.') }}</p>
            </div>
        </li>
        <li>
            <span>05</span>
            <div>
                <strong>{{ __('Resolve configured gateways') }}</strong>
                <p>{{ __('Merchant API Config selections restrict checkout to the chosen eligible gateways. If none are selected, the safe fallback is all active automatic gateways for that currency.') }}</p>
            </div>
        </li>
        <li>
            <span>06</span>
            <div>
                <strong>{{ __('Apply optional API filter') }}</strong>
                <p>{{ __('allow_payment_methods can narrow the available gateway list, but it cannot expose an unconfigured or wrong-currency gateway.') }}</p>
            </div>
        </li>
    </ol>

    <h3>{{ __('Real Cases & Results') }}</h3>
    <table class="api-table api-table--compact">
        <thead>
            <tr>
                <th>{{ __('Case') }}</th>
                <th>{{ __('API Result') }}</th>
                <th>{{ __('Fix') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ __('Merchant supports USD and EUR, but only USD wallet exists.') }}</td>
                <td><code>422 Receiver wallet for this currency is not available.</code></td>
                <td>{{ __('Create/activate the EUR merchant wallet before accepting EUR checkout.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Stripe USD is selected, but the request sends currency_code EUR.') }}</td>
                <td>{{ __('Stripe USD will not appear on checkout.') }}</td>
                <td>{{ __('Select an active EUR gateway or send USD.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Merchant has not selected any gateway yet.') }}</td>
                <td>{{ __('Checkout falls back to all active automatic gateways that match the requested currency.') }}</td>
                <td>{{ __('Use API Config to lock checkout to preferred gateways.') }}</td>
            </tr>
            <tr>
                <td><code>allow_payment_methods=["paypal"]</code> {{ __('but PayPal is not configured for the merchant.') }}</td>
                <td>{{ __('PayPal remains hidden.') }}</td>
                <td>{{ __('Enable PayPal from Payment Gateway Controls if its currency matches.') }}</td>
            </tr>
            <tr>
                <td>{{ __('Pending merchant calls production API.') }}</td>
                <td><code>403 Merchant Pending Approval</code></td>
                <td>{{ __('Use sandbox until admin approval is complete.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="api-limit-list">
        <div>
            <strong><i class="fas fa-ban"></i>{{ __('No cross-currency gateways') }}</strong>
            <p>{{ __('A gateway configured for USD cannot process EUR checkout unless a separate EUR gateway exists.') }}</p>
        </div>
        <div>
            <strong><i class="fas fa-shield-alt"></i>{{ __('Secrets stay server-side') }}</strong>
            <p>{{ __('API Secret is used only to sign server-to-server API requests and verify IPN signatures.') }}</p>
        </div>
        <div>
            <strong><i class="fas fa-clock"></i>{{ __('Signed checkout URL expires') }}</strong>
            <p>{{ __('The returned payment_url is time-limited. Create a new payment session for expired checkout links.') }}</p>
        </div>
        <div>
            <strong><i class="fas fa-user-lock"></i>{{ __('Verify is merchant scoped') }}</strong>
            <p>{{ __('A merchant can verify only its own transaction and only in the matching sandbox or production environment.') }}</p>
        </div>
    </div>
</section>
