<?php

defined('ABSPATH') || exit;

// --- Init Hooks
add_action('admin_notices', ['Moova\Helper\Helper', 'check_notices']);

// --- Settings
add_filter('plugin_action_links_' . plugin_basename(WCMoova::MAIN_FILE), ['WCMoova', 'create_settings_link']);
add_action('admin_enqueue_scripts', ['Moova\Helper\Helper', 'add_assets_files']);

// --- Initiate fields in pages
add_action('admin_init', ['\Moova\Settings\GeneralSettings\GeneralSettingsPage', 'init_settings']);
add_action('admin_init', ['\Moova\Settings\Mapping\MappingPage', 'init_mapping']);


// --- Shipment Method
add_filter('woocommerce_shipping_methods', ['WCMoova', 'add_shipping_method']);
add_filter('woocommerce_cart_shipping_method_full_label', ['WCMoova', 'customize_label_shipping_checkout'], 10, 2);

// --- Order section
add_action('woocommerce_order_status_changed', ['\Moova\Orders\Processor', 'handle_order_status'], 10, 4);
add_action('add_meta_boxes', ['\Moova\Orders\Metabox', 'create']);
add_action('wp_ajax_generate_order_shipping_label', ['\Moova\Orders\Processor', 'order_create_shipping_label_ajax']);
add_action('wp_ajax_change_order_status', ['\Moova\Orders\Processor', 'change_order_status']);
add_action('wp_ajax_get_autocomplete_street', ['\Moova\Settings\FormAjax', 'autocomplete']);
add_action('wp_ajax_set_rate_minimum_shippings', ['\Moova\Settings\FormAjax', 'rate']);

add_action('woocommerce_saved_order_items', ['\Moova\Orders\Processor', 'notifyMoova']);
// --- Tracking
add_shortcode('moova_tracking_form', ['\Moova\Orders\TrackingShortcode', 'output']);

// --- Webhook
add_action('woocommerce_api_wc-moova-orders', ['\Moova\Orders\Webhooks', 'listener']);

// --- Bulk changes
add_filter('bulk_actions-edit-shop_order', ['\Moova\Orders\BulkChanges', 'set_bulk_options'], 20, 1);
add_filter("bulk_actions-woocommerce_page_wc-orders",['\Moova\Orders\BulkChanges', 'set_bulk_options'], 20, 1); 

add_filter('handle_bulk_actions-edit-shop_order', ['\Moova\Orders\BulkChanges', 'start_bulk_shipments'], 10, 3);
add_action('admin_notices', ['\Moova\Orders\BulkChanges', 'response_start_bulk_shipments']);


add_filter('handle_bulk_actions-edit-shop_order', ['\Moova\Orders\BulkChanges', 'force_create_bulk_shipments'], 10, 3);
add_action('admin_notices', ['\Moova\Orders\BulkChanges', 'response_force_create']);


add_filter('handle_bulk_actions-edit-shop_order', ['\Moova\Orders\BulkChanges', 'force_latest_status_shipments'], 10, 3);
add_action('admin_notices', ['\Moova\Orders\BulkChanges', 'response_force_latest_status']);

// ---- Ask for review
add_action('admin_notices', ['WCMoova', 'qualify_application']);

// ---- Edit de the checkout
add_filter('woocommerce_default_address_fields', ['\Moova\Checkout\Checkout', 'moova_override_default_address_fields']);
add_action('wp_ajax_moova_custom_fields', ['\Moova\Checkout\Checkout', 'get_ajax_moova_custom_fields']);
add_action('wp_ajax_nopriv_moova_custom_fields', ['\Moova\Checkout\Checkout', 'get_ajax_moova_custom_fields']);
add_action('woocommerce_checkout_update_order_review', ['\Moova\Checkout\Checkout', 'refresh_shipping_methods'], 10, 1);
add_filter('woocommerce_admin_billing_fields', ['\Moova\Checkout\Checkout', 'moova_override_default_address_fields']);
add_filter('woocommerce_admin_shipping_fields', ['\Moova\Checkout\Checkout', 'moova_override_default_address_fields']);
add_action('woocommerce_after_checkout_form', ['\Moova\Checkout\Checkout', 'register_scripts']);
add_filter('woocommerce_thankyou_order_received_text', ['\Moova\Checkout\Checkout', 'thank_you_message'], 10, 2);
#add_action('woocommerce_thankyou', ['\Moova\Checkout\Checkout', 'custom_items'], 1);
