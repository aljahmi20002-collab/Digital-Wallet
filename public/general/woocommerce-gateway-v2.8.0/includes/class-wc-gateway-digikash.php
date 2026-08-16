<?php

/**
 * Hosted checkout gateway implementation.
 */
if (! defined('ABSPATH')) {
    exit;
}

class WC_Gateway_DigiKash extends WC_Payment_Gateway
{
    protected string $api_base_url = '';

    protected bool $testmode = false;

    protected bool $debug_mode = false;

    protected string $success_status = 'processing';

    public function __construct()
    {
        $this->id                 = 'digikash';
        $this->icon               = '';
        $this->has_fields         = true;
        $this->method_title       = __('Hosted Checkout Gateway', 'woocommerce');
        $this->method_description = __('Hosted checkout gateway for WooCommerce stores.', 'woocommerce');
        $this->supports           = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title          = (string) $this->get_option('title', __('Secure Payment', 'woocommerce'));
        $this->description    = (string) $this->get_option('description', __('Pay securely using our hosted checkout.', 'woocommerce'));
        $this->api_base_url   = untrailingslashit((string) $this->get_option('api_base_url', ''));
        $this->testmode       = $this->get_option('testmode', 'no')   === 'yes';
        $this->debug_mode     = $this->get_option('debug_mode', 'no') === 'yes';
        $this->success_status = $this->sanitize_success_status((string) $this->get_option('success_status', 'processing'));

        add_action('woocommerce_update_options_payment_gateways_'.$this->id, [$this, 'process_admin_options']);
        add_action('woocommerce_api_'.$this->id.'_payment_webhooks', [$this, 'webhook_handler']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_checkout_assets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    }

    public function init_form_fields(): void
    {
        $webhookUrl = $this->get_webhook_url();

        $this->form_fields = [
            'general_section' => [
                'title'       => __('General Settings', 'woocommerce'),
                'type'        => 'title',
                'description' => __('Configure how the gateway appears to customers and which environment is active.', 'woocommerce'),
            ],
            'enabled' => [
                'title'   => __('Enable gateway', 'woocommerce'),
                'type'    => 'checkbox',
                'label'   => __('Enable this payment method at checkout', 'woocommerce'),
                'default' => 'yes',
            ],
            'testmode' => [
                'title'       => __('Sandbox mode', 'woocommerce'),
                'label'       => __('Use sandbox credentials and webhook secret', 'woocommerce'),
                'type'        => 'checkbox',
                'default'     => 'no',
                'description' => __('Switch between production and sandbox credentials from here.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'debug_mode' => [
                'title'       => __('Debug logging', 'woocommerce'),
                'label'       => __('Write gateway events to the WooCommerce log', 'woocommerce'),
                'type'        => 'checkbox',
                'default'     => 'no',
                'description' => __('Useful while testing webhook delivery and checkout issues.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'api_base_url' => [
                'title'       => __('API base URL', 'woocommerce'),
                'type'        => 'url',
                'description' => __('Base URL of your Laravel merchant payment API.', 'woocommerce'),
                'default'     => 'https://example.com',
                'desc_tip'    => true,
            ],
            'production_section' => [
                'title'       => __('Production Credentials', 'woocommerce'),
                'type'        => 'title',
                'description' => __('Live credentials used when sandbox mode is disabled.', 'woocommerce'),
            ],
            'merchant_id' => [
                'title'       => __('Production merchant key', 'woocommerce'),
                'type'        => 'text',
                'description' => __('Merchant key or account identifier for live payments.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'api_key' => [
                'title'       => __('Production API key', 'woocommerce'),
                'type'        => 'password',
                'description' => __('API key for initiating live payments.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'webhook_secret' => [
                'title'       => __('Production webhook secret', 'woocommerce'),
                'type'        => 'password',
                'description' => __('Secret used to verify live webhook calls.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'sandbox_section' => [
                'title'       => __('Sandbox Credentials', 'woocommerce'),
                'type'        => 'title',
                'description' => __('Test credentials used when sandbox mode is enabled.', 'woocommerce'),
            ],
            'sandbox_merchant_id' => [
                'title'       => __('Sandbox merchant key', 'woocommerce'),
                'type'        => 'text',
                'description' => __('Merchant key for test payments.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'sandbox_api_key' => [
                'title'       => __('Sandbox API key', 'woocommerce'),
                'type'        => 'password',
                'description' => __('API key for sandbox payment initiation.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'sandbox_webhook_secret' => [
                'title'       => __('Sandbox webhook secret', 'woocommerce'),
                'type'        => 'password',
                'description' => __('Secret used to verify sandbox webhook calls.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'branding_section' => [
                'title'       => __('Checkout Branding', 'woocommerce'),
                'type'        => 'title',
                'description' => __('All customer-facing text can be adjusted from here.', 'woocommerce'),
            ],
            'title' => [
                'title'       => __('Payment method title', 'woocommerce'),
                'type'        => 'text',
                'default'     => __('Secure Payment', 'woocommerce'),
                'description' => __('Displayed in the WooCommerce payment method list.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Payment method description', 'woocommerce'),
                'type'        => 'textarea',
                'default'     => __('Pay securely using our hosted checkout.', 'woocommerce'),
                'description' => __('Short helper text shown below the payment method title.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'brand_name' => [
                'title'       => __('Brand label', 'woocommerce'),
                'type'        => 'text',
                'default'     => __('Secure Checkout', 'woocommerce'),
                'description' => __('Main brand label shown inside the checkout card.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'checkout_heading' => [
                'title'       => __('Checkout heading', 'woocommerce'),
                'type'        => 'text',
                'default'     => __('Hosted payment page', 'woocommerce'),
                'description' => __('Primary heading displayed inside the checkout card.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'checkout_notice' => [
                'title'       => __('Checkout notice', 'woocommerce'),
                'type'        => 'textarea',
                'default'     => __('After placing the order, the customer is redirected to the secure hosted payment page to complete the transaction.', 'woocommerce'),
                'description' => __('Secondary copy displayed before the redirect.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'security_badge_text' => [
                'title'       => __('Security badge text', 'woocommerce'),
                'type'        => 'text',
                'default'     => __('Secure', 'woocommerce'),
                'description' => __('Small badge label shown in the customer card.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'feature_badges' => [
                'title'       => __('Feature badges', 'woocommerce'),
                'type'        => 'textarea',
                'default'     => "SSL Protected\nFast Checkout\nMobile Friendly",
                'description' => __('One feature per line. These badges are shown in the classic and block checkout UIs.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'use_remote_branding' => [
                'title'       => __('Remote branding', 'woocommerce'),
                'label'       => __('Load remote site logo and descriptive copy from the Laravel API when available', 'woocommerce'),
                'type'        => 'checkbox',
                'default'     => 'no',
                'description' => __('Keep disabled for a standard experience controlled by WordPress settings.', 'woocommerce'),
                'desc_tip'    => true,
            ],
            'remote_logo_url' => [
                'title'       => __('Remote logo URL', 'woocommerce'),
                'type'        => 'url',
                'description' => __('Direct URL to your brand logo. This will be used on the checkout page if Remote Branding is enabled and the API does not provide a logo.', 'woocommerce'),
                'default'     => '',
                'desc_tip'    => true,
            ],
            'webhook_section' => [
                'title'       => __('Webhook Endpoint', 'woocommerce'),
                'type'        => 'title',
                'description' => sprintf(
                    '%s<br><code>%s</code>',
                    esc_html__('Use this URL as the IPN/webhook callback endpoint in your Laravel payment flow.', 'woocommerce'),
                    esc_html($webhookUrl)
                ),
            ],
            'success_status' => [
                'title'       => __('Successful order status', 'woocommerce'),
                'type'        => 'select',
                'description' => __('Choose which WooCommerce status should be applied after the webhook confirms a successful payment.', 'woocommerce'),
                'default'     => 'processing',
                'options'     => [
                    'processing' => __('Processing (default WooCommerce behaviour)', 'woocommerce'),
                    'completed'  => __('Completed (auto-fulfil digital orders)', 'woocommerce'),
                ],
            ],
        ];
    }

    public function admin_options(): void
    {
        $modeLabel   = $this->testmode ? __('Sandbox', 'woocommerce') : __('Production', 'woocommerce');
        $webhookUrl  = $this->get_webhook_url();
        $activeKey   = $this->get_active_merchant_key();
        $brandName   = $this->get_brand_label();
        $statusClass = $this->testmode ? 'dkwc-pill dkwc-pill--warning' : 'dkwc-pill dkwc-pill--success';

        echo '<div class="dkwc-admin-page">';
        echo '<div class="dkwc-admin-hero">';
        echo '<div>';
        echo '<h2>'.esc_html__('Hosted Checkout Gateway', 'woocommerce').'</h2>';
        echo '<p>'.esc_html__('Marketplace-ready settings for connecting WooCommerce to your Laravel payment checkout.', 'woocommerce').'</p>';
        echo '</div>';
        echo '<div class="dkwc-admin-hero__meta">';
        echo '<span class="'.esc_attr($statusClass).'">'.esc_html($modeLabel).'</span>';
        echo '<span class="dkwc-pill">'.esc_html($brandName).'</span>';
        echo '</div>';
        echo '</div>';

        echo '<div class="dkwc-admin-grid">';
        echo '<div class="dkwc-admin-card">';
        echo '<h3>'.esc_html__('Connection Summary', 'woocommerce').'</h3>';
        echo '<ul class="dkwc-admin-list">';
        echo '<li><strong>'.esc_html__('API Base URL:', 'woocommerce').'</strong> '.esc_html($this->api_base_url ?: '—').'</li>';
        echo '<li><strong>'.esc_html__('Active Merchant Key:', 'woocommerce').'</strong> '.esc_html($activeKey ?: '—').'</li>';
        echo '<li><strong>'.esc_html__('Webhook URL:', 'woocommerce').'</strong> <code>'.esc_html($webhookUrl).'</code></li>';
        echo '</ul>';
        echo '</div>';

        echo '<div class="dkwc-admin-card">';
        echo '<h3>'.esc_html__('Setup Checklist', 'woocommerce').'</h3>';
        echo '<ol class="dkwc-admin-list dkwc-admin-list--ordered">';
        echo '<li>'.esc_html__('Enter the API base URL of your Laravel application.', 'woocommerce').'</li>';
        echo '<li>'.esc_html__('Save both production and sandbox credentials so you can switch modes instantly.', 'woocommerce').'</li>';
        echo '<li>'.esc_html__('Copy the webhook URL into your merchant payment/IPN configuration.', 'woocommerce').'</li>';
        echo '<li>'.esc_html__('Customize the brand label, heading, and checkout copy for a professional finish.', 'woocommerce').'</li>';
        echo '</ol>';
        echo '</div>';
        echo '</div>';

        echo '<table class="form-table">';
        $this->generate_settings_html();
        echo '</table>';
        echo '</div>';
    }

    public function enqueue_checkout_assets(): void
    {
        if (! is_checkout() && ! is_cart()) {
            return;
        }

        wp_enqueue_style(
            'dkwc-checkout',
            DKWC_PLUGIN_URL.'assets/css/checkout.css',
            [],
            DKWC_PLUGIN_VERSION
        );
    }

    public function enqueue_admin_assets(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (! $screen || $screen->id !== 'woocommerce_page_wc-settings') {
            return;
        }

        wp_enqueue_style(
            'dkwc-admin',
            DKWC_PLUGIN_URL.'assets/css/admin.css',
            [],
            DKWC_PLUGIN_VERSION
        );
    }

    public function is_available(): bool
    {
        if ($this->get_option('enabled', 'no') !== 'yes') {
            return false;
        }

        if ($this->api_base_url === '' || $this->get_active_merchant_key() === '' || $this->get_active_api_key() === '') {
            return false;
        }

        return parent::is_available();
    }

    protected function get_success_status(): string
    {
        return $this->success_status;
    }

    protected function sanitize_success_status(string $status): string
    {
        return in_array($status, ['processing', 'completed'], true) ? $status : 'processing';
    }

    public function payment_fields(): void
    {
        $profile       = $this->get_site_info();
        $brandName     = (string) ($profile['brand_name'] ?? $this->get_brand_label());
        $heading       = (string) ($profile['checkout_heading'] ?? $this->get_option('checkout_heading', ''));
        $notice        = (string) ($profile['checkout_notice'] ?? $this->get_option('checkout_notice', ''));
        $badge         = (string) ($profile['security_badge_text'] ?? $this->get_option('security_badge_text', ''));
        $featureBadges = $profile['feature_badges'] ?? $this->get_feature_badges();
        $logoUrl       = ! empty($profile['site_logo'])
            ? (string) $profile['site_logo']
            : (string) $this->get_option('remote_logo_url', '');
        $logoUrl     = $logoUrl !== '' ? esc_url($logoUrl) : '';
        $sandboxCopy = (string) ($profile['sandbox_message'] ?? __('Sandbox mode is active. No real payment will be collected.', 'woocommerce'));

        echo '<div class="dkwc-checkout-card">';
        echo '<div class="dkwc-checkout-card__header">';
        echo '<div class="dkwc-checkout-card__brand">';

        if ($logoUrl !== '') {
            echo '<span class="dkwc-checkout-card__logo"><img src="'.esc_url($logoUrl).'" alt="'.esc_attr($brandName).'"></span>';
        }

        echo '<div>';
        echo '<strong class="dkwc-checkout-card__title">'.esc_html($brandName).'</strong>';
        echo '<div class="dkwc-checkout-card__subtitle">'.esc_html($heading).'</div>';
        echo '</div>';
        echo '</div>';
        echo '<span class="dkwc-checkout-card__badge">'.esc_html($badge).'</span>';
        echo '</div>';

        if ($this->description !== '') {
            echo '<p class="dkwc-checkout-card__description">'.esc_html($this->description).'</p>';
        }

        if ($notice !== '') {
            echo '<p class="dkwc-checkout-card__notice">'.esc_html($notice).'</p>';
        }

        if (! empty($featureBadges)) {
            echo '<div class="dkwc-checkout-card__features">';
            foreach ($featureBadges as $featureBadge) {
                echo '<span class="dkwc-checkout-card__feature">'.esc_html($featureBadge).'</span>';
            }
            echo '</div>';
        }

        if ($this->testmode) {
            echo '<div class="dkwc-checkout-card__alert">'.esc_html($sandboxCopy).'</div>';
        }

        echo '</div>';
    }

    public function process_payment($order_id): array
    {
        $order = wc_get_order($order_id);

        if (! $order instanceof WC_Order) {
            wc_add_notice(__('Order could not be loaded.', 'woocommerce'), 'error');

            return ['result' => 'fail'];
        }

        try {
            $payload  = $this->build_payment_payload($order);
            $response = wp_remote_post($this->api_base_url.'/api/v1/initiate-payment', [
                'method'      => 'POST',
                'timeout'     => 30,
                'data_format' => 'body',
                'headers'     => [
                    'Content-Type'   => 'application/json',
                    'X-Environment'  => $this->get_active_environment(),
                    'X-Merchant-Key' => $this->get_active_merchant_key(),
                    'X-API-Key'      => $this->get_active_api_key(),
                ],
                'body' => wp_json_encode($payload),
            ]);

            if (is_wp_error($response)) {
                $this->log('Initiate payment request failed', ['error' => $response->get_error_message()]);
                wc_add_notice(__('Unable to initiate the hosted payment right now. Please try again.', 'woocommerce'), 'error');

                return ['result' => 'fail'];
            }

            $responseCode = (int) wp_remote_retrieve_response_code($response);
            $responseBody = (string) wp_remote_retrieve_body($response);
            $responseData = json_decode($responseBody, true);

            if ($responseCode !== 200 || ! is_array($responseData) || empty($responseData['payment_url'])) {
                $message = is_array($responseData) && ! empty($responseData['error'])
                    ? (string) $responseData['error']
                    : __('The payment API returned an invalid response.', 'woocommerce');

                $this->log('Initiate payment response was invalid', [
                    'code'     => $responseCode,
                    'body'     => $responseBody,
                    'order_id' => $order->get_id(),
                ]);

                wc_add_notice($message, 'error');

                return ['result' => 'fail'];
            }

            $paymentUrl = esc_url_raw((string) $responseData['payment_url']);
            if (! filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
                wc_add_notice(__('Received an invalid redirect URL from the payment API.', 'woocommerce'), 'error');

                return ['result' => 'fail'];
            }

            $order->update_status('pending', __('Awaiting hosted payment confirmation.', 'woocommerce'));
            $order->update_meta_data('_dkwc_reference', (string) $payload['ref_trx']);
            $order->update_meta_data('_dkwc_order_number', (string) $payload['order_number']);
            $order->update_meta_data('_dkwc_payment_url', $paymentUrl);
            $order->update_meta_data('_dkwc_environment', $this->get_active_environment());
            $order->save();

            wc_maybe_reduce_stock_levels($order->get_id());

            if (WC()->cart) {
                WC()->cart->empty_cart();
            }

            $this->log('Initiated payment successfully', [
                'order_id'     => $order->get_id(),
                'reference'    => $payload['ref_trx'],
                'environment'  => $this->get_active_environment(),
                'redirect_url' => $paymentUrl,
            ]);

            return [
                'result'   => 'success',
                'redirect' => $paymentUrl,
            ];
        } catch (Exception $exception) {
            $this->log('Unexpected exception during payment initiation', [
                'order_id' => $order->get_id(),
                'error'    => $exception->getMessage(),
            ]);

            wc_add_notice(__('Unable to start the payment process. Please try again.', 'woocommerce'), 'error');

            return ['result' => 'fail'];
        }
    }

    public function webhook_handler(): void
    {
        $rawPayload = file_get_contents('php://input');
        $rawPayload = is_string($rawPayload) ? $rawPayload : '';

        $payload = json_decode($rawPayload, true);
        if (! is_array($payload)) {
            $this->respond_to_webhook(400, 'Invalid JSON payload');
        }

        $webhookData = is_array($payload['data'] ?? null) ? $payload['data'] : [];
        $environment = $this->resolve_webhook_environment($payload, $webhookData);
        $status      = strtolower((string) ($payload['status'] ?? ''));
        $message     = (string) ($payload['message'] ?? '');
        $reference   = (string) ($webhookData['ref_trx'] ?? '');
        $secret      = $this->get_webhook_secret_for_environment($environment);

        if ($reference === '' || $status === '') {
            $this->respond_to_webhook(400, 'Missing webhook reference or status');
        }

        if ($secret !== '' && ! $this->is_valid_webhook_signature($rawPayload, $secret)) {
            $this->log('Rejected webhook because signature verification failed', [
                'reference'   => $reference,
                'environment' => $environment,
            ]);

            $this->respond_to_webhook(401, 'Invalid signature');
        }

        $order = $this->find_order_by_reference($reference);
        if (! $order instanceof WC_Order) {
            $this->log('Webhook order resolution failed', ['reference' => $reference]);
            $this->respond_to_webhook(404, 'Order not found');
        }

        $order->update_meta_data('_dkwc_last_webhook', [
            'status'      => $status,
            'message'     => $message,
            'environment' => $environment,
            'received_at' => current_time('mysql'),
            'payload'     => $payload,
        ]);

        if (! empty($webhookData['trx_id'])) {
            $order->set_transaction_id((string) $webhookData['trx_id']);
        }

        switch ($status) {
            case 'completed':
            case 'success':
            case 'paid':
                if (! $order->is_paid()) {
                    $transactionId = ! empty($webhookData['trx_id'])
                        ? (string) $webhookData['trx_id']
                        : $reference;

                    $order->payment_complete($transactionId);
                }

                $order->add_order_note($this->build_order_note(__('Payment confirmed via webhook.', 'woocommerce'), $message, $environment));

                if ($this->get_success_status() === 'completed' && $order->get_status() !== 'completed') {
                    $order->update_status('completed', __('Order auto-completed because payment is fully settled.', 'woocommerce'));
                }
                break;

            case 'failed':
            case 'error':
            case 'declined':
                if (! in_array($order->get_status(), ['failed', 'cancelled', 'refunded', 'completed', 'processing'], true)) {
                    $order->update_status('failed', $this->build_order_note(__('Payment failed.', 'woocommerce'), $message, $environment));
                } else {
                    $order->add_order_note($this->build_order_note(__('Received failed payment update.', 'woocommerce'), $message, $environment));
                }
                break;

            case 'cancelled':
            case 'canceled':
                if (! in_array($order->get_status(), ['cancelled', 'completed', 'processing'], true)) {
                    $order->update_status('cancelled', $this->build_order_note(__('Payment canceled.', 'woocommerce'), $message, $environment));
                } else {
                    $order->add_order_note($this->build_order_note(__('Received canceled payment update.', 'woocommerce'), $message, $environment));
                }
                break;

            case 'pending':
            case 'processing':
            case 'initiated':
                if (! in_array($order->get_status(), ['on-hold', 'processing', 'completed'], true)) {
                    $order->update_status('on-hold', $this->build_order_note(__('Payment is pending confirmation.', 'woocommerce'), $message, $environment));
                } else {
                    $order->add_order_note($this->build_order_note(__('Received pending payment update.', 'woocommerce'), $message, $environment));
                }
                break;

            case 'refunded':
            case 'refund':
                if ($order->get_status() !== 'refunded') {
                    $order->update_status('refunded', $this->build_order_note(__('Payment refunded.', 'woocommerce'), $message, $environment));
                } else {
                    $order->add_order_note($this->build_order_note(__('Received refunded payment update.', 'woocommerce'), $message, $environment));
                }
                break;

            default:
                $order->add_order_note($this->build_order_note(__('Received unknown payment update.', 'woocommerce'), $message ?: $status, $environment));
                break;
        }

        $order->save();

        $this->log('Processed webhook successfully', [
            'order_id'    => $order->get_id(),
            'reference'   => $reference,
            'status'      => $status,
            'environment' => $environment,
        ]);

        $this->respond_to_webhook(200, 'OK');
    }

    public function get_site_info(): array
    {
        $fallback = $this->get_fallback_site_info();

        if ($this->get_option('use_remote_branding', 'no') !== 'yes' || $this->api_base_url === '') {
            return $fallback;
        }

        $cacheKey   = 'dkwc_site_info_'.md5($this->api_base_url.'|'.$this->get_active_environment().'|'.$this->get_active_merchant_key());
        $cachedInfo = get_transient($cacheKey);

        if (is_array($cachedInfo)) {
            return array_merge($fallback, $this->normalize_site_info($cachedInfo));
        }

        $response = wp_remote_get($this->api_base_url.'/api/v1/site-info', [
            'timeout' => 10,
            'headers' => [
                'Content-Type'   => 'application/json',
                'X-Environment'  => $this->get_active_environment(),
                'X-Merchant-Key' => $this->get_active_merchant_key(),
                'X-API-Key'      => $this->get_active_api_key(),
            ],
        ]);

        if (is_wp_error($response)) {
            $this->log('Failed to fetch remote site info', ['error' => $response->get_error_message()]);

            return $fallback;
        }

        $statusCode = (int) wp_remote_retrieve_response_code($response);
        $body       = (string) wp_remote_retrieve_body($response);
        $data       = json_decode($body, true);

        if ($statusCode !== 200 || ! is_array($data)) {
            $this->log('Remote site info returned an invalid response', ['code' => $statusCode, 'body' => $body]);

            return $fallback;
        }

        $normalized = $this->normalize_site_info($data);
        set_transient($cacheKey, $normalized, 3600);

        return array_merge($fallback, $normalized);
    }

    public function get_blocks_payment_method_data(): array
    {
        $profile = $this->get_site_info();

        $siteLogo = ! empty($profile['site_logo']) ? (string) $profile['site_logo'] : (string) $this->get_option('remote_logo_url', '');

        return [
            'title'               => $this->title,
            'description'         => $this->description,
            'supports'            => $this->supports,
            'testmode'            => $this->testmode,
            'brand_name'          => $profile['brand_name']          ?? $this->get_brand_label(),
            'checkout_heading'    => $profile['checkout_heading']    ?? '',
            'checkout_notice'     => $profile['checkout_notice']     ?? '',
            'security_badge_text' => $profile['security_badge_text'] ?? __('Secure', 'woocommerce'),
            'feature_badges'      => $profile['feature_badges']      ?? $this->get_feature_badges(),
            'site_logo'           => $siteLogo !== '' ? esc_url($siteLogo) : '',
            'sandbox_message'     => $profile['sandbox_message'] ?? __('Sandbox mode is active. No real payment will be collected.', 'woocommerce'),
        ];
    }

    protected function build_payment_payload(WC_Order $order): array
    {
        $customerName = trim((string) $order->get_formatted_billing_full_name());
        if ($customerName === '') {
            $customerName = trim((string) $order->get_billing_first_name().' '.$order->get_billing_last_name());
        }

        return [
            'payment_amount'   => (float) $order->get_total(),
            'currency_code'    => (string) $order->get_currency(),
            'ref_trx'          => (string) $order->get_id(),
            'order_number'     => (string) $order->get_order_number(),
            'order_key'        => (string) $order->get_order_key(),
            'description'      => sprintf(__('Order #%s', 'woocommerce'), $order->get_order_number()),
            'success_redirect' => $this->get_return_url($order),
            'cancel_redirect'  => $order->get_cancel_order_url_raw(),
            'ipn_url'          => $this->get_webhook_url(),
            'customer_name'    => $customerName,
            'customer_email'   => (string) $order->get_billing_email(),
        ];
    }

    protected function get_active_environment(): string
    {
        return $this->testmode ? 'sandbox' : 'production';
    }

    protected function get_active_merchant_key(): string
    {
        if ($this->testmode) {
            $sandboxMerchantKey = (string) $this->get_option('sandbox_merchant_id', '');
            if ($sandboxMerchantKey !== '') {
                return $sandboxMerchantKey;
            }
        }

        return (string) $this->get_option('merchant_id', '');
    }

    protected function get_active_api_key(): string
    {
        if ($this->testmode) {
            $sandboxApiKey = (string) $this->get_option('sandbox_api_key', '');
            if ($sandboxApiKey !== '') {
                return $sandboxApiKey;
            }
        }

        return (string) $this->get_option('api_key', '');
    }

    protected function get_webhook_secret_for_environment(string $environment): string
    {
        if ($environment === 'sandbox') {
            $sandboxSecret = (string) $this->get_option('sandbox_webhook_secret', '');
            if ($sandboxSecret !== '') {
                return $sandboxSecret;
            }
        }

        return (string) $this->get_option('webhook_secret', '');
    }

    protected function get_webhook_url(): string
    {
        return home_url('/wc-api/'.$this->id.'_payment_webhooks');
    }

    protected function get_brand_label(): string
    {
        return (string) $this->get_option('brand_name', __('Secure Checkout', 'woocommerce'));
    }

    protected function get_feature_badges(): array
    {
        $rawValue = (string) $this->get_option('feature_badges', '');
        $lines    = preg_split('/\r\n|\r|\n/', $rawValue) ?: [];

        $features = array_values(array_filter(array_map(static function ($line) {
            return trim((string) $line);
        }, $lines)));

        return $features;
    }

    protected function normalize_site_info(array $data): array
    {
        $siteLogo = ! empty($data['site_logo']) ? (string) $data['site_logo'] : (string) $this->get_option('remote_logo_url', '');

        return [
            'brand_name'          => (string) ($data['gateway_name'] ?? $data['site_name'] ?? $this->get_brand_label()),
            'checkout_heading'    => (string) ($data['gateway_name'] ?? $this->get_option('checkout_heading', '')),
            'checkout_notice'     => (string) ($data['gateway_description'] ?? $data['security_message'] ?? $this->get_option('checkout_notice', '')),
            'security_badge_text' => (string) ($data['security_badge_text'] ?? $this->get_option('security_badge_text', __('Secure', 'woocommerce'))),
            'site_logo'           => $siteLogo !== '' ? esc_url($siteLogo) : '',
            'feature_badges'      => array_values(array_filter(array_map(static function ($value) {
                return is_string($value) ? trim($value) : '';
            }, is_array($data['features'] ?? null) ? array_values($data['features']) : []))),
            'sandbox_message' => (string) (($data['environments']['sandbox'] ?? '') ?: __('Sandbox mode is active. No real payment will be collected.', 'woocommerce')),
        ];
    }

    protected function get_fallback_site_info(): array
    {
        $siteLogo = (string) $this->get_option('remote_logo_url', '');

        return [
            'brand_name'          => $this->get_brand_label(),
            'checkout_heading'    => (string) $this->get_option('checkout_heading', __('Hosted payment page', 'woocommerce')),
            'checkout_notice'     => (string) $this->get_option('checkout_notice', __('After placing the order, the customer is redirected to the secure hosted payment page to complete the transaction.', 'woocommerce')),
            'security_badge_text' => (string) $this->get_option('security_badge_text', __('Secure', 'woocommerce')),
            'site_logo'           => $siteLogo !== '' ? esc_url($siteLogo) : '',
            'feature_badges'      => $this->get_feature_badges(),
            'sandbox_message'     => __('Sandbox mode is active. No real payment will be collected.', 'woocommerce'),
        ];
    }

    protected function resolve_webhook_environment(array $payload, array $webhookData): string
    {
        $headerEnvironment = isset($_SERVER['HTTP_X_ENVIRONMENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_X_ENVIRONMENT'])) : '';

        if ($headerEnvironment !== '') {
            return strtolower($headerEnvironment) === 'sandbox' ? 'sandbox' : 'production';
        }

        if (! empty($webhookData['environment'])) {
            return strtolower((string) $webhookData['environment']) === 'sandbox' ? 'sandbox' : 'production';
        }

        if (! empty($webhookData['is_sandbox'])) {
            return 'sandbox';
        }

        if (! empty($payload['environment'])) {
            return strtolower((string) $payload['environment']) === 'sandbox' ? 'sandbox' : 'production';
        }

        return 'production';
    }

    protected function is_valid_webhook_signature(string $rawPayload, string $secret): bool
    {
        $receivedSignature = isset($_SERVER['HTTP_X_SIGNATURE']) ? trim((string) wp_unslash($_SERVER['HTTP_X_SIGNATURE'])) : '';
        if ($receivedSignature === '') {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $rawPayload, $secret);

        if (hash_equals($expectedSignature, $receivedSignature)) {
            return true;
        }

        $prefixedExpected = 'sha256='.$expectedSignature;

        return hash_equals($prefixedExpected, $receivedSignature);
    }

    protected function find_order_by_reference(string $reference): ?WC_Order
    {
        if (ctype_digit($reference)) {
            $order = wc_get_order((int) $reference);
            if ($order instanceof WC_Order) {
                return $order;
            }
        }

        $orders = wc_get_orders([
            'limit'      => 1,
            'meta_key'   => '_dkwc_reference',
            'meta_value' => $reference,
            'return'     => 'objects',
        ]);

        if (! empty($orders) && $orders[0] instanceof WC_Order) {
            return $orders[0];
        }

        $orders = wc_get_orders([
            'limit'      => 1,
            'meta_key'   => '_dkwc_order_number',
            'meta_value' => $reference,
            'return'     => 'objects',
        ]);

        if (! empty($orders) && $orders[0] instanceof WC_Order) {
            return $orders[0];
        }

        return null;
    }

    protected function build_order_note(string $title, string $message, string $environment): string
    {
        $parts = [$title];

        if ($message !== '') {
            $parts[] = sprintf(__('Message: %s', 'woocommerce'), $message);
        }

        $parts[] = sprintf(__('Environment: %s', 'woocommerce'), ucfirst($environment));

        return implode(' ', $parts);
    }

    protected function respond_to_webhook(int $statusCode, string $message): void
    {
        status_header($statusCode);
        exit($message);
    }

    protected function log(string $message, array $context = []): void
    {
        if (! $this->debug_mode) {
            return;
        }

        $logger = wc_get_logger();
        $logger->info($message.' '.wp_json_encode($context), ['source' => 'dkwc-gateway']);
    }
}
