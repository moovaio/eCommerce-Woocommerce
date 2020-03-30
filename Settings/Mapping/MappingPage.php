<?php

namespace Ecomerciar\Moova\Settings\Mapping;

use Ecomerciar\Moova\Helper\Helper;
use Ecomerciar\Moova\Settings\Mapping\MappingStatusSection;

defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class MappingPage
{
    /**
     * Gets all settings fields from all the settings sections
     *
     * @return array
     */
    public static function get_settings_fields()
    {
        return array_merge(
            MappingStatusSection::get_fields()
        );
    } 

    /**
     * Registers the sections and render them
     *
     * @return void
     */
    public static function init_mapping()
    {
        register_setting('wc-moova', 'wc-moova_options');
        $section = new MappingStatusSection();
        $section->add('wc-moova-mapping');
    }

    public static function initPage()
    {
        $fields =  self::get_settings_fields();
        Helper::renderPage('wc-moova-mapping', $fields);
    }
}
