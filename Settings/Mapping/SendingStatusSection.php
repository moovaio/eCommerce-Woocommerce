<?php

namespace Ecomerciar\Moova\Settings\Mapping;

use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;
use Ecomerciar\Moova\Helper\Helper;

/**
 * MoovaSection class
 */
class SendingStatusSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-cds-sending-status'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Inform to Moova', 'wc-moova');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        $status = wc_get_order_statuses();
        $fields =
            [
                'status_processing' => [
                    'name' => __('Status to process', 'wc-moova'),
                    'slug' => 'status_processing',
                    'description' => __('When an order has this status, it gets processed automatically with Moova.', 'wc-moova'),
                    'type' => 'select',
                    'default' => 'wc-completed',
                    'options' => array_merge(
                        ['0' => __('Disable automatic processing', 'wc-moova')],
                        $status
                    )
                ],

                'notify_cancel' => [
                    'name' => __('status to cancel', 'wc-moova'),
                    'slug' => "notify_cancel",
                    'description' => __("If you don't want to notify when cancelled change to N/A", 'wc-moova'),
                    'type' => 'select',
                    'default' => 'wc-cancelled',
                    'options' => $status
                ]

            ];

        return $fields;
    }
}
