<?php

namespace Ecomerciar\Moova\Helper;

trait SettingsTrait
{
    /**
     * Gets a plugin option
     *
     * @param string $key
     * @param boolean $default
     * @return mixed
     */
    public static function get_option(string $key, $default = false)
    {
        return get_option('wc-moova-' . $key, $default);
    }

    /**
     * Gets the seller settings
     *
     * @return array
     */
    public static function get_seller_from_settings($order = null)
    {

        if (is_plugin_active('dokan-lite/dokan.php') && $order) {
            $store = self::getDokanStore($order);
            Helper::log_info("Store get seller settings" . json_encode($store));
            return [
                'address' => $store['address']['street_1'] . $store['address']['city'],
                'country' => $store['address']['country'],
                'floor' => $store['address']['street_2'],
                'contact' => [
                    'firstName' => $store['store_name'],
                    'phone' => $store['phone']
                ],
            ];
        } elseif (self::get_option('google_place_id')) {
            $address = [
                'googlePlaceId' => self::get_option('google_place_id'),
                'address' => self::get_option('address_autocomplete'),
            ];
        }
        return array_merge($address, [
            'floor' => self::get_option('floor'),
            'apartment' => self::get_option('apartment'),
            'instructions' => self::get_option('observations'),
            'contact' => [
                'firstName' => self::get_option('first_name'),
                'lastName' => self::get_option('last_name'),
                'email' => self::get_option('email'),
                'phone' => self::get_option('phone')
            ],
            'message' => ''
        ]);
    }

    /**
     * Populate Dokan store info
     *
     * @return void
     */
    public static function getDokanStore($order)
    {
        global $wpdb;
        $row = $wpdb->get_row(
            "SELECT seller_id FROM wp_2.wp_dokan_orders WHERE order_id={$order->id}",
            ARRAY_A
        );
        $storeId = $row['seller_id'];
        $defaults = array(
            'store_name'              => '',
            'social'                  => array(),
            'payment'                 => array('paypal' => array('email'), 'bank' => array()),
            'phone'                   => '',
            'show_email'              => 'no',
            'address'                 => array(),
            'location'                => '',
            'banner'                  => 0,
            'icon'                    => 0,
            'gravatar'                => 0,
            'show_more_ptab'          => 'yes',
            'store_ppp'               => 10,
            'enable_tnc'              => 'off',
            'store_tnc'               => '',
            'show_min_order_discount' => 'no',
            'store_seo'               => array(),
            'dokan_store_time_enabled' => 'yes',
            'dokan_store_open_notice'  => '',
            'dokan_store_close_notice' => ''
        );

        $shop_info = get_user_meta($storeId, 'dokan_profile_settings', true);
        Helper::log_info("shop_info:" . json_encode($shop_info));
        $shop_info = is_array($shop_info) ? $shop_info : array();
        $shop_info = wp_parse_args($shop_info, $defaults);

        return apply_filters('dokan_vendor_shop_data', $shop_info);
    }
}
