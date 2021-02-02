<?php

namespace Ecomerciar\Moova\Checkout;

use Ecomerciar\Moova\Helper\Helper;

/**
 * Our main payment method class
 */
class Checkout
{
    /**
     * Register all scripts in checkout
     * 
     * @return void
     */
    public static function register_scripts()
    {
        $key = Helper::get_option('google_api_key');
        if ($key) {
            wp_enqueue_script('checkout', Helper::get_assets_folder_url() . '/js/checkout.js');
            wp_enqueue_script(
                'checkout-moova',
                "https://maps.googleapis.com/maps/api/js?key=$key&libraries=places&callback=initMap",
                [],
                false,
                true
            );
            wp_enqueue_style('wc-moova-checkout-css', Helper::get_assets_folder_url() . '/css/checkout.css');
        }
    }


    public static function moova_override_default_address_fields($address_fields)
    {

        $temp_fields = array();

        $address_fields['moova_lat'] = array(
            'label' => __('Latitude', 'woocommerce'),
            'placeholder' => '',
            'type'  => 'text',
            'class'      => array('form-row-wide', 'address-field'),
        );
        $address_fields['moova_lng'] = array(
            'label' => __('Longitude', 'woocommerce'),
            'placeholder' => '',
            'type'  => 'text'
        );

        return $address_fields;
    }

    public function get_ajax_moova_custom_fields()
    {
        if (isset($_POST['lat'])) {
            WC()->session->set('moova_lat', esc_attr($_POST['lat']));
            WC()->session->set('moova_lng', esc_attr($_POST['lng']));
            echo $_POST['lat'];
        }
        die();
    }

    public function refresh_shipping_methods()
    {
        $bool = true;
        if (WC()->session->get('billing_area') != '') $bool = false;

        // Mandatory to make it work with shipping methods
        foreach (WC()->cart->get_shipping_packages() as $package_key => $package) {
            WC()->session->set('shipping_for_package_' . $package_key, $bool);
        }
        WC()->cart->calculate_shipping();
    }
}
