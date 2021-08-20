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
        $this->data['name'] = __('Moova settings', 'wc-moova');
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
                'name' => __('Client ID', 'wc-moova'),
                'slug' => 'clientid',
                'type' => 'text'
            ],
            'clientsecret' => [
                'name' => __('Client Secret', 'wc-moova'),
                'slug' => 'clientsecret',
                'type' => 'text'
            ],
            'devclientid' => [
                'name' => __('Testing Client ID', 'wc-moova'),
                'slug' => 'devclientid',
                'type' => 'text'
            ],
            'devclientsecret' => [
                'name' => __('Testing Client Secret', 'wc-moova'),
                'slug' => 'devclientsecret',
                'type' => 'text'
            ],
            'google_api_key' => [
                'name' => __('Google Api Key', 'wc-moova'),
                'slug' => 'google_api_key',
                'type' => 'text'
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
    }
}
