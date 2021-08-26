<?php

namespace Moova\Settings;

defined('ABSPATH') || exit;

use Moova\Sdk\MoovaSdk;
use Moova\Helper\Helper;

class FormAjax
{
    public static function autocomplete()
    {
        try {
            if (!wp_verify_nonce($_POST['nonce'], 'moova-for-woocommerce')) {
                wp_send_json_error();
            }

            $query = sanitize_text_field($_POST['query']['term']);
            $moovaSdk = new MoovaSdk();
            $response = $moovaSdk->autocomplete($query);
            wp_send_json(["data" => $response]);

            die();
        } catch (\Throwable $th) {
            Helper::log_info($th);
            return false;
        }
    }

    public static function rate()
    {
        $rate_action = sanitize_text_field($_POST['rate_action']);
        $minShippings = get_option('wc-moova-min-shippings');
        if ('done-rating' === $rate_action) {
            $minShippings = -1;
        } else {
            switch ($minShippings) {
                case 10:
                    $minShippings = 30;
                    break;
                case 30:
                    $minShippings = 50;
                    break;
                case 50:
                    $minShippings = 100;
                    break;
                case 100:
                    $minShippings = -1;
                    break;
            }
        }
        update_option('wc-moova-min-shippings', $minShippings);

        echo esc_textarea(1);
        exit;
    }
}
