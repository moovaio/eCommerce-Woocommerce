<?php

namespace Ecomerciar\Moova\Helper;

trait SettingsTrait
{
    /**
     * Gets a plugin option
     *
     * @param string $key
     * @param boolean $default
     * @return mixed
     */
    public static function get_option(string $key, $default = false)
    {
        return get_option('wc-moova-' . $key, $default);
    }

    /**
     * Gets the seller settings
     *
     * @return array
     */
    public static function get_seller_from_settings()
    {
        if (self::get_option('google_place_id')) {
            $address = [
                'googlePlaceId' => self::get_option('google_place_id'),
                'address' => self::get_option('address_autocomplete'),
            ];
        } else {
            $address = [
                'street' => self::get_option('street'),
                'number' => self::get_option('street_number'),
                'city' => self::get_option('locality'),
                'state' => self::get_option('province'),
                'postalCode' => self::get_option('zipcode'),
                'country' => self::get_option('Argentina'),
            ];
        }
        return array_merge($address, [
            'floor' => self::get_option('floor'),
            'apartment' => self::get_option('apartment'),
            'instructions' => self::get_option('observations'),
            'contact' => [
                'firstName' => self::get_option('first_name'),
                'lastName' => self::get_option('last_name'),
                'email' => self::get_option('email'),
                'phone' => self::get_option('phone')
            ],
            'message' => ''
        ]);
    }
}
