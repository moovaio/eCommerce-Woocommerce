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
            'order_id' => $post->ID
        ]);
        $shipping_methods = $order->get_shipping_methods();
        if (empty($shipping_methods)) {
            return;
        }
        $shipping_method = array_shift($shipping_methods);
        if ($shipping_method->get_method_id() === 'moova') {
            $config_status = Helper::get_option('status_processing');
            $config_status = str_replace('wc-', '', $config_status);
            if ($order->has_status($config_status) || $config_status === '0') {
                $tracking_number = $shipping_method->get_meta('tracking_number');
                if (!empty($tracking_number)) {

                    // Tracking number
                    preg_match('/([a-zA-Z0-9]+)-/', $tracking_number, $matches);
                    $tracking_number = $matches[1];
                    echo 'El pedido ha sido procesado. Número de rastreo: <strong>' . $tracking_number . '</strong>';

                    // Tracking URL
                    if (Helper::get_option('environment') === 'prod') {
                        $tracking_url = 'https://dashboard.moova.io/external?id=' . $tracking_number;
                    } else {
                        $tracking_url = 'https://dev.moova.io/external?id=' . $tracking_number;
                    }
                    echo '<a class="button-primary" style="display:block;margin:10px 0;" href="' . $tracking_url . '" target="_blank">Rastrear pedido</a>';

                    // Label URL
                    $label_url = $shipping_method->get_meta('shipping_label');
                    if (empty($label_url)) {
                        echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" data-action="generate_order_shipping_label">Generar etiqueta</a>';
                    } else {
                        echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" href="' . $label_url . '">Ver Etiqueta</a>';
                    }

                    // Update status
                    $moova_sdk = new MoovaSdk();
                    $moova_status = $moova_sdk->get_order_status($tracking_number);
                    if (trim(strtoupper($moova_status)) === 'DRAFT') {
                        echo '<a class="button-primary" style="display:block;margin:10px 0;" data-action="change_order_status" data-to-status="ready">Marcar pedido como listo para enviar</a>';
                    }
                } else {
                    echo 'El pedido no está procesado aún';
                    if ($config_status === '0') {
                        echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" data-action="process_order">Procesar pedido</a>';
                    }
                }
            } else {
                $statuses = wc_get_order_statuses();
                echo 'El pedido será procesado cuando esté <strong>' . $statuses['wc-' . $config_status] . '</strong>';
            }
        }
    }
}
