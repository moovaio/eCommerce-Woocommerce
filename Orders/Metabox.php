<?php

namespace Ecomerciar\Moova\Orders;

use Ecomerciar\Moova\Helper\Helper;

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
        $shipping_method = array_shift($shipping_methods);
        if ($shipping_method->get_method_id() === 'moova') {
            $config_status = Helper::get_option('status_processing');
            $config_status = str_replace('wc-', '', $config_status);
            if ($order->has_status($config_status) || $config_status === '0') {
                $tracking_number = $shipping_method->get_meta('tracking_number');
                if (!empty($tracking_number)) {
                    preg_match('/([a-zA-Z0-9]+)-/', $tracking_number, $matches);
                    $tracking_number = $matches[1];
                    echo 'El pedido ha sido procesado. Número de rastreo: <strong>' . $tracking_number . '</strong>';
                    $label_url = $shipping_method->get_meta('shipping_label');
                    if (empty($label_url)) {
                        echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" data-action="generate-order-shipping-label">Generar etiqueta</a>';
                        echo '<h4 style="margin-bottom: 0;color: #e80202;display: none;" id="generate-order-shipping-label-error">Hubo un error, por favor intenta nuevamente</h4>';
                    } else {
                        echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" href="' . $label_url . '">Ver Etiqueta</a>';
                    }
                } else {
                    echo 'El pedido no está procesado aún';
                    if ($config_status === '0') {
                        echo '<a class="button-primary" style="display:block;margin:10px 0;" target="_blank" data-action="process-order">Procesar pedido</a>';
                        echo '<h4 style="margin-bottom: 0;color: #e80202;display: none;" id="process-order-error">Hubo un error, por favor intenta nuevamente</h4>';
                    }
                }
            } else {
                $statuses = wc_get_order_statuses();
                echo 'El pedido será procesado cuando esté <strong>' . $statuses['wc-' . $config_status] . '</strong>';
            }
        }
    }
}
