<?php

namespace Moova\Settings\Mapping;

use Moova\Settings\Sections\Section;
use Moova\Settings\Sections\SectionInterface;
use Moova\Helper\Helper;

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
        $this->data['name'] = __('Match moova status', 'moova-for-woocommerce');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        $fields = [];
        $moovaStatus = Helper::moova_status();
        $options = array_merge(['' => 'Disable option'], wc_get_order_statuses());
        foreach ($moovaStatus as $state) {
            $fields["receive_$state"] = [
                'name' => __($state, 'moova-for-woocommerce'),
                'slug' => "receive_$state",
                'description' => __("If you don't want to enable this state select 'Disable option'", 'moova-for-woocommerce'),
                'type' => 'select',
                'options' => $options
            ];
        }

        return $fields;
    }
}
