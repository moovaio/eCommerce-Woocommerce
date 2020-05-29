<?php

namespace Ecomerciar\Moova\Sdk;

use Ecomerciar\Moova\Api\MoovaApi;
use Ecomerciar\Moova\Helper\Helper;

class MoovaSdk
{
    private $api;
    private $country;
    public function __construct()
    {
        $this->api = new MoovaApi(
            Helper::get_option('clientid', ''),
            Helper::get_option('clientsecret', ''),
            Helper::get_option('environment', 'test')
        );
        $this->country = Helper::get_option('country', 'AR');
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
                'country' => $this->country,
            ],
            'to' => [
                'street' => $to['street'],
                'number' => $to['number'],
                'floor' => $to['floor'],
                'apartment' => $to['apartment'],
                'city' => $to['locality'],
                'state' => $to['province'],
                'postalCode' => $to['cp'],
                'country' => $this->country,
            ],
            'conf' => [
                'assurance' => false,
                'items' => []
            ],
            'type' => 'woocommerce_24_horas_max'
        ];
        foreach ($items as $item) {
            $data_to_send['conf']['items'][] = ['item' => $item];
        }
        if (empty($data_to_send['to']['street']) && !empty($data_to_send['to']['postalCode'])) {
            unset($data_to_send['to']['street']);
            unset($data_to_send['to']['number']);
            unset($data_to_send['to']['floor']);
            unset($data_to_send['to']['apartment']);
        }
        $res = $this->api->post('/budgets/estimate', $data_to_send);
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
        }
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
        $data_to_send = self::get_shipping_data($order);
        $res = $this->api->post('/shippings', $data_to_send);
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['id'])) {
            Helper::log_error(__('Order could not be processed', 'wc-moova'));
            Helper::log_error(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_error(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
            return false;
        }
        return $res;
    }

    public function update_order(\WC_Order $order)
    {

        if (!$order) return true;
        $shipping_method = Helper::getShippingMethod($order);
        $moova_id = $shipping_method->get_meta('tracking_number');

        $data_to_send = self::get_shipping_data($order);
        $res = $this->api->put("/shippings/$moova_id", $data_to_send);
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['id'])) {
            Helper::log_error(__('Order could not be updated', 'wc-moova'));
            Helper::log_error(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_error(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
            return false;
        }
        return $res;
    }

    private static function get_shipping_data(\WC_Order $order)
    {
        $seller = Helper::get_seller_from_settings();
        $customer = Helper::get_customer_from_order($order);
        $orderItems = Helper::get_items_from_order($order);

        if (!$orderItems) {
            Helper::log_error(__('One of the products has no right measures', 'wc-moova'));
            return;
        }

        $parsedItems = Helper::group_items($orderItems);

        return [
            'scheduledDate' => null,
            'currency' => get_woocommerce_currency(),
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
                'country' => Helper::get_option('country', 'AR'),
                'instructions' => $seller['instructions']
            ],
            'to' => [
                'street' => $customer['street'],
                'number' => $customer['number'],
                'floor' => $customer['floor'],
                'apartment' => $customer['apartment'],
                'city' => $customer['locality'],
                'state' => $customer['province'],
                'postalCode' => $customer['cp'],
                'country' => Helper::get_option('country', 'AR'),
                'instructions' => $customer['extra_info'],
                'contact' => [
                    'firstName' => $customer['first_name'],
                    'lastName' => $customer['last_name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone']
                ]
            ],
            'conf' => [
                'assurance' => false,
                'items' => $parsedItems
            ],
            'internalCode' => $order->get_id(),
            'description' => '',
            'label' => '',
            'type' => 'woocommerce_24_horas_max',
            'extra' => []
        ];
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
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, $order_id));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['label'])) {
            Helper::log_error(sprintf(__('Could not find shipping label of order %s', 'wc-moova'), $order_id));
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
        $res = $this->get_order($order_id);
        if (is_array($res)) {
            return $res['statusHistory'];
        }
        return false;
    }

    /**
     * Gets a Moova order
     *
     * @param string $order_id
     * @return array|false
     */
    public function get_order(string $order_id)
    {
        $res = $this->api->get('/shippings/' . $order_id);
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, $order_id));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['id'])) {
            Helper::log_error(sprintf(__('Could not get order %s', 'wc-moova'), $order_id));
            return false;
        }
        return $res;
    }

    /**
     * Gets the order status in Moova
     *
     * @param string $order_id
     * @return void
     */
    public function get_order_status(string $order_id)
    {
        $res = $this->get_order($order_id);
        if (is_array($res)) {
            return $res['status'];
        }
        return false;
    }

    /**
     * Updates the order status in Moova
     *
     * @param string $order_id
     * @param string $status
     * @param string $reason
     * @return false|array
     */
    public function update_order_status(string $order_id, string $status, string $reason = '')
    {
        $data_to_send = [];
        if ($reason) {
            $data_to_send['reason'] = $reason;
        }
        $res = $this->api->post('/shippings/' . $order_id . '/' . strtolower($status), $data_to_send);
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['status']) || strtoupper($res['status']) !== strtoupper($status)) {
            return false;
        }
        return $res;
    }
}
