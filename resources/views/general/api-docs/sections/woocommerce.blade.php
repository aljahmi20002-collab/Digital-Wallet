<!-- WooCommerce Integration Section -->
<section id="woocommerce-integration" class="content-section">
    <h2><i class="fa-brands fa-wordpress text-info me-2"></i>WooCommerce Integration</h2>
    <p>Advanced {{ setting('site_title') }} payment gateway with modern WooCommerce Blocks support, dynamic branding, and enterprise-grade security features.</p>

    <div class="woocommerce-integration-section">
        <!-- Professional Header Section -->
        <div class="woocommerce-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fab fa-wordpress-simple"></i>
                </div>
                <div class="header-text">
                    <h3>Advanced WooCommerce Integration</h3>
                    <p>Production-ready payment gateway with WooCommerce Blocks (Gutenberg) support, dynamic branding, and secure webhook processing.</p>
                </div>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <span class="stat-number">3</span>
                    <span class="stat-label">Minutes Setup</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">99.9%</span>
                    <span class="stat-label">Uptime</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number">24/7</span>
                    <span class="stat-label">Webhook Support</span>
                </div>
            </div>
        </div>

        <!-- Quick Overview Cards -->
        <div class="overview-section">
            <div class="row mb-4 woocommerce-cards-row">
                <div class="col-md-6">
                    <div class="card border-primary premium-card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-download me-2"></i>Latest Plugin Download</h5>
                        </div>
                        <div class="card-body">
                            <p>Enterprise-grade WooCommerce payment gateway with modern Blocks support and dynamic branding.</p>
                            <div class="download-info">
                                <div class="info-item">
                                    <i class="fas fa-file-archive text-primary"></i>
                                    <span>Plugin Size: 45.2 KB</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-code-branch text-success"></i>
                                    <span>Version: 2.8.0</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-calendar text-info"></i>
                                    <span>Last Updated: {{ date('M d, Y') }}</span>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <a href="{{ asset('general/woocommerce-gateway-v2.8.0.zip') }}" class="btn btn-primary btn-download" download>
                                    <i class="fas fa-download me-2"></i>Download Plugin v2.8.0
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-success premium-card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>System Requirements</h5>
                        </div>
                        <div class="card-body">
                            <ul class="requirements-list">
                                <li><i class="fas fa-check text-success"></i>WordPress 5.8+</li>
                                <li><i class="fas fa-check text-success"></i>WooCommerce 6.0+</li>
                                <li><i class="fas fa-check text-success"></i>PHP 8.1+ (8.2+ Recommended)</li>
                                <li><i class="fas fa-check text-success"></i>SSL Certificate (Required)</li>
                                <li><i class="fas fa-check text-success"></i>{{ setting('site_title') }} Merchant Account</li>
                                <li><i class="fas fa-star text-warning"></i>WooCommerce Blocks Support</li>
                            </ul>
                            <div class="compatibility-badge">
                                <i class="fas fa-shield-alt text-success me-1"></i>
                                <span>Production Ready</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Features Section -->
        <div class="features-section">
            <div class="section-header">
                <h4><i class="fas fa-rocket text-warning me-2"></i>Advanced Features</h4>
                <p>Enterprise-grade payment processing with modern architecture</p>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon bg-primary">
                            <i class="fab fa-react"></i>
                        </div>
                        <h6><i class="fas fa-puzzle-piece me-2"></i>@lang('WooCommerce Blocks')</h6>
                        <p>Full Gutenberg checkout compatibility with React-based UI</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon bg-success">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h6><i class="fas fa-paint-brush me-2"></i>@lang('Dynamic Branding')</h6>
                        <p>Auto-fetch logo, colors, and branding from {{ setting('site_title') }} API</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon bg-info">
                            <i class="fas fa-webhook"></i>
                        </div>
                        <h6><i class="fas fa-shield-alt me-2"></i>@lang('Secure Webhooks')</h6>
                        <p>HMAC-SHA256 signature verification for payment callbacks</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon bg-warning">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h6><i class="fas fa-mobile-alt me-2"></i>@lang('Compact Mobile UI')</h6>
                        <p>Space-efficient, responsive design for all devices</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon bg-danger">
                            <i class="fas fa-shield-virus"></i>
                        </div>
                        <h6><i class="fas fa-shield-virus me-2"></i>@lang('Advanced Security')</h6>
                        <p>Multi-header authentication with environment isolation</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="feature-card">
                        <div class="feature-icon bg-dark">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h6><i class="fas fa-vial me-2"></i>@lang('Test/Live Mode')</h6>
                        <p>Seamless sandbox testing with production deployment</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Installation Steps Section -->
        <div class="card mb-4 installation-section">
            <div class="card-body">
                <div class="section-header mb-3">
                    <h4 class="card-title"><i class="fas fa-cogs text-primary me-2"></i>@lang('Quick Installation Guide')</h4>
                    <p class="card-text">@lang('Get started with :site WooCommerce integration in minutes', ['site' => setting('site_title')])</p>
                </div>
                
                <div class="installation-steps">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h6><i class="fas fa-download me-2"></i>@lang('Download Latest Plugin')</h6>
                            <p>Download {{ setting('site_title') }} WooCommerce Gateway v2.8.0 from the download section above. This includes all latest features and security updates.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h6><i class="fas fa-upload me-2"></i>@lang('Install via WordPress Admin')</h6>
                            <p>Navigate to <code>Plugins → Add New → Upload Plugin</code> and select the downloaded ZIP file. The plugin will auto-extract and install.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h6><i class="fas fa-cog me-2"></i>@lang('Activate & Configure')</h6>
                            <p>Activate the plugin and go to <code>WooCommerce → Settings → Payments → {{ setting('site_title') }}</code>. Enter your API credentials and webhook secret.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h6><i class="fas fa-vial me-2"></i>@lang('Test Integration')</h6>
                            <p>Enable <strong>Test Mode</strong>, process a sandbox transaction to verify Blocks checkout, webhook delivery, and order completion.</p>
                        </div>
                    </div>

                    <div class="step-item">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h6><i class="fas fa-rocket me-2"></i>@lang('Go Live')</h6>
                            <p>Disable test mode, ensure production API keys are configured, and start accepting real payments with full webhook processing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Configuration Section -->
        <div class="card mb-4 api-config-section">
            <div class="card-body">
                <div class="section-header mb-3">
                    <h4 class="card-title"><i class="fas fa-key text-secondary me-2"></i>@lang('API Configuration')</h4>
                    <p class="card-text">@lang('Essential API settings for secure payment processing')</p>
                </div>
                
                <div class="config-grid">
                    <div class="config-item d-flex align-items-center">
                        <i class="fas fa-server text-primary me-2"></i>
                        <div class="config-content">
                            <h6 class="mb-0">@lang('API Base URL')</h6>
                            <p><code>{{ request()->getSchemeAndHttpHost() }}</code></p>
                            <span class="config-badge">Auto-configured</span>
                        </div>
                    </div>
                    
                    <div class="config-item d-flex align-items-center">
                        <i class="fas fa-key text-warning me-2"></i>
                        <div class="config-content">
                            <h6 class="mb-0">@lang('Merchant ID & API Key')</h6>
                            <p>Your unique merchant credentials from {{ setting('site_title') }} dashboard</p>
                            <span class="config-badge">Required</span>
                        </div>
                    </div>
                    
                    <div class="config-item d-flex align-items-center">
                        <i class="fas fa-shield-alt text-success me-2"></i>
                        <div class="config-content">
                            <h6 class="mb-0">@lang('Webhook Secret')</h6>
                            <p>HMAC-SHA256 signature verification for secure callbacks</p>
                            <span class="config-badge">Recommended</span>
                        </div>
                    </div>
                </div>

                <!-- API Headers Section -->
                <div class="api-headers-section mt-4">
                    <h5><i class="fas fa-list text-info me-2"></i>@lang('Authentication Headers')</h5>
                    <p>@lang('Required headers for all :site API requests:', ['site' => setting('site_title')])</p>
                    <div class="code-block">
                        <pre><code>X-Environment: sandbox|production
