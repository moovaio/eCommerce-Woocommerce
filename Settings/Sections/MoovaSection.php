<?php

namespace Ecomerciar\Moova\Settings\Sections;

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
        $this->data['name'] = __('Datos de Moova', 'wc-moova');
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
                'name' => 'Client ID',
                'slug' => 'clientid',
                'type' => 'text'
            ],
            'clientsecret' => [
                'name' => 'Client Secret',
                'slug' => 'clientsecret',
                'type' => 'text'
            ]
        ];
    }
}
