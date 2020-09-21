<?php

defined('ABSPATH') || exit;

// --- Init Hooks
add_action('admin_notices', ['Ecomerciar\Moova\Helper\Helper', 'check_notices']);

// --- Settings
add_filter('plugin_action_links_' . plugin_basename(WCMoova::MAIN_FILE), ['WCMoova', 'create_settings_link']);
add_action('admin_enqueue_scripts', ['Ecomerciar\Moova\Helper\Helper', 'add_assets_files']);

// --- Initiate fields in pages
add_action('admin_init', ['\Ecomerciar\Moova\Settings\GeneralSettings\GeneralSettingsPage', 'init_settings']);
add_action('admin_init', ['\Ecomerciar\Moova\Settings\Mapping\MappingPage', 'init_mapping']);


// --- Shipment Method
add_filter('woocommerce_shipping_methods', ['WCMoova', 'add_shipping_method']);
add_filter('woocommerce_cart_shipping_method_full_label', ['WCMoova', 'free_shipping_text'], 10, 2);

// --- Order section
add_action('woocommerce_order_status_changed', ['\Ecomerciar\Moova\Orders\Processor', 'handle_order_status'], 10, 4);
add_action('add_meta_boxes', ['\Ecomerciar\Moova\Orders\Metabox', 'create']);
add_action('wp_ajax_generate_order_shipping_label', ['\Ecomerciar\Moova\Orders\Processor', 'order_create_shipping_label_ajax']);
add_action('wp_ajax_process_order', ['\Ecomerciar\Moova\Orders\Processor', 'process_order_ajax']);
add_action('wp_ajax_change_order_status', ['\Ecomerciar\Moova\Orders\Processor', 'change_order_status']);
add_action('wp_ajax_get_autocomplete_street', ['\Ecomerciar\Moova\Settings\FormAjax', 'autocomplete']);

add_action('woocommerce_saved_order_items', ['\Ecomerciar\Moova\Orders\Processor', 'notifyMoova']);
// --- Tracking
add_shortcode('moova_tracking_form', ['\Ecomerciar\Moova\Orders\TrackingShortcode', 'output']);

// --- Webhook
add_action('woocommerce_api_wc-moova-orders', ['\Ecomerciar\Moova\Orders\Webhooks', 'listener']);

// --- Bulk changes
add_filter('bulk_actions-edit-shop_order', ['\Ecomerciar\Moova\Orders\BulkChanges', 'set_bulk_options'], 20, 1);
add_filter('handle_bulk_actions-edit-shop_order', ['\Ecomerciar\Moova\Orders\BulkChanges', 'create_bulk_shipments'], 10, 3);
add_action('admin_notices', ['\Ecomerciar\Moova\Orders\BulkChanges', 'response_create_bulk_shipments']);

add_filter('handle_bulk_actions-edit-shop_order', ['\Ecomerciar\Moova\Orders\BulkChanges', 'start_bulk_shipments'], 10, 3);
add_action('admin_notices', ['\Ecomerciar\Moova\Orders\BulkChanges', 'response_start_bulk_shipments']);


add_filter('handle_bulk_actions-edit-shop_order', ['\Ecomerciar\Moova\Orders\BulkChanges', 'force_create_bulk_shipments'], 10, 3);
add_action('admin_notices', ['\Ecomerciar\Moova\Orders\BulkChanges', 'response_force_create']);
