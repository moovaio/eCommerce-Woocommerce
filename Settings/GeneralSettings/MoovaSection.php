<?php

namespace Ecomerciar\Moova\Settings\GeneralSettings;


use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;

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

        ];
    }
}
