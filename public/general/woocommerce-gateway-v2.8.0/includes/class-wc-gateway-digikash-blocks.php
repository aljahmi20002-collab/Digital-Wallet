<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

/**
 * Hosted checkout blocks integration for WooCommerce.
 */
if (! defined('ABSPATH')) {
    exit;
}

final class WC_Gateway_DigiKash_Blocks extends AbstractPaymentMethodType
{
    protected $name = 'digikash';

    public function initialize(): void
    {
        $this->settings = get_option('woocommerce_digikash_settings', []);
    }

    public function is_active(): bool
    {
        return ! empty($this->settings['enabled']) && $this->settings['enabled'] === 'yes';
    }

    public function get_payment_method_script_handles(): array
    {
        wp_register_script(
            'wc-dkwc-blocks-integration',
            plugin_dir_url(__FILE__).'../assets/js/checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            DKWC_PLUGIN_VERSION,
            true
        );

        wp_register_style(
            'wc-dkwc-blocks-style',
            plugin_dir_url(__FILE__).'../assets/css/checkout.css',
            [],
            DKWC_PLUGIN_VERSION
        );

        wp_enqueue_style('wc-dkwc-blocks-style');

        return ['wc-dkwc-blocks-integration'];
    }

    public function get_payment_method_data(): array
    {
        $gateway = new WC_Gateway_DigiKash;

        return $gateway->get_blocks_payment_method_data();
    }
}
