<?php

namespace Ecomerciar\Moova\Helper;

trait SettingsTrait
{
    public static function get_option(string $key, $default = false)
    {
        return get_option('wc-moova-' . $key, $default);
    }

    public static function get_seller_from_settings()
    {
        return [
            'street' => self::get_option('street'),
            'number' => self::get_option('street_number'),
            'floor' => self::get_option('floor'),
            'apartment' => self::get_option('apartment'),
            'city' => self::get_option('locality'),
            'state' => self::get_option('province'),
            'postalCode' => self::get_option('zipcode'),
            'country' => self::get_option('Argentina'),
            'instructions' => self::get_option('observations'),
            'contact' => [
                'firstName' => self::get_option('first_name'),
                'lastName' => self::get_option('last_name'),
                'email' => self::get_option('email'),
                'phone' => self::get_option('phone')
            ],
            'message' => ''
        ];
    }
}
