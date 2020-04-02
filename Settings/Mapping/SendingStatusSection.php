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
                        ['0' => __('Disable option', 'wc-moova')],
                        $status
                    )
                ],

                'status_ready' => [
                    'name' => __('Status to initiate', 'wc-moova'),
                    'slug' => 'status_ready',
                    'description' => __('When an order has this status, its ready do be delivered by moova.', 'wc-moova'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array_merge(
                        ['0' => __('Disable option', 'wc-moova')],
                        $status
                    )
                ],

                'status_cancel' => [
                    'name' => __('status to cancel', 'wc-moova'),
                    'slug' => "status_cancel",
                    'description' => __("If you don't want to notify when cancelled change to Disable option", 'wc-moova'),
                    'type' => 'select',
                    'default' => 'wc-cancelled',
                    'options' => array_merge(
                        ['0' => __('Disable option', 'wc-moova')],
                        $status
                    )
                ]

            ];

        return $fields;
    }
}
