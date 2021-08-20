<?php

namespace Moova\Settings\GeneralSettings;


use Moova\Settings\Sections\Section;
use Moova\Settings\Sections\SectionInterface;

/**
 * MoovaSection class
 */
class MoovaSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-cds-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Moova settings', 'moova-for-woocommerce');
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
            'clientid' => [
                'name' => __('Client ID', 'moova-for-woocommerce'),
                'slug' => 'clientid',
                'type' => 'text'
            ],
            'clientsecret' => [
                'name' => __('Client Secret', 'moova-for-woocommerce'),
                'slug' => 'clientsecret',
                'type' => 'text'
            ],
            'devclientid' => [
                'name' => __('Testing Client ID', 'moova-for-woocommerce'),
                'slug' => 'devclientid',
                'type' => 'text'
            ],
            'devclientsecret' => [
                'name' => __('Testing Client Secret', 'moova-for-woocommerce'),
                'slug' => 'devclientsecret',
                'type' => 'text'
            ],
            'google_api_key' => [
                'name' => __('Google Api Key', 'moova-for-woocommerce'),
                'slug' => 'google_api_key',
                'type' => 'text'
            ],

            'environment' => [
                'name' => __('Environment', 'moova-for-woocommerce'),
                'slug' => 'environment',
                'description' => __('', 'moova-for-woocommerce'),
                'type' => 'select',
                'options' => [
                    'prod' => __('Production', 'moova-for-woocommerce'),
                    'test' => __('Test', 'moova-for-woocommerce')
                ]
            ],
            'debug' => [
                'name' => __('Debug Mode', 'moova-for-woocommerce'),
                'slug' => 'debug',
                'description' => __('Activate the debug log for developers. If you do not know what is this then probably you do not need to activate it', 'moova-for-woocommerce'),
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Si'
                ]
            ]

        ];
    }
}
