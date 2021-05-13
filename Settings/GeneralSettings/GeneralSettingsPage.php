<?php

namespace Ecomerciar\Moova\Settings\GeneralSettings;

use Ecomerciar\Moova\Helper\Helper;
use Ecomerciar\Moova\Settings\GeneralSettings\MoovaSection;
use Ecomerciar\Moova\Settings\GeneralSettings\CheckoutSection;
use Ecomerciar\Moova\Settings\GeneralSettings\SellerSection;

defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class GeneralSettingsPage
{
    /**
     * Gets all settings fields from all the settings sections
     *
     * @return array
     */
    public static function get_settings_fields()
    {
        return array_merge(
            MoovaSection::get_fields(),
            SellerSection::get_fields(),
            CheckoutSection::get_fields()
        );
    }

    /**
     * Registers the sections and render them
     *
     * @return void
     */
    public static function init_settings()
    {
        register_setting('wc-moova', 'wc-moova_options');

        $section = new MoovaSection();
        $section->add('wc-moova-settings');
        $section = new SellerSection();
        $section->add('wc-moova-settings');
        $section = new CheckoutSection();
        $section->add('wc-moova-settings');
    }

    public static function initPage()
    {
        $fields =  self::get_settings_fields();
        Helper::renderPage('wc-moova-settings', $fields);
    }
}
