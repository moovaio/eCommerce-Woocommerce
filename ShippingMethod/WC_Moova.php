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
        $this->method_description = __('Permite a tus clientes recibir sus pedidos con Moova', 'wc-moova');
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
                'description' => __('Elige el nombre que verán tus clientes en la sección del checkout', 'wc-moova'),
                'default' => __('Moova', 'wc-moova')
            ],
            'free_shipping' => [
                'title' => __('Envío gratis', 'woocommerce'),
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
        $seller = Helper::get_seller_from_settings();
        $customer = Helper::get_customer_from_cart(WC()->customer);
        $items = Helper::get_items_from_cart(WC()->cart);
        if ($items === false) {
            return;
        }
        $moovaSdk = new MoovaSdk();
        $price = $moovaSdk->get_price($seller, $customer, $items);
        if (!empty($price)) {
            $this->add_rate([
                'id'        => $this->get_rate_id(), // ID for the rate. If not passed, this id:instance default will be used.
                'label'     => $this->title, // Label for the rate.
                'cost'      => ($this->get_instance_option('free_shipping') === 'yes' ? 0 : $price['price']) // Amount or array of costs (per item shipping).
            ]);
        }
    }
}
