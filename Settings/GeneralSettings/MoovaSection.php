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
            'country' => [
                'name' => __('Country where you operate', 'wc-moova'),
                'slug' => 'country',
                'type' => 'select',
                'options' => [
                    'AR' => 'Argentina',
                    'CL' => 'Chile',
                    'UY'=> 'Uruguay',
                    'GT'=> 'Guatemala',
                    'MX'=> 'México',
                ]
            ]
        ];
    }
}
