<?php

namespace Moova\Settings\GeneralSettings;


use Moova\Settings\Sections\Section;
use Moova\Settings\Sections\SectionInterface;

/**
 * SellerSection class
 */
class SellerSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-seller-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Sender settings', 'moova-for-woocommerce');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        return [
            'first_name' => [
                'name' => __('Name', 'moova-for-woocommerce'),
                'slug' => 'first_name',
                'type' => 'text'
            ],
            'last_name' => [
                'name' => __('Last name', 'moova-for-woocommerce'),
                'slug' => 'last_name',
                'type' => 'text'
            ],
            'email' => [
                'name' => __('Email', 'moova-for-woocommerce'),
                'slug' => 'email',
                'type' => 'text'
            ],
            'phone' => [
                'name' => __('Phone number', 'moova-for-woocommerce'),
                'slug' => 'phone',
                'type' => 'text'
            ],
            'address_autocomplete' => [
                'name' => __('Street', 'moova-for-woocommerce'),
                'slug' => 'address_autocomplete',
                'type' => 'text',
                'description' => __('Example: Lavalleja 297, Buenos Aires. Please select one of the options on the autocomplete', 'moova-for-woocommerce')
            ],
            'google_place_id' => [
                'name' => __('PlaceId', 'moova-for-woocommerce'),
                'slug' => 'google_place_id',
                'type' => 'text',
                'description' => __('', 'moova-for-woocommerce')
            ],
            'floor' => [
                'name' => __('Floor', 'moova-for-woocommerce'),
                'slug' => 'floor',
                'type' => 'text',
                'description' => __('Example: 2', 'moova-for-woocommerce')
            ],
            'apartment' => [
                'name' => __('Apt. Number (Optional)', 'moova-for-woocommerce'),
                'slug' => 'apartment',
                'type' => 'text',
                'description' => __('Example: A', 'moova-for-woocommerce')
            ],
            'observations' => [
                'name' => __('Notes about the direction', 'moova-for-woocommerce'),
                'slug' => 'observations',
                'type' => 'text',
                'description' => __('Example: Door bell does not work', 'moova-for-woocommerce')
            ]
        ];
    }
}
