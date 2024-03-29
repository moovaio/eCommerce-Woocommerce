<?php

namespace Moova\Orders;

use Moova\Helper\Helper;
use Moova\Sdk\MoovaSdk;

defined('ABSPATH') || exit;

class Metabox
{
    public static function create()
    {
        $order_types = wc_get_order_types('order-meta-boxes');
        foreach ($order_types as $order_type) {
            add_meta_box(
                'moova_metabox',           // Unique ID
                'Moova',  // Box title
                [__CLASS__, 'content'],  // Content callback, must be of type callable
                $order_type,
                'side',
                'default'
            );
        }
    }

    public static function content($post, $metabox)
    {
        $order = wc_get_order($post->ID);
        if (empty($order)) {
            return false;
        }
        wp_enqueue_script('wc-moova-orders-js');
        wp_localize_script('wc-moova-orders-js', 'wc_moova_settings', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'ajax_nonce' => wp_create_nonce('moova-for-woocommerce'),
            'order_id' => $post->ID,
            'text_shipping_label_current' => __('Creating shipping label...', 'moova-for-woocommerce'),
            'text_shipping_label_completed' => __('Shipping label created', 'moova-for-woocommerce'),
            'text_shipping_label_name' => __('Generate shipping label', 'moova-for-woocommerce'),
            'text_order_current' => __('Processing order...', 'moova-for-woocommerce'),
            'text_order_completed' => __('Order processed', 'moova-for-woocommerce'),
            'text_order_name' => __('Process order', 'moova-for-woocommerce'),
            'text_order_status_current' => __('Updating order status...', 'moova-for-woocommerce'),
            'text_order_status_completed' => __('Order updated', 'moova-for-woocommerce'),
            'text_order_status_name' => __('Mark order as ready to be shipped', 'moova-for-woocommerce'),
            'text_error' => __('There was an error, please try again', 'moova-for-woocommerce')
        ]);
        $shipping_method = Helper::get_shipping_method($order);
        if (!$shipping_method) {
            return;
        }

        $container_moova = '';
        if (!empty($shipping_method->get_meta('tracking_number'))) {
            $tracking_number = $shipping_method->get_meta('tracking_number');
            $tracking_url = $shipping_method->get_meta('tracking_url');
            // Tracking number
            preg_match('/([a-zA-Z0-9]+)-/', $tracking_number, $matches);
            $tracking_number = $matches[1];
            printf(__('The order has been processed, tracking number: <strong>%s</strong>', 'moova-for-woocommerce'), $tracking_number);

            $container_moova = '<a class="button-primary" style="display:block; margin:10px 0;" href="' . $tracking_url . '" target="_blank">' . __('Track order', 'moova-for-woocommerce') . '</a>';

            // Label URL
            $label_url = $shipping_method->get_meta('shipping_label');
            if (empty($label_url)) {
                $container_moova .= '<a class="button-primary" style="display:block; margin:10px 0;" target="_blank" data-action="generate_order_shipping_label">' . __('Generate shipping label', 'moova-for-woocommerce') . '</a>';
            } else {
                $container_moova .= '<a class="button-primary" style="display:block; margin:10px 0;" target="_blank" href="' . $label_url . '">' . __('View shipping label', 'moova-for-woocommerce') . '</a>';
            }

            // Update status
            $moova_sdk = new MoovaSdk();
            $moova_status = $moova_sdk->get_order_status($tracking_number);
            if (trim(strtoupper($moova_status)) === 'DRAFT') {
                $container_moova .= '<a class="button-primary" style="display:block;margin:10px 0;" data-action="change_order_status" data-to-status="ready">' . __('Mark order as ready to be shipped', 'moova-for-woocommerce') . '</a>';
            }
        } else {
            $container_moova .= __('The order is not processed yet', 'moova-for-woocommerce');
            if ($config_status === '0') {
                $container_moova .= '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" data-action="process_order">' . __('Process order', 'moova-for-woocommerce') . '</a>';
            }
        }

        echo $container_moova;
    }
}
