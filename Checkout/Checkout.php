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
}
