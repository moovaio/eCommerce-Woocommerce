<?php

namespace Ecomerciar\Moova\Settings\Sections;

use Ecomerciar\Moova\Settings\FieldFactory;
use Ecomerciar\Moova\Settings\GeneralSettingsPage;
use Ecomerciar\Moova\Helper\Helper;

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
    public function add($pageName)
    {
        add_settings_section(
            $this->data['slug'],
            $this->data['name'],
            '',
            $pageName
        );

        $settings_fields = $this->get_fields();
        foreach ($settings_fields as $setting) {
            add_settings_field(
                'wc-moova-' . $setting['slug'],
                $setting['name'],
                function () use ($setting) {
                    $fFactory = new FieldFactory();
                    $field = $fFactory->create($setting);
                    if ($field !== false) {
                        $field->render();
                    }
                },
                $pageName,
                $this->data['slug']
            );
        }
    }
}
