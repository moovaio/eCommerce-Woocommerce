<?php

namespace Ecomerciar\Moova\Settings\GeneralSettings;

use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;

/**
 * IntegrationSection class
 */
class IntegrationSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-integration-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Integration settings', 'wc-moova');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        $fields = [
            'has_special_price' => [
                'name' => __('Offer a special price to the client', 'wc-moova'),
                'slug' => 'has_special_price',
                'description' => __('Offer a special price to the client', 'wc-moova'),
                'options' => [
                    'default' => __('Moova calculates the price', 'wc-moova'),
                    'fixed' => __('Fixed price', 'wc-moova'),
                    'range' => __('range price', 'wc-moova')
                ],
                'default' => 'default',
                'type' => 'select'
            ],
            'fixed_price' => [
                'name' => __('Fixed price', 'wc-moova'),
                'slug' => 'fixed_price',
                'description' => __('Offer this fixed price', 'wc-moova'),
                'type' => 'number'
            ],
            'min_price' => [
                'name' => __('Minimum price', 'wc-moova'),
                'slug' => 'min_price',
                'description' => __('Offer price from', 'wc-moova'),
                'type' => 'number'
            ],
            'max_price' => [
                'name' => __('Maximum price', 'wc-moova'),
                'slug' => 'max_price',
                'description' => __('Maximum price of the shipping', 'wc-moova'),
                'type' => 'number'
            ],
            'has_free_shipping' => [
                'name' => __('Free shipping', 'wc-moova'),
                'slug' => 'has_free_shipping',
                'description' => __('Offer free shipping if the order amount is bigger than this price', 'wc-moova'),
                'options' => [
                    '0' => 'No',
                    '1' => 'Si'
                ],
                'default' => '0',
                'type' => 'select'
            ],
            'free_shipping_price' => [
                'name' => __('Free shipping price', 'wc-moova'),
                'description' => __('Minimum amount to buy to get free shipping', 'wc-moova'),
                'slug' => 'free_shipping_price',
                'type' => 'number'
            ],
            'tracking' => [
                'name' => __('Tracking', 'wc-moova'),
                'slug' => 'tracking',
                'description' => __('This plugin offers a tracking form using the shortcode <strong>[moova_tracking_form]</strong>. You can use it in any page.', 'wc-moova'),
                'type' => 'description'
            ],
            'webhooks' => [
                'name' => __('Shipments notifications', 'wc-moova'),
                'slug' => 'webhooks',
                'description' => sprintf(__('In order to receive notifications about your Moova shipments, you need to create a webhook in your Moova dashboard, use this URL: <strong>%s</strong> with the POST method.', 'wc-moova'), get_site_url(null, '/wc-api/wc-moova-orders')),
                'type' => 'description'
            ],
            'environment' => [
                'name' => __('Environment', 'wc-moova'),
                'slug' => 'environment',
                'description' => __('', 'wc-moova'),
                'type' => 'select',
                'options' => [
                    'prod' => __('Production', 'wc-moova'),
                    'test' => __('Test', 'wc-moova')
                ]
            ],
            'debug' => [
                'name' => __('Debug Mode', 'wc-moova'),
                'slug' => 'debug',
                'description' => __('Activate the debug log for developers. If you do not know what is this then probably you do not need to activate it', 'wc-moova'),
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Si'
                ]
            ]
        ];
        return $fields;
    }
}
