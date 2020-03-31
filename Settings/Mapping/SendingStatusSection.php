<?php

namespace Ecomerciar\Moova\Settings\Mapping;
use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;

/**
 * MoovaSection class
 */
class StatusSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-cds-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Moova mapping', 'wc-moova');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        $fields= [
            'country' => [
                'name' => __('Enable Mapping', 'wc-moova'),
                'slug' => 'is_mapping_froom_moova_enabled',
                'description' => __('When receiving this status to moova we will change the order state', 'wc-moova'),
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Si',
                ]
            ]
        ];

        return $fields;
    }
}
