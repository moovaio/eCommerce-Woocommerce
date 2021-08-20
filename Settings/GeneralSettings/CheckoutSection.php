<?php

namespace Moova\Settings\GeneralSettings;

use Moova\Settings\Sections\Section;
use Moova\Settings\Sections\SectionInterface;

/**
 * CheckoutSection class
 */
class CheckoutSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-checkout-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Checkout settings', 'wc-moova');
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
            'show_moova_image_checkout' => [
                'name' => __('Show moova image in checkout', 'wc-moova'),
                'slug' => 'show_moova_image_checkout',
                'description' => __('In the checkout we will show the moova icon', 'wc-moova'),
                'options' => [
                    '0' => 'No',
                    '1' => 'Yes'
                ],
                'default' => '1',
                'type' => 'select'
            ],
            'moova_shipping_days' => [
                'name' => __('How many days it takes to send package to moova', 'wc-moova'),
                'slug' => 'moova_shipping_days',
                'description' => __('If you take an extra time to make the packaging and to send it to moova write it here in days', 'wc-moova'),
                'type' => 'number',
                'default' => 0,
            ],
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
            ]
        ];
        return $fields;
    }
}
