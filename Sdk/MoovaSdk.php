<?php

namespace Ecomerciar\Moova\Sdk;

use Ecomerciar\Moova\Api\MoovaApi;
use Ecomerciar\Moova\Api\UserApi;

use Ecomerciar\Moova\Helper\Helper;
use Exception;

class MoovaSdk
{
    private $api;
    public function __construct()
    {
        $this->api = new MoovaApi(
            Helper::get_option('clientid', ''),
            Helper::get_option('clientsecret', ''),
            Helper::get_option('environment', 'test')
        );
        $this->userApi = new UserApi(Helper::get_option('environment', 'test'));
    }

    /**
     * Gets a quote for an order
     *
     * @param array $from
     * @param array $to
     * @param array $items
     * @return array|false
     */
    public function get_price(array $origin, array $to, array $items)
    {
        $from = [
            'floor' => $origin['floor'],
            'apartment' => $origin['apartment'],
            'address' => $origin['address'],
        ];

        $data_to_send = [
            'from' => $from,
            'to' => [
                'floor' => $to['floor'],
                'apartment' => $to['apartment'],
                'postalCode' => $to['cp'],
            ],
            'conf' => [
                'assurance' => false,
                'items' => $items
            ],
            'type' => 'woocommerce_24_horas_max'
        ];

        if (isset($to['number']) && !empty($to['number'])) {
            $data_to_send['to']['address'] = "{$to['street']} {$to['number']},{$to['province']}, {$to['country']}";
        }
        try {
            $res = $this->api->post('/budgets/estimate', $data_to_send);
            if (Helper::get_option('debug')) {
                Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($data_to_send)));
                Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
            }
            if (empty($res['budget_id'])) {
                unset($data_to_send['to']['address']);
                $res = $this->api->post('/budgets/estimate', $data_to_send);
            }
        } catch (Exception $error) {
            Helper::log_debug('Budget from postalcode');
            unset($data_to_send['to']['address']);
            $res = $this->api->post('/budgets/estimate', $data_to_send);
        }

        return $this->formatPrice($res, WC()->cart->cart_contents_total);
    }

    private function formatPrice($price, $cartPrice)
    {
        if (empty($price['budget_id'])) {
            return false;
        }
        $shippingPrice = Helper::get_option('price_iva', '1') ? $price['billing']['gross_price'] : $price['price'];
        $specialPricing =  Helper::get_option('has_special_price', 'default');
        $hasFreeShip = Helper::get_option('has_free_shipping', null) === "1" && Helper::get_option('free_shipping_price', null);
        if ($hasFreeShip && $cartPrice > Helper::get_option('free_shipping_price', null)) {
            Helper::log_info('formatPrice - Shipping is free');
            return 0;
        }

        if ($specialPricing === 'fixed' && Helper::get_option('fixed_price', null)) {
            return Helper::get_option('fixed_price', null);
        } elseif ($specialPricing === 'range') {
            $min = Helper::get_option('min_price', 0);
            if ($price < $min) {
                return $min;
            }
            $max = Helper::get_option('max_price', null);
            if ($price > $max) {
                return $max;
            }
        }
        Helper::log_info("formatPrice -Final price: $shippingPrice");
        return $shippingPrice;
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
        return [
            'scheduledDate' => null,
            'currency' => get_woocommerce_currency(),
            'type' => 'regular',
            'flow' => 'manual',
            'from' => $seller,
            'to' => [
                'street' => $customer['street'],
                'number' => $customer['number'],
                'floor' => $customer['floor'],
                'apartment' => $customer['apartment'],
                'city' => $customer['locality'],
                'state' => $customer['province'],
                'postalCode' => $customer['cp'],
                'country' => $order->shipping_country,
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
                'items' => $orderItems
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

    /**
     * Updates the order status in Moova
     *
     * @param string $order_id
     * @param string $status
     * @param string $reason
     * @return false|array
     */
    public function autocomplete($query)
    {
        $res = $this->userApi->get("/autocomplete?query=$query");
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($query)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'wc-moova'), __FUNCTION__, json_encode($res)));
        }
        return $res;
    }
}
