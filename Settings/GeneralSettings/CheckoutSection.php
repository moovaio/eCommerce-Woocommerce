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
        $this->data['name'] = __('Checkout settings', 'moova-for-woocommerce');
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
            'enable_postal_codes_quotes' => [
                'name' => __('Enable postal code budgets as priority', 'moova-for-woocommerce'),
                'slug' => 'enable_postal_codes_quotes',
                'description' => __('This will try to quote with postal codes first and address second', 'moova-for-woocommerce'),
                'options' => [
                    '0' => 'No',
                    '1' => 'Yes'
                ],
                'default' => '1',
                'type' => 'select'
            ],
            'show_moova_image_checkout' => [
                'name' => __('Show moova image in checkout', 'moova-for-woocommerce'),
                'slug' => 'show_moova_image_checkout',
                'description' => __('In the checkout we will show the moova icon', 'moova-for-woocommerce'),
                'options' => [
                    '0' => 'No',
                    '1' => 'Yes'
                ],
                'default' => '1',
                'type' => 'select'
            ],
            'moova_shipping_days' => [
                'name' => __('How many days it takes to send package to moova', 'moova-for-woocommerce'),
                'slug' => 'moova_shipping_days',
                'description' => __('If you take an extra time to make the packaging and to send it to moova write it here in days', 'moova-for-woocommerce'),
                'type' => 'number',
                'default' => 0,
            ],
            'has_special_price' => [
                'name' => __('Offer a special price to the client', 'moova-for-woocommerce'),
                'slug' => 'has_special_price',
                'description' => __('Offer a special price to the client', 'moova-for-woocommerce'),
                'options' => [
                    'default' => __('Moova calculates the price', 'moova-for-woocommerce'),
                    'fixed' => __('Fixed price', 'moova-for-woocommerce'),
                    'range' => __('range price', 'moova-for-woocommerce')
                ],
                'default' => 'default',
                'type' => 'select'
            ],
            'fixed_price' => [
                'name' => __('Fixed price', 'moova-for-woocommerce'),
                'slug' => 'fixed_price',
                'description' => __('Offer this fixed price', 'moova-for-woocommerce'),
                'type' => 'number'
            ],
            'min_price' => [
                'name' => __('Minimum price', 'moova-for-woocommerce'),
                'slug' => 'min_price',
                'description' => __('Offer price from', 'moova-for-woocommerce'),
                'type' => 'number'
            ],
            'max_price' => [
                'name' => __('Maximum price', 'moova-for-woocommerce'),
                'slug' => 'max_price',
                'description' => __('Maximum price of the shipping', 'moova-for-woocommerce'),
                'type' => 'number'
            ],
            'has_free_shipping' => [
                'name' => __('Free shipping', 'moova-for-woocommerce'),
                'slug' => 'has_free_shipping',
                'description' => __('Offer free shipping if the order amount is bigger than this price', 'moova-for-woocommerce'),
                'options' => [
                    '0' => 'No',
                    '1' => 'Si'
                ],
                'default' => '0',
                'type' => 'select'
            ],
            'free_shipping_price' => [
                'name' => __('Free shipping price', 'moova-for-woocommerce'),
                'description' => __('Minimum amount to buy to get free shipping', 'moova-for-woocommerce'),
                'slug' => 'free_shipping_price',
                'type' => 'number'
            ],
            'tracking' => [
                'name' => __('Tracking', 'moova-for-woocommerce'),
                'slug' => 'tracking',
                'description' => __('This plugin offers a tracking form using the shortcode <strong>[moova_tracking_form]</strong>. You can use it in any page.', 'moova-for-woocommerce'),
                'type' => 'description'
            ]
        ];
        return $fields;
    }
}
