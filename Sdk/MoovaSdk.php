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
        Helper::log_info("Entering get_price");
        $data_to_send = self::format_payload_estimate($origin, $to, $items);
        $address_hash = hash('md5', wp_json_encode($data_to_send));

        if( $address_hash ===  WC()->session->get("moova_prev_quote_hash") ){
            Helper::log_info("Duplicated request returning session hashed");
            return WC()->session->get("moova_prev_price");
        }
        if( !$this->has_all_required_params($data_to_send)){
            Helper::log_info("not all params are set");
            return $this->format_price(false, WC()->cart->cart_contents_total);
        } 

        try {
            $res = $this->api->post('/budgets/estimate', $data_to_send);
            Helper::log_info(sprintf(__('%s - Data sent to Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_info(sprintf(__('%s - Data received from Moova: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
            if (empty($res['budget_id']) && !Helper::get_option('google_api_key')) 
            { 
                $res = $this->get_price_by_postal_code($data_to_send); 
            }
        } catch (Exception $error) {
        }
       $formated = $this->format_price($res, WC()->cart->cart_contents_total);
       
       WC()->session->set('moova_prev_quote_hash',$address_hash);
       WC()->session->set('moova_prev_price',$formated);
       return $formated;  
    }

    private function has_all_required_params($data_to_send){ 
        if(!$data_to_send["from"]["address"]){
            return false;
        }
        else if(is_cart() && $data_to_send["to"]["postalCode"]){
            Helper::log_info("is cart without postalcode");
            return true;
        }
        else if($data_to_send["to"]["address"] && $data_to_send["to"]["country"] && $data_to_send["to"]["city"]){
            return true;
        }
        return false;
    }

    public function get_price_by_postal_code($data_to_send)
    { 
        if(Helper::get_option('enable_postal_codes_quotes') === "0"){
            return false;     
        }
        Helper::log_info("Starting get price by cp 2" );  
        $data_to_send["to"]=["postalCode"=>$data_to_send["to"]["postalCode"]];

        try{
            $res = $this->api->post('/budgets/estimate', $data_to_send);
            Helper::log_info(sprintf(__('%s - Data sent to Moova ONLY CP quote : %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
            Helper::log_info(sprintf(__('%s - Data received from Moova ONLY CP quote: %s', 'moova-for-woocommerce'), __FUNCTION__, json_encode($res)));
            return $res;
        }catch (Exception $error) { 
            return false;
        }
    }


    private static function format_payload_estimate($from, $to, $items)
    {
        return [
            'from' => [
                'floor' => $from['floor'],
                'apartment' => $from['apartment'],
                'address' => $from['address']
            ],
            'to' => $to,
            'items' => $items,
            'type' => 'regular'
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
        return $shippingPrice + Helper::get_option('extra_for_packaging', 0);
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
        if(isset(WC()->session)){
            WC()->session->set('shipping_error', !empty($res["addressErrors"]));
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
        $to = Helper::get_customer_from_cart($order);
        $orderItems = Helper::get_items_from_order($order);
        $scheduledDate = Helper::get_custom_shipping_type('schedule_date', $order);
        $scheduledDate = $scheduledDate ?  "$scheduledDate 01:00:00" : null;
        $parse = parse_url(get_site_url());
        $prefix = substr($parse['host'], 0, 3);
        return [
            'scheduledDate' => $scheduledDate,
            'currency' => get_woocommerce_currency(),
            'type' => 'regular',
            'flow' => 'manual',
            'from' => $seller,
            'to' => $to,
            'conf' => [
                'assurance' => false,
                'items' => $orderItems
            ],
            'internalCode' => $prefix . "-" . $order->get_id(),
            'description' => '',
            'label' => '',
            'type' => 'regular',
            'extra' => [],
            'settings' => [10],
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
        $res = $this->userApi->get("autocomplete?query=$query");
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
