<?php

namespace Ecomerciar\Moova\Settings;

defined('ABSPATH') || exit;

/**
 * Class which will handle our settings being saved
 */
class FieldsVerifier
{
    /**
     * Saves all our fields, and sanitizes them.
     *
     * @param array $post_data
     * @return bool
     */
    public static function save_settings(array $post_data)
    {
        $saved = false;
        $settings_fields = Main::get_settings_fields();
        foreach ($settings_fields as $setting) {
            if (!isset($post_data[$setting['slug']])) {
                continue;
            }
            $value = $post_data[$setting['slug']];
            $value = filter_var($value, FILTER_SANITIZE_STRING);
            $value = strip_tags($value);
            update_option('wc-moova-' . $setting['slug'], $value);
            $saved = true;
        }
        return $saved;
    }
}
