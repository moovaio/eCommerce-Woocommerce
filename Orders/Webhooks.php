<?php

namespace Moova\Orders;

use Moova\Helper\DatabaseTrait;
use Moova\Helper\Helper;
use Moova\Sdk\MoovaSdk;

defined('ABSPATH') || exit;

class Webhooks
{
    /**
     * Receives the webhook and check if it's valid to proceed
     *
     * @return void
     */
    public static function listener()
    {
        try {
            $input = file_get_contents('php://input');
            Helper::log_info("Webhook recibido:$input");
            $input = json_decode($input, true);
            if (Helper::get_option('debug')) {
                Helper::log_debug(__FUNCTION__ . ' - Webhook recibido de Moova: ' . json_encode($input));
            }

            if (empty($input) || !self::validate_input($input)) {
                wp_die('WooCommerce Moova invalid Webhook', 'Moova Webhook', ['response' => 500]);
            }
        } catch (\Throwable $th) {
            Helper::log_info($th);
            Helper::log_info('Unexpected error');
            return true;
        }
    }

    /**
     * Validates the incoming webhook
     *
     * @param array $data
     * @return bool
     */
    public static function validate_input(array $data)
    {
        $data = wp_unslash($data);
        if (empty($data['id'])) {
            Helper::log_info('validate_input - Without id');
            return true;
        }
        $moova_id = filter_var($data['id'], FILTER_SANITIZE_STRING);
        $list_orders = Helper::get_orders_by_itemmeta_value($moova_id);

        if (empty($list_orders)) {
            Helper::log_info('validate_input - Order not found');
            return true;
        }
        self::handle_webhook($list_orders[0]->order_id, $data);

        if (sizeof($list_orders) == 1) {
            return true;
        }

        $main_child_order = wc_get_order($list_orders[0]->order_id);
        $parent = wc_get_order($main_child_order->get_parent_id());
        $order_status = Helper::get_option('receive_' . $data['status']);

        if (!$order_status || $parent->get_status() === $order_status) {
            return true;
        }

        $list_child_orders = DatabaseTrait::get_orders_by_parent_id($parent->get_id());
        foreach ($list_child_orders as $child) {
            if ($child->post_status !== $order_status) {
                return true;
            }
        }

        self::handle_webhook($parent->get_id(), $data);
        return true;
    }

    /**
     * Handles and processes the webhook
     *
     * @param int $order_id
     * @param array $data
     * @return void
     */
    private static function handle_webhook(int $order_id, array $data)
    {
        $order = wc_get_order($order_id);
        $status = self::translate_order_status($data['status']);
        $order->add_order_note('Moova - ' . $status . '. ' . $data['date']);

        $new_order_status = Helper::get_option('receive_' . $data['status']);
        if ($new_order_status) {
            $order->update_status($new_order_status);
        }
        $order->save();
        Helper::log_info(sprintf(__('Order #%s updated with status: %s', 'wc-moova'), $order_id, $status));
        return true;
    }

    /**
     * Translates an order status (from Moova)
     *
     * @param string $status
     * @return string
     */
    private static function translate_order_status(string $status)
    {
        $translations = [
            'DRAFT'     => 'El envío fue creado',
            'READY'     => 'El envío se encuentra listo para ser procesado',
            'CONFIRMED' => 'Envío asignado a un Moover',
            'PICKEDUP'  => 'Envío recogido por el Moover',
            'INTRANSIT' => 'El envío está en viaje',
            'DELIVERED' => 'Envío entregado satisfactoriamente',
            'CANCELED'  => 'Envío cancelado por el usuario',
            'INCIDENCE' => 'Incidencia inesperada',
            'RETURNED'  => 'El envío fue devuelto a su lugar de origen'
        ];
        return (isset($translations[$status]) ? $translations[$status] : 'El envío está en estado ' . $status);
    }
}
