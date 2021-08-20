<?php

namespace Moova\Settings\Mapping;

use Moova\Settings\Sections\Section;
use Moova\Settings\Sections\SectionInterface; 

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
                'status_ready' => [
                    'name' => __('Status to send a moover', 'wc-moova'),
                    'slug' => 'status_ready',
                    'description' => __('When an order has this status, its receive a moover', 'wc-moova'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array_merge(
                        ['0' => __('Disable option', 'wc-moova')],
                        $status
                    )
                ]
            ];

        return $fields;
    }
}
