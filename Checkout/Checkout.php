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
        wp_enqueue_style('wc-moova-checkout-css', Helper::get_assets_folder_url() . '/css/checkout.css');
        if ($key) {
            wp_enqueue_script('checkout', Helper::get_assets_folder_url() . '/js/checkout.js');
            wp_enqueue_script(
                'checkout-moova',
                "https://maps.googleapis.com/maps/api/js?key=$key&libraries=places&callback=initMap",
                [],
                false,
                true
            );
        }
    }

    public static function moova_override_default_address_fields($address_fields)
    {
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

        $address_fields['moova_schedule_date'] = array(
            'label' => __('Delivery Day', 'woocommerce'),
            'placeholder' => '',
            'type'  => 'date'
        );

        return $address_fields;
    }

    public function get_ajax_moova_custom_fields()
    {
        if (isset($_POST['lat'])) {
            WC()->session->set('moova_lat', sanitize_text_field($_POST['lat']));
            WC()->session->set('moova_lng', sanitize_text_field($_POST['lng']));
            echo sanitize_text_field($_POST['lat']);
        }
        die();
    }

    public static function refresh_shipping_methods()
    {
        $bool = true;
        if (WC()->session->get('billing_area') != '') $bool = false;

        // Mandatory to make it work with shipping methods
        foreach (WC()->cart->get_shipping_packages() as $package_key => $package) {
            WC()->session->set('shipping_for_package_' . $package_key, $bool);
        }
        WC()->cart->calculate_shipping();
    }

    public static function thank_you_message($str, $order)
    {
        /*$is_moova = true;
        if ($is_moova) {
            $str .= 'Segui tu envio : <a href=""> aqui</a>';
        }*/
        return $str;
    }

    public static function custom_items($order_id)
    {
        $order = wc_get_order($order_id);
        $shipping_method = Helper::get_shipping_method($order);
        if ($shipping_method) {
            $url = $shipping_method->get_meta('tracking_url');
            $message = "Segui tu envio y valida que tu direccion sea la correcta haciendo" .
                "<a style='color: #0272a9;' href='$url'> click aquí</a>";
            echo esc_html($message);
        }
    }
}
