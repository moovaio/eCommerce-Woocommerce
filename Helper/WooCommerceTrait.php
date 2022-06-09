<?php

namespace Moova\Helper;

trait WooCommerceTrait
{

    /**
     * Gets the customer from a WooCommerce Cart
     *
     * @param WC_Customer $customer
     * @return array|false
     */
    public static function get_customer_from_cart($customer)
    {
        $first_name = self::get_customer_first_name($customer);
        $last_name = self::get_customer_last_name($customer);
        $postal_code = self::get_postal_code($customer);
        $street = self::get_address($customer);
        $province = self::get_province($customer);
        $locality = self::get_locality($customer);
        $country = $customer->get_shipping_country();
        $address = "$street,$province,$postal_code,$locality,$country";

        $destination = [
            "contact"=>[
                "name"=> $first_name,
                "lastName"=>$last_name,
                "email"=> $customer->get_billing_email(),
                "phone"=> $customer->get_billing_phone(),
            ],
            "apartment" => self::get_apartment($customer),
            "country" =>$country,
        ];
        
        if (self::get_custom_shipping_type('lat', $customer)) {
            return array_merge($destination, [
                "coords"=>[
                    "lat"=>self::get_custom_shipping_type('lat', $customer),
                    "lng"=>self::get_custom_shipping_type('lng', $customer)
                ],
                "addressDescription"=>$address
            ]);
        }

        return array_merge($destination, [
            "address"=>$address
        ]);
    }

    public static function get_custom_shipping_type($type, $customer)
    {
        if (session_status() == PHP_SESSION_NONE) {
            return $customer->get_meta("_billing_moova_$type");
        } elseif (isset(WC()->session)) {
            return WC()->session->get("moova_$type");
        }
        return '';
    }

    public static function get_shipping_method($order)
    {
        if (!$order->has_shipping_method('moova')) {
            return null;
        }

        $method = null;
        foreach ($order->get_shipping_methods() as $shipping_method) {
            $is_moova = ($shipping_method['method_id'] === 'moova');
            if ($is_moova && $shipping_method->get_meta('tracking_number')) {
                return $shipping_method;
            } elseif ($is_moova) {
                $method = $shipping_method;
            }
        }

        return $method;
    }

    public static function get_province($customer)
    {
        $province = strtolower(self::get_province_wc($customer));
        $map = [
            'metropolitana de santiago' => 'Region Metropolitana de Santiago'
        ];
        return isset($map[$province]) ?  $map[$province] : $province;
    }
    /**
     * Gets the province from a customer
     *
     * @param WC_Customer $customer
     * @return string
     */
    private static function get_province_wc($customer)
    {
        $province = '';
        if (!($province = $customer->get_shipping_state())) {
            $province = $customer->get_billing_state();
        }

        $country = $customer->get_shipping_country();
        $states =  WC()->countries->get_shipping_country_states();
        if (!isset($states[$country])) {
            return $province;
        }

        $stateOptions = $states[$country];
        if (isset($stateOptions[$province])) {
            return $stateOptions[$province];
        }
        return $province;
    }

    /**
     * Gets the locality from a customer
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_locality($customer)
    {
        $locality = '';
        if (!($locality = $customer->get_shipping_city())) {
            $locality = $customer->get_billing_city();
        }
        return $locality;
    }

    /**
     * Gets the postal code from a customer
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_postal_code($customer)
    {
        $postal_code = '';
        if (!($postal_code = $customer->get_shipping_postcode())) {
            $postal_code = $customer->get_billing_postcode();
        }
        return $postal_code;
    }

    /**
     * Gets the customer first name
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_customer_first_name($customer)
    {
        $name = '';
        if ($customer->get_shipping_first_name()) {
            $name = $customer->get_shipping_first_name();
        } else {
            $name = $customer->get_billing_first_name();
        }
        return $name;
    }

    /**
     * Gets the customer last name
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_customer_last_name($customer)
    {
        $name = '';
        if ($customer->get_shipping_last_name()) {
            $name = $customer->get_shipping_last_name();
        } else {
            $name = $customer->get_billing_last_name();
        }
        return $name;
    }

    /**
     * Gets the address of an order
     *
     * @param WC_Order $order
     * @return false|array
     */
    public static function get_address($order)
    {
        if (!$order) {
            return false;
        }
        if ($order->get_shipping_address_1()) {
            return $order->get_shipping_address_1();
        }
        return $order->get_billing_address_1();
    }

    /**
     * Gets the address of an order
     *
     * @param WC_Order $order
     * @return false|array
     */
    public static function get_apartment($order)
    {
        if (!$order) {
            return false;
        }
        if ($order->get_shipping_address_2()) {
            return $order->get_shipping_address_2();
        }
        return $order->get_billing_address_2();
    }
    /**
     * Gets product dimensions and details
     *
     * @param int $product_id
     * @return false|array
     */
    public static function get_product_dimensions($product_id, $quantity = 1)
    {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }
        $dimension_unit = 'cm';
        $weight_unit = 'g';
        $new_product = array(
            'height' => round(
                wc_get_dimension(floatval($product->get_height()), $dimension_unit),
                2
            ),
            'width' => round(
                wc_get_dimension(floatval($product->get_width()), $dimension_unit),
                2
            ),
            'length' => round(
                wc_get_dimension(floatval($product->get_length()), $dimension_unit),
                2
            ),
            'weight' => round(
                wc_get_weight(floatval($product->get_weight()), $weight_unit),
                2
            ),
            'price' => $product->get_price(),
            'name' => $product->get_name(),
            'quantity' => $quantity
        );
        return ["item" => $new_product];
    }

    /**
     * Gets all items from a cart
     *
     * @param WC_Cart $cart
     * @return false|array
     */
    public static function get_items_from_cart($cart)
    {
        $products = array();
        $items = $cart->get_cart();
        foreach ($items as $item) {
            $product_id = $item['data']->get_id();
            $new_product = self::get_product_dimensions($product_id, $item['quantity']);
            $products[] = $new_product;
        }
        return $products;
    }

    /*
    */
    public static function get_items_per_package($package)
    {
        $parsed_items = [];
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            $parsed_items[] = self::get_product_dimensions($product_id, $item['quantity']);
        }
        return $parsed_items;
    }

    /**
     * Gets items by vendor
     *
     * @param WC_Cart $cart
     * @return false|array
     */
    public static function divide_items_per_vendor($cart)
    {
        $vendor_items = array();
        $items = $cart->get_cart();
        foreach ($items as $item) {
            $product_id = $item['data']->get_id();
            $new_product = self::get_product_dimensions($product_id, $item['quantity']);
            $product = wc_get_product($product_id);
            $vendor_id = $product->post->post_author;
            if (!isset($vendor_items[$vendor_id])) {
                $vendor_items[$vendor_id] = [];
            }
            $vendor_items[$vendor_id][] = $new_product;
        }
        return $vendor_items;
    }

    /**
     * Gets items from an order
     *
     * @param WC_Order $order
     * @return false|array
     */
    public static function get_items_from_order($order)
    {
        $products = array();
        $items = $order->get_items();
        foreach ($items as $item) {
            $product_id = $item->get_variation_id();
            if (!$product_id) {
                $product_id = $item->get_product_id();
            }
            $new_product = self::get_product_dimensions($product_id, $item['quantity']);
            $products[] = $new_product;
        }
        return $products;
    }
}
