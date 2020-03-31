<?php

namespace Ecomerciar\Moova\Settings\Mapping;

use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;
use Ecomerciar\Moova\Helper\Helper;

/**
 * MoovaSection class
 */
class ReceivingStatusSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-cds-receiving-status'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Match moova status', 'wc-moova');
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
                'name' => __('Enable matching', 'wc-moova'),
                'slug' => 'is_mapping_froom_moova_enabled',
                'description' => __('When receiving this status from moova we will change the order state!', 'wc-moova'),
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Si',
                ]
            ]
        ];

        $moovaStatus = Helper::moova_status();
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
}
