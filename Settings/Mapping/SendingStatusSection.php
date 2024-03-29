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
        $this->data['name'] = __('Inform to Moova', 'moova-for-woocommerce');
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
                    'name' => __('Status to send a moover', 'moova-for-woocommerce'),
                    'slug' => 'status_ready',
                    'description' => __('When an order has this status, its receive a moover', 'moova-for-woocommerce'),
                    'type' => 'select',
                    'default' => '0',
                    'options' => array_merge(
                        ['0' => __('Disable option', 'moova-for-woocommerce')],
                        $status
                    )
                ]
            ];

        return $fields;
    }
}
