<section id="sandbox-guide" class="content-section">
    <div class="api-section-kicker">
        <span><i class="fas fa-list-check"></i>{{ __('Release Readiness') }}</span>
        <strong>{{ __('Sandbox first, production after approval') }}</strong>
    </div>

    <h2>{{ __('Sandbox & Go Live Checklist') }}</h2>
    <p>{{ __('Use this checklist before moving from test payments to live checkout. The same API routes work in both modes; only the environment header and credential set change.') }}</p>

    <div class="api-release-grid">
        <article class="api-release-card api-release-card--sandbox">
            <span class="api-release-card__icon"><i class="fas fa-flask"></i></span>
            <h3>{{ __('Sandbox') }}</h3>
            <p>{{ __('Use test credentials and X-Environment: sandbox. Transactions are marked as sandbox and do not represent real money movement.') }}</p>
            <ul>
                <li>{{ __('Generate or copy test Merchant ID, API Key, and API Secret.') }}</li>
                <li>{{ __('Send signed API requests with the sandbox secret.') }}</li>
                <li>{{ __('Test success, cancel, pending, failed, webhook, and verify flows.') }}</li>
            </ul>
        </article>

        <article class="api-release-card api-release-card--production">
            <span class="api-release-card__icon"><i class="fas fa-rocket"></i></span>
            <h3>{{ __('Production') }}</h3>
            <p>{{ __('Use live credentials and X-Environment: production only after admin approval, wallet readiness, and gateway configuration are complete.') }}</p>
            <ul>
                <li>{{ __('Merchant status must be approved.') }}</li>
                <li>{{ __('Every live currency must have an active merchant wallet.') }}</li>
                <li>{{ __('Payment Gateway Controls should contain only gateways you want customers to see.') }}</li>
            </ul>
        </article>
    </div>

    <div class="api-readiness-board">
        <div class="api-readiness-board__head">
            <div>
                <span>{{ __('Production Launch Checks') }}</span>
                <strong>{{ __('Required before live traffic') }}</strong>
            </div>
            <a href="#merchant-config" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-gears me-1"></i>{{ __('Review Config') }}
            </a>
        </div>
        <div class="api-readiness-items">
            <div>
                <i class="fas fa-user-check"></i>
                <strong>{{ __('Approved merchant') }}</strong>
                <span>{{ __('Production API returns 403 until admin approval is complete.') }}</span>
            </div>
            <div>
                <i class="fas fa-key"></i>
                <strong>{{ __('Server-side signing') }}</strong>
                <span>{{ __('Each request includes X-Timestamp and X-Signature generated with the matching API secret.') }}</span>
            </div>
            <div>
                <i class="fas fa-wallet"></i>
                <strong>{{ __('Wallet coverage') }}</strong>
                <span>{{ __('Each merchant currency has an active receiver wallet.') }}</span>
            </div>
            <div>
                <i class="fas fa-credit-card"></i>
                <strong>{{ __('Gateway coverage') }}</strong>
                <span>{{ __('Selected gateways match the currencies customers will use.') }}</span>
            </div>
            <div>
                <i class="fas fa-link"></i>
                <strong>{{ __('Redirect URLs') }}</strong>
                <span>{{ __('success_redirect and cancel_redirect are HTTPS and handle repeated visits safely.') }}</span>
            </div>
            <div>
                <i class="fas fa-shield-alt"></i>
                <strong>{{ __('IPN verification') }}</strong>
                <span>{{ __('Webhook signature is verified before fulfillment or wallet-side order updates.') }}</span>
            </div>
        </div>
    </div>

    <div class="api-alert api-alert-warning api-alert-modern">
        <strong><i class="fas fa-triangle-exclamation me-2"></i>{{ __('Important') }}</strong>
        {{ __('Never switch only the X-Environment header. Always switch the Merchant ID, API Key, and API Secret together so the environment and credential set match.') }}
    </div>
</section>
