<?php

namespace Ecomerciar\Moova\Sdk;

use Ecomerciar\Moova\Api\MoovaApi;
use Ecomerciar\Moova\Helper\Helper;

class MoovaSdk
{
    private $api;
    public function __construct()
    {
        $this->api = new MoovaApi(
            Helper::get_option('clientid', ''),
            Helper::get_option('clientsecret', '')
        );
    }

    /**
     * Gets a quote for an order
     *
     * @param array $from
     * @param array $to
     * @param array $items
     * @return array|false
     */
    public function get_price(array $from, array $to, array $items)
    {
        $data_to_send = [
            'from' => [
                'street' => $from['street'],
                'number' => $from['number'],
                'floor' => $from['floor'],
                'apartment' => $from['apartment'],
                'city' => $from['city'],
                'state' => $from['state'],
                'postalCode' => $from['postalCode'],
                'country' => 'AR',
            ],
            'to' => [
                'street' => $to['street'],
                'number' => $to['number'],
                'floor' => $to['floor'],
                'apartment' => $to['apartment'],
                'city' => $to['locality'],
                'state' => $to['province'],
                'postalCode' => $to['cp'],
                'country' => 'AR',
            ],
            'conf' => [
                'assurance' => false,
                'items' => []
            ],
            'size_id' => 1,
            'shipping_type_id' => 6
        ];
        foreach ($items as $item) {
            $data_to_send['conf']['items'][] = ['item' => $item];
        }
        $res = $this->api->post('/v2/budgets', $data_to_send);
        if (!$res || empty($res['budget_id'])) {
            return false;
        }
        return $res;
    }


    /**
     * Process an order in Moova's Api
     *
     * @return array|false
     */
    public function process_order(\WC_Order $order)
    {
        $seller = Helper::get_seller_from_settings();
        $customer = Helper::get_customer_from_order($order);
        $items = Helper::get_items_from_order($order);
        $data_to_send = [
            'scheduledDate' => null,
            'currency' => 'ARS',
            'type' => 'regular',
            'flow' => 'manual',
            'from' => [
                'street' => $seller['street'],
                'number' => $seller['number'],
                'floor' => $seller['floor'],
                'apartment' => $seller['apartment'],
                'city' => $seller['city'],
                'state' => $seller['state'],
                'postalCode' => $seller['postalCode'],
                'country' => 'AR',
            ],
            'to' => [
                'street' => $customer['street'],
                'number' => $customer['number'],
                'floor' => $customer['floor'],
                'apartment' => $customer['apartment'],
                'city' => $customer['locality'],
                'state' => $customer['province'],
                'postalCode' => $customer['cp'],
                'country' => 'AR',
            ],
            'conf' => [
                'assurance' => false,
                'items' => []
            ],
            'internalCode' => $order->get_id(),
            'description' => 'Pedido número ' . $order->get_id(),
            'label' => '',
            'extra' => []
        ];
        foreach ($items as $item) {
            $data_to_send['conf']['items'][] = ['item' => $item];
        }
        $res = $this->api->post('/shippings', $data_to_send);
        if (Helper::get_option('debug')) {
            Helper::log_debug(__FUNCTION__ . ' - Data enviada a Moova: ' . json_encode($data_to_send));
            Helper::log_debug(__FUNCTION__ . ' - Data recibida de Moova: ' . json_encode($res));
        }
        if (empty($res['id'])) {
            Helper::log_error('No se pudo procesar el pedido.');
            Helper::log_error(__FUNCTION__ . ' - Data enviada a Moova: ' . json_encode($data_to_send));
            Helper::log_error(__FUNCTION__ . ' - Data recibida de Moova: ' . json_encode($res));
            return false;
        }
        return $res;
    }

    /**
     * Gets the shipping label url for a Moova Shipment
     *
     * @param string $order_id
     * @return array|false
     */
    public function get_shipping_label(string $order_id)
    {
        $res = $this->api->get('/shippings/' . $order_id . '/label');
        if (Helper::get_option('debug')) {
            Helper::log_debug(__FUNCTION__ . ' - Data enviada a Moova: ' . $order_id);
            Helper::log_debug(__FUNCTION__ . ' - Data recibida de Moova: ' . json_encode($res));
        }
        if (empty($res['label'])) {
            Helper::log_error('No se pudo obtener etiqueta del pedido ' . $order_id);
            return false;
        }
        return $res;
    }

    /**
     * Gets the tracking status for a Moova Shipment
     *
     * @param string $order_id
     * @return array|false
     */
    public function get_tracking(string $order_id)
    {
        $res = $this->api->get('/shippings/' . $order_id);
        if (Helper::get_option('debug')) {
            Helper::log_debug(__FUNCTION__ . ' - Data enviada a Moova: ' . $order_id);
            Helper::log_debug(__FUNCTION__ . ' - Data recibida de Moova: ' . json_encode($res));
        }
        if (empty($res['id'])) {
            Helper::log_error('No se pudo obtener etiqueta del pedido ' . $order_id);
            return false;
        }
        return $res['statusHistory'];
    }
}
