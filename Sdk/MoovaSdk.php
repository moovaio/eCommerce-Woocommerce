<?php

namespace Moova\Sdk;

use Moova\Api\MoovaApi;
use Moova\Api\UserApi;

use Moova\Helper\Helper;
use Exception;

class MoovaSdk
{
    private $api;
    public function __construct()
    {
        $prefixEnv = Helper::get_option('environment', 'test') === 'test' ? 'dev' : '';
        $this->api = new MoovaApi(
            Helper::get_option($prefixEnv . 'clientid', ''),
            Helper::get_option($prefixEnv . 'clientsecret', ''),
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
        $data_to_send = self::format_payload_estimate($origin, $to, $items);
        try {
            Log::info(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
            $res = $this->api->post('/budgets/estimate', $data_to_send);
            Log::info(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
        } catch (Exception $error) {
        }
        return $this->format_price($res, WC()->cart->cart_contents_total);
    }

    private static function format_payload_estimate($origin, $destination, $items)
    {
        $to =[
            'floor' => $to['floor'],
            'apartment' => $to['apartment'],
        ];
        if (!empty($to['lat'])) {
            $to['coords'] = ['lat' => $to['lat'],  'lng' => $to['lng'] ];
        } elseif (!empty($to['full_address'])) {
            $to['address'] = "{$destination['full_address']} {$destination['country']}";
        } else {
            $to['postalCode'] = $destination['postal_code'];
        }

        $data_to_send = [
            'from' => [
                'floor' => $origin['floor'],
                'apartment' => $origin['apartment'],
                'address' => $origin['address'],
            ],
            'to' => $to,
            'conf' => [
                'assurance' => false,
                'items' => $items
            ],
            'type' => 'woocommerce_24_horas_max'
        ];
    }

    private function format_price($price, $cartPrice)
    {
        if (empty($price['budget_id'])) {
            return false;
        }
        $shippingPrice = $price['billing']['gross_price'];
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
            if ($shippingPrice < $min) {
                return $min;
            }
            $max = Helper::get_option('max_price', null);
            if ($shippingPrice > $max) {
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
        Helper::log_info(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
        $res = $this->api->post('/shippings', $data_to_send);
        Helper::log_info(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));

        if (empty($res['id'])) {
            Helper::log_error(__('Order could not be processed', 'moova-for-woocommerce'));
            Helper::log_error(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
            return false;
        }
        return $res;
    }

    public function update_order(\WC_Order $order)
    {
        if (!$order) {
            return true;
        }
        $shipping_method = Helper::get_shipping_method($order);
        $moova_id = $shipping_method->get_meta('tracking_number');

        $data_to_send = self::get_shipping_data($order);
        $res = $this->api->put("/shippings/$moova_id", $data_to_send);
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['id'])) {
            Helper::log_error(__('Order could not be updated', 'moova-for-woocommerce'));
            Helper::log_error(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_error(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
            return false;
        }
        return $res;
    }

    private static function get_shipping_data(\WC_Order $order)
    {
        $seller = Helper::get_seller_from_settings($order);
        $customer = Helper::get_customer_from_order($order);
        $orderItems = Helper::get_items_from_order($order);
        $scheduledDate = Helper::get_custom_shipping_type('schedule_date', $order);
        $scheduledDate = $scheduledDate ?  "$scheduledDate 01:00:00" : null;
        $parse = parse_url(get_site_url());
        $prefix = substr($parse['host'], 0, 3);
        $to = [
            'street' => $customer['street'],
            'number' => $customer['number'],
            'city' => $customer['locality'],
            'state' => $customer['province'],
        ];

        if (!empty($customer['lat'])) {
            $to = [
                'coords' => [
                    'lat' => $customer['lat'],
                    'lng' => $customer['lng']
                ],
                'addressDescription' => "{$customer['street']} {$customer['number']}"
            ];
        }
        Helper::log_info($scheduledDate);
        return [
            'scheduledDate' => $scheduledDate,
            'currency' => get_woocommerce_currency(),
            'type' => 'regular',
            'flow' => 'manual',
            'from' => $seller,
            'to' => array_merge($to, [
                'country' => $customer['country'],
                'floor' => $customer['floor'],
                'apartment' => $customer['apartment'],
                'postalCode' => $customer['cp'],
                'instructions' => $customer['extra_info'],
                'contact' => [
                    'firstName' => $customer['first_name'],
                    'lastName' => $customer['last_name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone']
                ]
            ]),
            'conf' => [
                'assurance' => false,
                'items' => $orderItems
            ],
            'internalCode' => $prefix . "-" . $order->get_id(),
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
        try {
            $res = $this->api->get('/shippings/' . $order_id . '/label');
            if (empty($res['label'])) {
                Helper::log_error(sprintf(__('Could not find shipping label of order %s', 'moova-for-woocommerce'), $order_id));
                return false;
            }
        } catch (Exception $error) {
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
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, $order_id));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['id'])) {
            Helper::log_error(sprintf(__('Could not get order %s', 'moova-for-woocommerce'), $order_id));
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
        $url = '/shippings/' . $order_id . '/' . strtolower($status);
        $res = $this->api->post($url, $data_to_send);
        if (Helper::get_option('debug')) {
            Helper::log_debug(__("Data sent to Moova: $url", 'moova-for-woocommerce'));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
        }
        if (empty($res['status']) || strtoupper($res['status']) !== strtoupper($status)) {
            return false;
        }
        return $res;
    }

    /**
     * Get autocomplete
     *
     */
    public function autocomplete($query)
    {
        $res = $this->userApi->get("/autocomplete?query=$query");
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($query)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
        }
        return $res;
    }


    /**
     * Set hooks
     *
     * @param string $order_id
     * @param string $status
     * @param string $reason
     * @return false|array
     */
    public function setHooks()
    {
        $body = [
            "webhook_enabled" => true,
            "webhook_giveup_count" => 0,
            "webhook_method" => "POST",
            "webhook_url" => get_site_url(null, '/wc-api/wc-moova-orders')
        ];
        $res = $this->api->patch("/applications/webhooks", $body);
        if (Helper::get_option('debug')) {
            Helper::log_debug(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($body)));
            Helper::log_debug(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
        }
        return $res;
    }
}
