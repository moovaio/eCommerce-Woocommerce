<?php

namespace Ecomerciar\Moova\Settings\GeneralSettings;

use Ecomerciar\Moova\Settings\Sections\Section;
use Ecomerciar\Moova\Settings\Sections\SectionInterface;

/**
 * IntegrationSection class
 */
class IntegrationSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-moova-integration-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Integration settings', 'wc-moova');
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
            'tracking' => [
                'name' => __('Tracking', 'wc-moova'),
                'slug' => 'tracking',
                'description' => __('This plugin offers a tracking form using the shortcode <strong>[moova_tracking_form]</strong>. You can use it in any page.', 'wc-moova'),
                'type' => 'description'
            ],
            'webhooks' => [
                'name' => __('Shipments notifications', 'wc-moova'),
                'slug' => 'webhooks',
                'description' => sprintf(__('In order to receive notifications about your Moova shipments, you need to create a webhook in your Moova dashboard, use this URL: <strong>%s</strong> with the POST method.', 'wc-moova'), get_site_url(null, '/wc-api/wc-moova-orders')),
                'type' => 'description'
            ],
            'environment' => [
                'name' => __('Environment', 'wc-moova'),
                'slug' => 'environment',
                'description' => __('', 'wc-moova'),
                'type' => 'select',
                'options' => [
                    'prod' => __('Production', 'wc-moova'),
                    'test' => __('Test', 'wc-moova')
                ]
            ],
            'debug' => [
                'name' => __('Debug Mode', 'wc-moova'),
                'slug' => 'debug',
                'description' => __('Activate the debug log for developers. If you do not know what is this then probably you do not need to activate it', 'wc-moova'),
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Si'
                ]
            ]
        ];
        return $fields;
    }
}