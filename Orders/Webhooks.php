<?php

namespace Ecomerciar\Moova\Orders;

use Ecomerciar\Moova\Helper\Helper;

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
        $input = file_get_contents('php://input');
        $input = json_decode($input, true);
        Helper::log_info('Webhook recibido');
        if (Helper::get_option('debug')) {
            Helper::log_debug(__FUNCTION__ . ' - Webhook recibido de Moova: ' . json_encode($input));
        }
        if (empty($input) || !self::validate_input($input)) {
            wp_die('WooCommerce Moova invalid Webhook', 'Moova Webhook', ['response' => 500]);
        }
    }
    /**
     * Validates the incoming webhook
     *
     * @param array $data
     * @return bool
     */
    private static function validate_input(array $data)
    {
        $data = wp_unslash($data);
        if (empty($data['id'])) {
            return false;
        }
        $moova_id = filter_var($data['id'], FILTER_SANITIZE_STRING);
        $order_id = Helper::find_order_by_itemmeta_value($moova_id);
        if (empty($order_id)) {
            return false;
        }
        self::handle_webhook($order_id, $data);
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

        $newOrderStatus = self::getOrderStatus($data['status']);
        if ($newOrderStatus) {
            $order->update_status($newOrderStatus);
        }

        $order->save();
        Helper::log_info(sprintf(__('Order #%s updated with status: %s', 'wc-moova'), $order_id, $status));

        return true;
    }

    private function getOrderStatus($status)
    {
        if (!Helper::get_option('is_mapping_froom_moova_enabled')) {
            return '';
        }
        $mapping = Helper::get_option('receive_' . $status);
        if (Helper::get_option('debug')) {
            Helper::log_debug("Now we try to map this status $status to $mapping");
        }
        return $mapping;
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
