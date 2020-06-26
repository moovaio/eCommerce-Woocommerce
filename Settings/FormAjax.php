<?php

namespace Ecomerciar\Moova\Settings;

defined('ABSPATH') || exit;

use Ecomerciar\Moova\Sdk\MoovaSdk;

class FormAjax
{
    public function autocomplete()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'wc-moova')) {
            wp_send_json_error();
        }

        $query = filter_var($_POST['query']['term'], FILTER_SANITIZE_STRING);
        $moovaSdk = new MoovaSdk();
        $response = $moovaSdk->autocomplete($query);
        wp_send_json(["data" => $response]);

        die();
    }
}
