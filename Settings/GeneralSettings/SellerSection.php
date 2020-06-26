<?php

namespace Ecomerciar\Moova\Settings\GeneralSettings;


use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;

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
        $this->data['name'] = __('Sender settings', 'wc-moova');
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
                'name' => __('Name', 'wc-moova'),
                'slug' => 'first_name',
                'type' => 'text'
            ],
            'last_name' => [
                'name' => __('Last name', 'wc-moova'),
                'slug' => 'last_name',
                'type' => 'text'
            ],
            'email' => [
                'name' => __('Email', 'wc-moova'),
                'slug' => 'email',
                'type' => 'text'
            ],
            'phone' => [
                'name' => __('Phone number', 'wc-moova'),
                'slug' => 'phone',
                'type' => 'text'
            ],
            'address' => [
                'name' => __('Street', 'wc-moova'),
                'slug' => 'address_autocomplete',
                'type' => 'text',
                'description' => __('Example: Lavalleja 297, Buenos Aires. Please select one of the options on the autocomplete', 'wc-moova')
            ],
            'google_place_id' => [
                'name' => __('PlaceId', 'wc-moova'),
                'slug' => 'google_place_id',
                'type' => 'text',
                'description' => __('', 'wc-moova')
            ],
            'floor' => [
                'name' => __('Floor', 'wc-moova'),
                'slug' => 'floor',
                'type' => 'text',
                'description' => __('Example: 2', 'wc-moova')
            ],
            'apartment' => [
                'name' => __('Apt. Number (Optional)', 'wc-moova'),
                'slug' => 'apartment',
                'type' => 'text',
                'description' => __('Example: A', 'wc-moova')
            ],
            'observations' => [
                'name' => __('Notes about the direction', 'wc-moova'),
                'slug' => 'observations',
                'type' => 'text',
                'description' => __('Example: Door bell does not work', 'wc-moova')
            ]
        ];
    }
}
