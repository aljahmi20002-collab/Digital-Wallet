<section id="currency-gateway-rules" class="content-section">
    <h2>{{ __('Currency & Gateway Rules') }}</h2>
    <p>{{ __('Merchant API checkout is controlled by three layers: merchant-supported currency, active receiver wallet, and configured payment gateways. This keeps checkout options accurate after API credentials are generated.') }}</p>

    <div class="api-rule-panel">
        <div class="api-rule-panel__main">
            <h3>{{ __('Gateway Match Matrix') }}</h3>
            <p>{{ __('A gateway appears on hosted checkout only when every rule below is true. If the merchant has selected gateways, those selected gateways become the allowed set for checkout.') }}</p>
        </div>
        <div class="api-rule-panel__aside">
            <span class="api-rule-panel__label">{{ __('Dashboard Source') }}</span>
            <strong>{{ __('Merchant -> Config -> Payment Gateway Controls') }}</strong>
        </div>
    </div>

    <div class="api-check-grid">
        <div class="api-check-item">
            <i class="fas fa-check"></i>
            <div>
                <strong>{{ __('Currency enabled for merchant') }}</strong>
                <span>{{ __('The request currency_code must exist in the merchant-supported currency list.') }}</span>
            </div>
        </div>
        <div class="api-check-item">
            <i class="fas fa-check"></i>
            <div>
                <strong>{{ __('Active merchant wallet exists') }}</strong>
                <span>{{ __('The merchant owner must have an active wallet for the requested currency.') }}</span>
            </div>
        </div>
        <div class="api-check-item">
            <i class="fas fa-check"></i>
            <div>
                <strong>{{ __('Gateway currency matches') }}</strong>
                <span>{{ __('Only active automatic deposit methods for that exact currency are eligible.') }}</span>
            </div>
        </div>
        <div class="api-check-item">
            <i class="fas fa-check"></i>
            <div>
                <strong>{{ __('Merchant gateway selection passes') }}</strong>
                <span>{{ __('When gateways are selected in API Config, checkout is restricted to those selected methods.') }}</span>
            </div>
        </div>
    </div>

    <h3>{{ __('How allow_payment_methods Works Now') }}</h3>
    <table class="api-table">
        <thead>
            <tr>
                <th>{{ __('Layer') }}</th>
                <th>{{ __('Who controls it') }}</th>
                <th>{{ __('Effect') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><code>currency_code</code></td>
                <td>{{ __('API request') }}</td>
                <td>{{ __('Must match a merchant-supported currency with an active receiver wallet.') }}</td>
            </tr>
            <tr>
                <td><code>{{ __('Payment Gateway Controls') }}</code></td>
                <td>{{ __('Merchant dashboard') }}</td>
                <td>{{ __('Defines the gateway set available to hosted checkout for each eligible currency.') }}</td>
            </tr>
            <tr>
                <td><code>allow_payment_methods</code></td>
                <td>{{ __('API request') }}</td>
                <td>{{ __('Optional name/code keyword filter applied after merchant gateway rules. It cannot expose a gateway the merchant did not configure.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="api-alert api-alert-success api-alert-modern">
        <strong><i class="fas fa-lock me-2"></i>{{ __('Safe Default') }}</strong>
        {{ __('If a merchant has not selected any gateways, checkout falls back to all active automatic gateways that match the requested currency. Once the merchant selects gateways, checkout uses only that configured list.') }}
    </div>
</section>
