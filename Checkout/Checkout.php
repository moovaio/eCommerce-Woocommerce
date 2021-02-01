<?php

namespace Ecomerciar\Moova\Checkout;

/**
 * Our main payment method class
 */
class Checkout
{
    function wdm_override_default_address_fields($address_fields)
    {

        $temp_fields = array();

        $address_fields['moova_lat'] = array(
            'label' => __('Latitude', 'woocommerce'),
            'placeholder' => '',
            'type'  => 'text'
        );
        $address_fields['moova_lng'] = array(
            'label' => __('Longitude', 'woocommerce'),
            'placeholder' => '',
            'type'  => 'text'
        );

        return $address_fields;
    }

    function get_ajax_moova_custom_fields()
    {
        if (isset($_POST['lat'])) {
            WC()->session->set('moova_lat', esc_attr($_POST['lat']));
            WC()->session->set('moova_lng', esc_attr($_POST['lng']));
            echo $_POST['lat'];
        }
        die();
    }

    function refresh_shipping_methods()
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
