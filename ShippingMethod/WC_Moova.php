<?php

namespace Ecomerciar\Moova\ShippingMethod;

use Ecomerciar\Moova\Helper\Helper;
use Ecomerciar\Moova\Sdk\MoovaSdk;

defined('ABSPATH') || class_exists('\WC_Shipping_method') || exit;

/**
 * Our main payment method class
 */
class WC_Moova extends \WC_Shipping_method
{
    /**
     * Default constructor, loads settings and MercadoPago's SDK
     */
    public function __construct($instance_id = 0)
    {
        $this->instance_id = absint($instance_id);
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        // Setup general properties.
        $this->setup_properties();
    }

    /**
     * Establishes default settings, and loads IPN Processor
     *
     * @return void
     */
    private function setup_properties()
    {
        $this->id = 'moova';
        $this->method_title = 'Moova';
        $this->method_description = __('Allow your customers receive their orders with Moova', 'wc-moova');
        $this->title = $this->get_option('title');
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal'
        );
    }

    /**
     * Declares our instance configuration
     *
     * @return void
     */
    public function init_form_fields()
    {
        $this->instance_form_fields = [
            'title' => [
                'title' => __('Title', 'woocommerce'),
                'type' => 'text',
                'description' => __('Choose the name that your customers will see in the checkout', 'wc-moova'),
                'default' => __('Moova', 'wc-moova')
            ],
            'free_shipping' => [
                'title' => __('Free shipping', 'wc-moova'),
                'type' => 'checkbox'
            ]
        ];
    }

    /**
     * Calculates the shipping
     *
     * @return void
     */
    public function calculate_shipping($package = [])
    {
        if (is_plugin_active('dokan-lite/dokan.php')) {
            $rate = $this->get_rate_dokan();
        } else {
            $rate = $this->get_single_origin_rate();
        }

        if (!empty($rate) || $rate === 0) {
            $this->add_rate([
                'id'        => $this->get_rate_id(), // ID for the rate. If not passed, this id:instance default will be used.
                'label'     => $this->title, // Label for the rate.
                'cost'      => $rate // Amount or array of costs (per item shipping).
            ]);
        }
    }

    public function get_rate_dokan()
    {
        $moovaSdk = new MoovaSdk();
        $final_price = 0;
        $items_per_vendor = Helper::divide_items_per_vendor(WC()->cart);
        $customer = Helper::get_customer_from_cart(WC()->customer);
        $vendor_list = array_keys($items_per_vendor);
        foreach ($vendor_list as $vendor_id) {
            $vendor_cart = $items_per_vendor[$vendor_id];
            $vendor = Helper::get_dokan_seller_by_id($vendor_id);
            $format_origin = Helper::format_dokan_origin_to_moova($vendor);
            $price = $moovaSdk->get_price($format_origin, $customer, $vendor_cart);
            if (!$price && $price != 0) return;
            $final_price += $price;
        }
        return $final_price;
    }

    public function get_single_origin_rate()
    {
        $moovaSdk = new MoovaSdk();
        $seller = Helper::get_seller_from_settings();
        $items = Helper::get_items_from_cart(WC()->cart);
        $customer = Helper::get_customer_from_cart(WC()->customer);
        $unable_to_calculate = empty($seller) || empty($customer) || !empty($items);
        if ($unable_to_calculate) {
            return null;
        }
        return $moovaSdk->get_price($seller, $customer, $items);
    }
}