X-Merchant-Key: your_merchant_id
X-API-Key: your_api_key
Content-Type: application/json</code></pre>
                    </div>
                </div>
            </div>
        </div>

        <!-- Webhook Configuration -->
        <div class="card mb-4 webhook-section">
            <div class="card-body">
                <div class="section-header mb-3">
                    <h4 class="card-title"><i class="fas fa-webhook text-info me-2"></i>@lang('Webhook Configuration')</h4>
                    <p class="card-text">@lang('Real-time payment status updates and order processing')</p>
                </div>
                
                <div class="webhook-config">
                    <div class="webhook-info">
                        <h6><i class="fas fa-link me-2"></i>@lang('Webhook Endpoint')</h6>
                        <div class="endpoint-box">
                            <code>https://yoursite.com/wc-api/{{ strtolower(setting("site_title")) }}_webhook</code>
                            <button class="btn btn-sm btn-outline-primary copy-btn" onclick="copyToClipboard(this)">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <p class="mt-2"><small>Configure this URL in your {{ setting('site_title') }} merchant dashboard for automatic order updates.</small></p>
                    </div>

                    <div class="webhook-events">
                        <h6><i class="fas fa-list-alt me-2"></i>@lang('Supported Events')</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="event-list">
                                    <li><i class="fas fa-check text-success"></i> Payment Success</li>
                                    <li><i class="fas fa-times text-danger"></i> Payment Failed</li>
                                    <li><i class="fas fa-ban text-warning"></i> Payment Cancelled</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="event-list">
                                    <li><i class="fas fa-pause text-info"></i> Payment Pending</li>
                                    <li><i class="fas fa-undo text-secondary"></i> Refund Processed</li>
                                    <li><i class="fas fa-clock text-muted"></i> Payment Timeout</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- WooCommerce Blocks Section -->
        <div class="card mb-4 blocks-section">
            <div class="card-body">
                <div class="section-header mb-3">
                    <h4 class="card-title"><i class="fab fa-react text-primary me-2"></i>@lang('WooCommerce Blocks Integration')</h4>
                    <p class="card-text">@lang('Modern Gutenberg checkout with React-based payment UI')</p>
                </div>
                
                <div class="blocks-features">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="blocks-feature">
                                <h6><i class="fas fa-ruler-combined me-2"></i>@lang('Responsive Design')</h6>
                                <p>Compact, mobile-optimized payment interface that adapts to any screen size.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="blocks-feature">
                                <h6><i class="fas fa-paint-brush me-2"></i>@lang('Dynamic Branding')</h6>
                                <p>Automatically fetches and displays your {{ setting('site_title') }} branding and logos.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="blocks-feature">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-shield-alt text-warning me-2"></i>
                                    <h6 class="mb-0">@lang('Security Indicators')</h6>
                                </div>
                                <p>Clear SSL and security badges to build customer trust during checkout.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="blocks-feature">
                                <h6><i class="fas fa-vial me-2"></i>@lang('Test Mode Support')</h6>
                                <p>Clear sandbox indicators for testing without affecting live transactions.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Production Checklist -->
        <div class="card mb-4 checklist-section">
            <div class="card-body">
                <div class="section-header mb-3">
                    <h4 class="card-title"><i class="fas fa-clipboard-check text-success me-2"></i>@lang('Production Deployment Checklist')</h4>
                    <p class="card-text">@lang('Ensure everything is configured correctly before going live')</p>
                </div>
                
                <div class="checklist-container">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-tools me-2"></i>@lang('Technical Requirements')</h6>
                            <div class="checklist-item">
                                <input type="checkbox" id="ssl-certificate">
                                <label for="ssl-certificate"><i class="fas fa-check me-2"></i>@lang('Valid SSL certificate installed')</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="php-version">
                                <label for="php-version"><i class="fas fa-check me-2"></i>@lang('PHP 8.1+ running (8.2+ recommended)')</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="woo-version">
                                <label for="woo-version"><i class="fas fa-check me-2"></i>@lang('WooCommerce 6.0+ active')</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="plugin-latest">
                                <label for="plugin-latest"><i class="fas fa-check me-2"></i>@lang(':site plugin v2.8.0 installed', ['site' => setting('site_title')])</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-cog me-2"></i>@lang('API Configuration')</h6>
                            <div class="checklist-item">
                                <input type="checkbox" id="api-credentials">
                                <label for="api-credentials"><i class="fas fa-check me-2"></i>@lang('Production API credentials configured')</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="webhook-setup">
                                <label for="webhook-setup"><i class="fas fa-check me-2"></i>@lang('Webhook URL configured in :site', ['site' => setting('site_title')])</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="webhook-secret">
                                <label for="webhook-secret"><i class="fas fa-check me-2"></i>@lang('Webhook secret key configured')</label>
                            </div>
                            <div class="checklist-item">
                                <input type="checkbox" id="test-payments">
                                <label for="test-payments"><i class="fas fa-check me-2"></i>@lang('Test payments successful (sandbox mode)')</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="final-checks mt-4">
                        <h6><i class="fas fa-clipboard-check me-2"></i>@lang('Final Verification')</h6>
                        <div class="checklist-item">
                            <input type="checkbox" id="blocks-checkout">
                            <label for="blocks-checkout"><i class="fas fa-check me-2"></i>@lang('WooCommerce Blocks checkout tested')</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="webhook-response">
                            <label for="webhook-response"><i class="fas fa-check me-2"></i>@lang('Webhook responses updating order status')</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="backup-created">
                            <label for="backup-created"><i class="fas fa-check me-2"></i>@lang('Complete site backup created')</label>
                        </div>
                        <div class="checklist-item">
                            <input type="checkbox" id="test-mode-off">
                            <label for="test-mode-off"><i class="fas fa-check me-2"></i>@lang('Test mode disabled for live transactions')</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Troubleshooting Section -->
        <div class="card mb-4 troubleshooting-section">
            <div class="card-body">
                <div class="section-header mb-3">
                    <h4 class="card-title"><i class="fas fa-wrench text-warning me-2"></i>@lang('Troubleshooting')</h4>
                    <p class="card-text">@lang('Common issues and solutions for :site WooCommerce integration', ['site' => setting('site_title')])</p>
                </div>
                
                <div class="troubleshooting-items">
                    <div class="troubleshoot-item">
                        <div class="issue-title">
                            <h6><i class="fas fa-exclamation-triangle text-warning me-2"></i>@lang('Payment method not showing in checkout')</h6>
                        </div>
                        <div class="issue-solution">
                            <p><strong>Solutions:</strong></p>
                            <ul>
                                <li>Verify plugin is activated and enabled in WooCommerce → Settings → Payments</li>
                                <li>Clear browser cache and WooCommerce cache</li>
                                <li>Check if API credentials are correctly configured</li>
                                <li>Ensure SSL certificate is properly installed</li>
                            </ul>
                        </div>
                    </div>

                    <div class="troubleshoot-item">
                        <div class="issue-title">
                            <h6><i class="fas fa-times-circle text-danger me-2"></i>@lang('401 Unauthorized API errors')</h6>
                        </div>
                        <div class="issue-solution">
                            <p><strong>Solutions:</strong></p>
                            <ul>
                                <li>Verify Merchant ID and API Key are correct</li>
                                <li>Ensure environment (sandbox/production) matches your credentials</li>
                                <li>Check that all required headers are being sent</li>
                                <li>Contact {{ setting('site_title') }} support to verify account status</li>
                            </ul>
                        </div>
                    </div>

                    <div class="troubleshoot-item">
                        <div class="issue-title">
                            <h6><i class="fas fa-clock text-info me-2"></i>@lang('Orders not updating after payment')</h6>
                        </div>
                        <div class="issue-solution">
                            <p><strong>Solutions:</strong></p>
                            <ul>
                                <li>Verify webhook URL is configured in {{ setting('site_title') }} dashboard</li>
                                <li>Check webhook secret key matches plugin configuration</li>
                                <li>Review WordPress error logs for webhook processing errors</li>
                                <li>Test webhook delivery using {{ setting('site_title') }} dashboard tools</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>