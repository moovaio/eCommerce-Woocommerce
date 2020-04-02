<?php

namespace Ecomerciar\Moova\Orders;

use Ecomerciar\Moova\Helper\Helper;
use Ecomerciar\Moova\Sdk\MoovaSdk;
use Error;
use ErrorException;
use Exception;
use TypeError;

defined('ABSPATH') || exit;

class BulkChanges
{

    public static function set_bulk_options($actions)
    {
        $actions['create_bulk_shipments'] = __('Moova - Create shipments', 'wc-moova');
        $actions['start_bulk_shipments'] = __('Moova - Start shipments',  'wc-moova');
        return $actions;
    }

    public static function create_bulk_shipments($redirect_to, $action, $post_ids)
    {
        if ($action !== 'create_bulk_shipments')
            return $redirect_to; // Exit

        $moovaSdk = new MoovaSdk();
        $success = 0;
        $failure = [];
        foreach ($post_ids as $post_id) {
            $order = wc_get_order($post_id);
            $moovaId = self::creatShipment($order, $moovaSdk);
            if ($moovaId) {
                $success += 1;
            } else {
                $failure[] = $post_id;
            }
        }

        return $redirect_to = add_query_arg(array(
            'response_bulk_create_moova' => '1',
            'success' => $success,
            'failure_ids' => implode(',', $failure),
            'failure_total' => count($failure)
        ), $redirect_to);
    }

    private static function creatShipment($order, $moovaSdk)
    {
        try {
            $shipping_methods = $order->get_shipping_methods();
            if (!self::is_enabled_to_create($shipping_methods)) {
                return null;
            }
            $shipping_method = array_shift($shipping_methods);
            $customer =  Helper::get_customer_from_order($order);
            $res = $moovaSdk->process_order($order, $customer);

            $tracking_id = $res['id'];
            $shipping_method->update_meta_data('tracking_number', $tracking_id);
            $res = $moovaSdk->get_shipping_label($tracking_id);
            if ($res) {
                $shipping_label = $res['label'];
                $shipping_method->update_meta_data('shipping_label', $shipping_label);
            }
            $shipping_method->save();
            return $tracking_id;
        } catch (Exception $error) {
            return null;
        } catch (TypeError $error) {
            return null;
        } catch (Error $error) {
            return null;
        }
    }

    private static function is_enabled_to_create($shipping_methods)
    {
        foreach ($shipping_methods as $method) {
            if ($method['method_id'] === 'moova') {
                if ($method->get_meta('tracking_number')) {
                    return false;
                }
                return true;
            }
        }
        return false;
    }

    // The results notice from bulk action on orders
    public function response_create_bulk_shipments()
    {
        if (empty($_REQUEST['response_bulk_create_moova'])) return; // Exit

        $success = intval($_REQUEST['success']);
        $failures =  $_REQUEST['failure_ids'];
        $totalFailures = intval($_REQUEST['failure_total']);

        if ($success || $success > 0) {
            $message = __("We have created succesfully ",  'wc-moova') . $success . __(" shipments in Moova. ",  'wc-moova');
            self::send_message('success', $message);
        }

        if ($totalFailures || $totalFailures > 0) {
            $message = __("We found error in the following orders"
                . ". Please check they are Moova shipments and they are not already created. Ids: ",  'wc-moova') .
                $failures;
            self::send_message('error', $message);
        }
    }

    private function send_message($type, $text)
    {
        printf("<div id='message' class='$type updated fade'><p>$text</p></div>", 1);
    }


    public static function start_bulk_shipments($redirect_to, $action, $post_ids)
    {
        if ($action !== 'start_bulk_shipments')
            return $redirect_to; // Exit

        $moovaSdk = new MoovaSdk();
        $success = 0;
        $failure = [];
        foreach ($post_ids as $post_id) {
            $order = wc_get_order($post_id);
            $moovaId = self::update_moova_shipment($order, $moovaSdk);
            if ($moovaId) {
                $success += 1;
            } else {
                $failure[] = $post_id;
            }
        }

        return $redirect_to = add_query_arg(array(
            'response_start_bulk_shipments' => '1',
            'success' => $success,
            'failure_ids' => implode(',', $failure),
            'failure_total' => count($failure)
        ), $redirect_to);
    }

    private static function update_moova_shipment($order, $moovaSdk)
    {
        try {
            $shipping_methods = $order->get_shipping_methods();
            $shipping_method = array_shift($shipping_methods);
            $id = $shipping_method->get_meta('tracking_number');
            $can_change_status = $shipping_method['method_id'] === 'moova' && $id;
            if (!$can_change_status) {
                return null;
            }
            $response = $moovaSdk->update_order_status($id, 'READY');
            if ($response) return $id;
            return null;
        } catch (Exception $error) {
            return null;
        } catch (TypeError $error) {
            return null;
        } catch (Error $error) {
            return null;
        }
    }

    public static function response_start_bulk_shipments()
    {
        if (empty($_REQUEST['response_start_bulk_shipments'])) return; // Exit

        $success = intval($_REQUEST['success']);
        $failures =  $_REQUEST['failure_ids'];
        $totalFailures = intval($_REQUEST['failure_total']);

        if ($success || $success > 0) {
            $message = __("We have started succesfully ",  'wc-moova') . $success . __(" shipments in Moova. ",  'wc-moova');
            self::send_message('success', $message);
        }

        if ($totalFailures || $totalFailures > 0) {
            $message = __("We found error in the following orders"
                . ". Please check they are already created Moova shipments and they are not in status READY: ",  'wc-moova') .
                $failures;
            self::send_message('error', $message);
        }
    }
}
