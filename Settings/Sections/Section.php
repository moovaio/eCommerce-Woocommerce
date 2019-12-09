<?php

namespace Ecomerciar\Moova\Settings\Sections;

use Ecomerciar\Moova\Settings\FieldFactory;

/**
 * Base Section class
 */
class Section
{
    private $data = [];

    /**
     * Default constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Adds the section itself into the settings page
     *
     * @return void
     */
    public function add()
    {
        add_settings_section(
            $this->data['slug'],
            $this->data['name'],
            '',
            'wc-moova-settings'
        );

        $settings_fields = $this->get_fields();
        foreach ($settings_fields as $setting) {
            add_settings_field(
                'wc-moova-' . $setting['slug'],
                $setting['name'],
                function () use ($setting) {
                    $fFactory = new FieldFactory();
                    $field = $fFactory->create($setting['slug']);
                    if ($field !== false) {
                        $field->render();
                    }
                },
                'wc-moova-settings',
                $this->data['slug']
            );
        }
    }
}
