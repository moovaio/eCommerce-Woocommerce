<?php

namespace Ecomerciar\Moova\Settings\Mapping;

use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;

/**
 * MoovaSection class
 */
class ReceivingStatusSection extends Section implements SectionInterface
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
        $fields = [
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

        $moovaStatus = self::moovaStatus();
        $options = array_merge(['' => 'N/A'], wc_get_order_statuses());
        foreach ($moovaStatus as $state) {
            $fields["receive_$state"] = [
                'name' => __($state, 'wc-moova'),
                'slug' => "receive_$state",
                'description' => __("If you don't want to enable this state select N/A", 'wc-moova'),
                'type' => 'select',
                'options' => $options
            ];
        }

        return $fields;
    }

    private static function moovaStatus()
    {
        return [
            'DRAFT', 'READY', 'BLOCKED', 'WAITING',
            'CONFIRMED', 'PICKEDUP', 'INTRANSIT', 'DELIVERED', 'INCIDENCE',
            'CANCELED', 'RETURNED', 'TO,BERETURNED', 'WAITINGCLIENT'
        ];
    }
}
