<?php

namespace Ecomerciar\Moova\Orders;

use Ecomerciar\Moova\Helper\Helper;
use Ecomerciar\Moova\Sdk\MoovaSdk;
use Error;
use Exception;
use TypeError;

defined('ABSPATH') || exit;

class Processor
{
    /**
     * Handles the WooCommerce order status
     *
     * @param int $order_id
     * @param string $status_from
     * @param string $status_to
     * @param WC_Order $order
     * @return void
     */
    public static function handle_order_status(int $order_id, string $status_from, string $status_to, \WC_Order $order)
    {
        $config_status = Helper::get_option('status_processing');
        $cancel_status =  str_replace('wc-', '', Helper::get_option('status_cancel'));
        $config_status = str_replace('wc-', '', $config_status);
        $ready_status =  str_replace('wc-', '', Helper::get_option('status_ready'));
        $shipping_method = Helper::getShippingMethod($order);
        if (!$shipping_method) {
            return;
        }
        $moovaSdk = new MoovaSdk();
        $currentStatus = $order->get_status();
        if ($currentStatus === $config_status && empty($shipping_method->get_meta('tracking_number'))) {
            $res = $moovaSdk->process_order($order, Helper::get_customer_from_order($order));
            if (!$res) {
                Helper::add_error(__('The order could not be processed.', 'wc-moova'));
                return;
            }
            $tracking_id = $res['id'];
            $shipping_method->update_meta_data('tracking_number', $tracking_id);
            $shipping_method->save();

            $res = $moovaSdk->get_shipping_label($tracking_id);
            if (!$res) {
                Helper::add_error(__('Shipping label could not be found.', 'wc-moova'));
                return;
            }
            $shipping_label = $res['label'];
            $shipping_method->update_meta_data('shipping_label', $shipping_label);

            $shipping_method->save();
        } else if ($currentStatus === $ready_status) {
            self::update_status($order, 'READY');
        } else if ($currentStatus === $cancel_status) {
            self::update_status($order, 'CANCEL', 'Cancel by woocommercer admin');
        }
    }

    public static function update_status($order, $status, $reason = '')
    {
        try {
            $moovaSdk = new MoovaSdk();
            $shipping_method = Helper::getShippingMethod($order);
            $moova_id = $shipping_method->get_meta('tracking_number');
            if ($shipping_method['method_id'] !== 'moova' && $moova_id) {
                return null;
            }
            $res =  $moovaSdk->update_order_status($moova_id, $status, $reason);
            if ($res) {
                return $moova_id;
            }
            return null;
        } catch (Exception $error) {
            return null;
        } catch (TypeError $error) {
            return null;
        } catch (Error $error) {
            return null;
        }
    }
    /**
     * Creates a shipping label for a shipment, made for AJAX calls
     *
     * @return void
     */
    public static function order_create_shipping_label_ajax()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'wc-moova') || empty($_POST['order_id'])) {
            wp_send_json_error();
        }

        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error();
        }

        $moovaSdk = new MoovaSdk();

        $shipping_method = Helper::getShippingMethod($order);
        if (!$shipping_method) {
            wp_send_json_error();
        }
        $tracking_id = $shipping_method->get_meta('tracking_number');
        if (!$tracking_id) {
            wp_send_json_error();
        }
        $res = $moovaSdk->get_shipping_label($tracking_id);
        if (!$res) {
            wp_send_json_error();
        }
        $shipping_label = $res['label'];
        $shipping_method->update_meta_data('shipping_label', $shipping_label);
        $shipping_method->save();

        wp_send_json_success();
    }

    /**
     * Process an order in Moova, made for AJAX calls
     *
     * @return void
     */
    public static function process_order_ajax()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'wc-moova') || empty($_POST['order_id'])) {
            wp_send_json_error();
        }

        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error();
        }

        $moovaSdk = new MoovaSdk();

        $shipping_method = Helper::getShippingMethod($order);
        if (!$shipping_method) {
            wp_send_json_error();
        }

        $res = $moovaSdk->process_order($order, Helper::get_customer_from_order($order));
        if (!$res) {
            wp_send_json_error();
        }
        $tracking_id = $res['id'];
        $shipping_method->update_meta_data('tracking_number', $tracking_id);
        $res = $moovaSdk->get_shipping_label($tracking_id);
        if ($res) {
            $shipping_label = $res['label'];
            $shipping_method->update_meta_data('shipping_label', $shipping_label);
        }
        $shipping_method->save();

        wp_send_json_success();
    }

    /**
     * Changes an order status in Moova, made for AJAX calls
     *
     * @return void
     */
    public static function change_order_status()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'wc-moova') || empty($_POST['order_id'])) {
            wp_send_json_error();
        }

        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $new_status = strtoupper(filter_var($_POST['toStatus'], FILTER_SANITIZE_STRING));
        $reason = 'Cambio de estado manual por el usuario';
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error();
        }
        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $new_status = strtoupper(filter_var($_POST['toStatus'], FILTER_SANITIZE_STRING));
        $reason = 'Cambio de estado manual por el usuario';
        $order = wc_get_order($order_id);
        $res = self::update_status($order, $new_status, $order);
        if (!$res) {
            wp_send_json_error();
        }
        wp_send_json_success();
    }


    public static function notifyMoova($order_id)
    {
        $order = wc_get_order($order_id);
        $shipping_method = Helper::getShippingMethod($order);
        if (empty($shipping_method)) {
            return null;
        }
        $moovaSdk = new MoovaSdk();
        if ($shipping_method->get_meta('tracking_number')) {
            $moovaSdk->update_order($order);
        } else {
            $moovaSdk->process_order($order);
        }
    }
}
