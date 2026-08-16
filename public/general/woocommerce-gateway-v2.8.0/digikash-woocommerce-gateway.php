<?php

/**
 * Plugin Name: Hosted Checkout Gateway for WooCommerce
 * Description: Marketplace-ready hosted checkout gateway for WooCommerce.
 * Version: 3.0.0
 * Author: Coevs
 */
if (! defined('ABSPATH')) {
    exit;
}

define('DKWC_PLUGIN_FILE', __FILE__);
define('DKWC_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('DKWC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DKWC_PLUGIN_VERSION', '3.0.0');

add_action('plugins_loaded', 'dkwc_gateway_init', 11);

function dkwc_gateway_init(): void
{
    if (! class_exists('WC_Payment_Gateway')) {
        return;
    }

    require_once DKWC_PLUGIN_PATH.'includes/class-wc-gateway-digikash.php';

    add_filter('woocommerce_payment_gateways', 'dkwc_register_gateway');
}

function dkwc_register_gateway(array $gateways): array
{
    $gateways[] = 'WC_Gateway_DigiKash';

    return $gateways;
}

add_action('woocommerce_blocks_loaded', 'dkwc_register_blocks_support');

function dkwc_register_blocks_support(): void
{
    if (! class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
        return;
    }

    require_once DKWC_PLUGIN_PATH.'includes/class-wc-gateway-digikash.php';
    require_once DKWC_PLUGIN_PATH.'includes/class-wc-gateway-digikash-blocks.php';

    add_action('woocommerce_blocks_payment_method_type_registration', function ($payment_method_registry): void {
        $payment_method_registry->register(new WC_Gateway_DigiKash_Blocks);
    });
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'dkwc_action_links');

function dkwc_action_links(array $links): array
{
    $settingsLink = '<a href="'.esc_url(admin_url('admin.php?page=wc-settings&tab=checkout&section=digikash')).'">'.esc_html__('Settings', 'woocommerce').'</a>';
    array_unshift($links, $settingsLink);

    return $links;
}
