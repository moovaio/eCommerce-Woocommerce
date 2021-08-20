<?php

namespace Moova\Orders;

use Moova\Helper\DatabaseTrait;
use Moova\Helper\Helper;
use Moova\Sdk\MoovaSdk;
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
        $ready_status =  str_replace('wc-', '', Helper::get_option('status_ready'));
        $shipping_method = Helper::get_shipping_method($order);
        if (!$shipping_method) {
            return;
        }
        $currentStatus = $order->get_status();
        Helper::log_info("Handling status $currentStatus for $order_id. Ready=$ready_status");
        if (empty($shipping_method->get_meta('tracking_number'))) {
            self::process_order_and_childrens($order, $shipping_method);
        } else if ($currentStatus === $ready_status) {
            self::update_status($order, 'READY');
        } else if ($currentStatus == 'wc-cancelled' || $currentStatus == 'cancelled') {
            self::update_status($order, 'CANCEL', 'Cancel by woocommercer admin');
        }
    }

    public static function update_status($order, $status, $reason = '')
    {
        try {
            $moovaSdk = new MoovaSdk();
            $shipping_method = Helper::get_shipping_method($order);
            $moova_id = $shipping_method->get_meta('tracking_number');
            Helper::log_info("Updating status $moova_id");
            if (!$shipping_method) {
                return null;
            }
            $res =  $moovaSdk->update_order_status($moova_id, $status, $reason);
            return $res ? $moova_id : null;
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
        if (!wp_verify_nonce(sanitize_text_field($_POST['nonce']), 'moova-for-woocommerce') || empty(sanitize_text_field($_POST['order_id']))) {
            wp_send_json_error();
        }

        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error();
        }

        $moovaSdk = new MoovaSdk();

        $shipping_method = Helper::get_shipping_method($order);
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
     * Process an order in Moova
     *
     * @return void
     */
    public static function process_order_and_childrens($order, $shipping_method = null)
    {
        $list_of_child_orders = DatabaseTrait::get_orders_by_parent_id($order->id);
        Helper::log_info("process_order_and_childrens - list_of_child_orders:" . json_encode($list_of_child_orders));
        if (empty($list_of_child_orders)) {
            return (array) self::format_creation($order, $shipping_method);
        }
        $list_of_tracking_ids = [];
        foreach ($list_of_child_orders as $child) {
            $child_order = wc_get_order($child->id);
            $tracking_id = self::format_creation($child_order);
            if ($tracking_id) {
                $list_of_tracking_ids[] = $tracking_id;
            }
        }

        return $list_of_tracking_ids;
    }

    public static function format_creation($order, $shipping_method = null)
    {
        try {
            Helper::log_info("Processing order {$order->id}");
            $moovaSdk = new MoovaSdk();
            $res = $moovaSdk->process_order($order);
            if (!$res) {
                Helper::log_info($res);
                return false;
            }
            $tracking_id = $res['id'];
            self::set_shipping_method_in_order($order, $res, $shipping_method);
            return $tracking_id;
        } catch (Exception $error) {
            Helper::log_info($error);
            return null;
        } catch (TypeError $error) {
            return null;
        } catch (Error $error) {
            return null;
        }
    }

    public static function set_shipping_method_in_order($order, $moovaOrder, $shipping_method = null, $moovaSdk = null)
    {
        $moovaSdk = $moovaSdk ?? new MoovaSdk();
        $item = $shipping_method ?? new  \WC_Order_Item_Shipping();
        $moovaShippingmethod = WC()->shipping->get_shipping_methods()['moova'];
        $tracking_id = $moovaOrder['id'];
        $hash = $moovaOrder['hash'];
        $item->set_method_title($moovaShippingmethod->method_title);
        $item->set_method_id($moovaShippingmethod->id);
        $item->update_meta_data('tracking_number', $tracking_id);

        $domain = Helper::get_option('environment') === 'prod' ? 'dashboard' : 'dev';
        $tracking_url = "https://$domain.moova.io/external/$tracking_id?hash=$hash";
        $item->update_meta_data('tracking_url', $tracking_url);

        $res = $moovaSdk->get_shipping_label($tracking_id);

        if ($res && !empty($res['label'])) {
            $order->add_order_note("Etiqueta: " . $res['label']);
            $item->update_meta_data('shipping_label', $res['label']);
        }
        $order->add_item($item);
        $order->save();
    }

    /**
     * Changes an order status in Moova, made for AJAX calls
     *
     * @return void
     */
    public static function change_order_status()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'moova-for-woocommerce') || empty(sanitize_text_field($_POST['order_id']))) {
            wp_send_json_error();
        }

        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);
        $new_status = strtoupper(filter_var($_POST['toStatus'], FILTER_SANITIZE_STRING));
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error();
        }
        $res = self::update_status($order, $new_status, $order);
        if (!$res) {
            wp_send_json_error();
        }
        wp_send_json_success();
    }


    public static function notifyMoova($order_id)
    {
        $order = wc_get_order($order_id);
        $shipping_method = Helper::get_shipping_method($order);
        if (empty($shipping_method)) {
            return null;
        }
        $moovaSdk = new MoovaSdk();
        if ($shipping_method->get_meta('tracking_number')) {
            $moovaSdk->update_order($order);
        }
    }

    public static function get_latest_status($order)
    {
        try {
            $shipping_method = Helper::get_shipping_method($order);
            $id = $shipping_method->get_meta('tracking_number');
            if (!$shipping_method) {
                return false;
            }
            $moovaSdk = new MoovaSdk();
            $status = $moovaSdk->get_order_status($id);
            Helper::log_info($status);
            Webhooks::validate_input([
                "status" => $status,
                "id" => $id
            ]);
            return true;
        } catch (\Throwable $th) {
            Helper::log_info("get_latest_status - Unable to process $order->id");
            Helper::log_info($th);
            return false;
        }
    }
}
