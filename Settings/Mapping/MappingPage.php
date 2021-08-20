<?php

namespace Moova\Settings\Mapping;

use Moova\Helper\Helper;
use Moova\Settings\Mapping\ReceivingStatusSection;
use Moova\Settings\Mapping\SendingStatusSection;


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
            ReceivingStatusSection::get_fields(),
            SendingStatusSection::get_fields()
        );
    }

    /**
     * Registers the sections and render them
     *
     * @return void
     */
    public static function init_mapping()
    {
        $sectionName = 'wc-moova-mapping';
        register_setting('wc-moova', 'wc-moova_options');

        $section = new SendingStatusSection();
        $section->add($sectionName);

        $section = new ReceivingStatusSection();
        $section->add($sectionName);
    }

    public static function initPage()
    {
        $fields =  self::get_settings_fields();
        Helper::renderPage('wc-moova-mapping', $fields);
    }
}
