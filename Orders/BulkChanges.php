<?php

namespace Moova\Orders;

defined('ABSPATH') || exit;

class BulkChanges
{

    public static function set_bulk_options($actions)
    {
        $actions['force_create_bulk_shipments'] = __('Moova - Send with Moova', 'wc-moova');
        $actions['start_bulk_shipments'] = __('Moova - Change shipping to Ready',  'wc-moova');
        $actions['force_latest_status_shipments'] = __('Moova - Force latest status', 'wc-moova');

        return $actions;
    }

    public static function force_create_bulk_shipments($redirect_to, $action, $post_ids)
    {
        if ($action !== 'force_create_bulk_shipments')
            return $redirect_to;
        $success = 0;
        $failure = [];
        foreach ($post_ids as $post_id) {
            $order = wc_get_order($post_id);
            $moovaId = Processor::process_order_and_childrens($order);
            if ($moovaId) {
                $success += 1;
            } else {
                $failure[] = $post_id;
            }
        }

        $redirect_to = add_query_arg(array(
            'response_force_create' => '1',
            'success' => $success,
            'force_create_errors' => implode(',', $failure),
            'force_create_total_errors' => count($failure)
        ), $redirect_to);

        return $redirect_to;
    }

    // The results notice from bulk action on orders
    public static function response_force_create()
    {
        if (empty(sanitize_text_field($_REQUEST['response_force_create']))) return; // Exit

        $failures =  sanitize_text_field($_REQUEST['force_create_errors']);
        $totalFailures = intval($_REQUEST['force_create_total_errors']);

        $success = intval($_REQUEST['success']);

        if ($success || $success > 0) {
            $message = __("We have created succesfully ",  'wc-moova') . $success . __(" shipments in Moova. ",  'wc-moova');
            self::send_message('success', $message);
        }

        if ($totalFailures > 0) {
            $message = __("We found error in the following orders"
                . ". Please check they have a shipping address before creating them. Ids: ",  'wc-moova') .
                $failures;
            self::send_message('error', $message);
        }
    }

    private static function send_message($type, $text)
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

        $success = intval( sanitize_text_field($_REQUEST['success']));
        $failures =  sanitize_text_field($_REQUEST['failure_ids']);
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

    public static function force_latest_status_shipments($redirect_to, $action, $post_ids)
    {
        if ($action !== 'force_latest_status_shipments')
            return $redirect_to;
        $success = 0;
        $failure = [];
        foreach ($post_ids as $post_id) {
            $order = wc_get_order($post_id);
            if (Processor::get_latest_status($order)) {
                $success += 1;
            } else {
                $failure[] = $post_id;
            }
        }

        $redirect_to = add_query_arg(array(
            'response_force_latest_status' => '1',
            'success' => $success,
            'force_create_errors' => implode(',', $failure),
            'force_create_total_errors' => count($failure)
        ), $redirect_to);

        return $redirect_to;
    }

    // The results notice from bulk action on orders
    public static function response_force_latest_status()
    {
        if (empty($_REQUEST['response_force_latest_status'])) return;

        $success = intval($_REQUEST['success']);
        $failures =  sanitize_text_field($_REQUEST['failure_ids']);
        $totalFailures = intval($_REQUEST['failure_total']);

        if ($success || $success > 0) {
            $message = __("We got the latest status succesfully of",  'wc-moova') . $success . __(" shipments in Moova. ",  'wc-moova');
            self::send_message('success', $message);
        }

        if ($totalFailures || $totalFailures > 0) {
            $message = __("We found error in the following orders, please be sure they are moova shippings:",  'wc-moova') .
                $failures;
            self::send_message('error', $message);
        }
    }
}
