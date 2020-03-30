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
            'street' => [
                'name' => __('Street', 'wc-moova'),
                'slug' => 'street',
                'type' => 'text',
                'description' => __('Example: Av. Belgrano', 'wc-moova')
            ],
            'street_number' => [
                'name' => __('Street number', 'wc-moova'),
                'slug' => 'street_number',
                'type' => 'text',
                'description' => __('Example: 520', 'wc-moova')
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
            'locality' => [
                'name' => __('Locality', 'wc-moova'),
                'slug' => 'locality',
                'type' => 'text',
                'description' => __('Example: Palermo', 'wc-moova')
            ],
            'province' => [
                'name' => __('State', 'wc-moova'),
                'slug' => 'province',
                'type' => 'text',
                'description' => __('Example: Capital Federal', 'wc-moova')
            ],
            'zipcode' => [
                'name' => __('Postal code', 'wc-moova'),
                'slug' => 'zipcode',
                'type' => 'text',
                'description' => __('Example: 1040', 'wc-moova')
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
