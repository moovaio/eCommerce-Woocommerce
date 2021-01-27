<?php

namespace Ecomerciar\Moova\Orders;

use Ecomerciar\Moova\Helper\Helper;
use Ecomerciar\Moova\Sdk\MoovaSdk;

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
            'ajax_nonce' => wp_create_nonce('wc-moova'),
            'order_id' => $post->ID,
            'text_shipping_label_current' => __('Creating shipping label...', 'wc-moova'),
            'text_shipping_label_completed' => __('Shipping label created', 'wc-moova'),
            'text_shipping_label_name' => __('Generate shipping label', 'wc-moova'),
            'text_order_current' => __('Processing order...', 'wc-moova'),
            'text_order_completed' => __('Order processed', 'wc-moova'),
            'text_order_name' => __('Process order', 'wc-moova'),
            'text_order_status_current' => __('Updating order status...', 'wc-moova'),
            'text_order_status_completed' => __('Order updated', 'wc-moova'),
            'text_order_status_name' => __('Mark order as ready to be shipped', 'wc-moova'),
            'text_error' => __('There was an error, please try again', 'wc-moova')
        ]);
        $shipping_method = Helper::get_shipping_method($order);
        if (!$shipping_method) {
            return;
        }

        if ($shipping_method->get_meta('tracking_number')) {
            $tracking_number = $shipping_method->get_meta('tracking_number');
            if (!empty($tracking_number)) {

                // Tracking number
                preg_match('/([a-zA-Z0-9]+)-/', $tracking_number, $matches);
                $tracking_number = $matches[1];
                printf(__('The order has been processed, tracking number: <strong>%s</strong>', 'wc-moova'), $tracking_number);

                // Tracking URL
                if (Helper::get_option('environment') === 'prod') {
                    $tracking_url = 'https://dashboard.moova.io/external?id=' . $tracking_number;
                } else {
                    $tracking_url = 'https://dev.moova.io/external?id=' . $tracking_number;
                }
                echo '<a class="button-primary" style="display:block;margin:10px 0;" href="' . $tracking_url . '" target="_blank">' . __('Track order', 'wc-moova') . '</a>';

                // Label URL
                $label_url = $shipping_method->get_meta('shipping_label');
                if (empty($label_url)) {
                    echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" data-action="generate_order_shipping_label">' . __('Generate shipping label', 'wc-moova') . '</a>';
                } else {
                    echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" href="' . $label_url . '">' . __('View shipping label', 'wc-moova') . '</a>';
                }

                // Update status
                $moova_sdk = new MoovaSdk();
                $moova_status = $moova_sdk->get_order_status($tracking_number);
                if (trim(strtoupper($moova_status)) === 'DRAFT') {
                    echo '<a class="button-primary" style="display:block;margin:10px 0;" data-action="change_order_status" data-to-status="ready">' . __('Mark order as ready to be shipped', 'wc-moova') . '</a>';
                }
            } else {
                echo __('The order is not processed yet', 'wc-moova');
                if ($config_status === '0') {
                    echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" data-action="process_order">' . __('Process order', 'wc-moova') . '</a>';
                }
            }
        }
    }
}
