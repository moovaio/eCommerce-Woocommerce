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
        $actions['force_create_bulk_shipments'] = __('Moova - Forzar creacion de envio incluso si no eligio Moova', 'wc-moova');
        $actions['create_bulk_shipments'] = __('Moova - Crear solo si el cliente eligio Moova', 'wc-moova');
        $actions['start_bulk_shipments'] = __('Moova - Cambiar envio creado a Listo para ser buscado',  'wc-moova');
        return $actions;
    }

    public static function force_create_bulk_shipments($redirect_to, $action, $post_ids)
    {
        if ($action !== 'force_create_bulk_shipments')
            return $redirect_to;

        $moovaShipping = WC()->shipping->get_shipping_methods()['moova'];

        $errors = [];
        $itemsToCreate = [];
        foreach ($post_ids as $post_id) {
            $item = new  \WC_Order_Item_Shipping();
            $item->set_method_title($moovaShipping->method_title);
            $item->set_method_id($moovaShipping->id);
            $order = wc_get_order($post_id);
            if (!self::isAddressCorrect($order)) {
                $errors[] = $post_id;
                continue;
            }
            $shipping_method = Helper::getShippingMethod($order);
            if (!$shipping_method) {
                $order->add_item($item);
                $order->save();
            }
            $itemsToCreate[] = $post_id;
        }

        $redirect_to = add_query_arg(array(
            'response_force_create' => '1',
            'force_create_total_errors' => sizeof($errors),
            'force_create_errors' => implode(',', $errors),
        ), $redirect_to);
        return self::create_bulk_shipments($redirect_to, 'create_bulk_shipments', $itemsToCreate);
    }

    private static function isAddressCorrect($order)
    {
        try {
            Helper::get_customer_from_order($order);
            return true;
        } catch (Exception $error) {
            return false;
        } catch (Error $error) {
            return false;
        }
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
            $moovaId = self::createShipment($order, $moovaSdk);
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

    private static function createShipment($order, $moovaSdk)
    {
        try {
            $shipping_method = Helper::getShippingMethod($order);

            if ($shipping_method->get_meta('tracking_number')) {
                return null;
            }
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

    // The results notice from bulk action on orders
    public static function response_create_bulk_shipments()
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

    // The results notice from bulk action on orders
    public static function response_force_create()
    {
        if (empty($_REQUEST['response_force_create'])) return; // Exit

        $failures =  $_REQUEST['force_create_errors'];
        $totalFailures = intval($_REQUEST['force_create_total_errors']);

        if ($totalFailures > 0) {
            $message = __("We found error in the following orders"
                . ". Please check they have a shipping address before creating them. Ids: ",  'wc-moova') .
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

        $success = 0;
        $failure = [];
        foreach ($post_ids as $post_id) {
            $order = wc_get_order($post_id);
            $moovaId = Processor::update_status($order, 'READY');
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
