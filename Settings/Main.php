<?php

namespace Ecomerciar\Moova\Settings;

use Ecomerciar\Moova\Helper\Helper;
use Ecomerciar\Moova\Settings\Sections\MoovaSection;
use Ecomerciar\Moova\Settings\Sections\IntegrationSection;
use Ecomerciar\Moova\Settings\Sections\SellerSection;

defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class Main
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
            IntegrationSection::get_fields()
        );
    }

    /**
     * Gets all settings (options registered with their values)
     *
     * @return array
     */
    public static function get_all_settings()
    {
        $settings = self::get_settings_fields();
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting['slug']] = Helper::get_option($setting['slug']);
        }
        return $data;
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
        $section->add();
        $section = new SellerSection();
        $section->add();
        $section = new IntegrationSection();
        $section->add();
    }

    /**
     * Adds our assets into our settings page
     *
     * @param string $hook
     * @return void
     */
    public static function add_assets_files(string $hook)
    {
        if ($hook === 'settings_page_wc-moova-settings') {
            wp_enqueue_style('wc-moova-settings-css');
        }
    }

    /**
     * Creates a setting option in the WordPress Sidebar
     *
     * @return void
     */
    public static function create_menu_option()
    {
        add_options_page(
            'Moova',
            'Moova',
            'manage_options',
            'wc-moova-settings',
            [__CLASS__, 'settings_page_content']
        );
    }

    /**
     * Displays the settings pages
     *
     * @return void
     */
    public static function settings_page_content()
    {

        if (!is_admin() || !current_user_can('manage_options')) {
            die('what are you doing here?');
        }

        $nonce = $_REQUEST['_wpnonce'] ?? null;
        if (!empty($_POST) && $nonce && !wp_verify_nonce($nonce, 'wc-moova-settings-options')) {
            die('what are you doing here?');
        }

        $settings_saved = FieldsVerifier::save_settings($_POST);
        if ($settings_saved) {
            Helper::add_success(__('Configuración guardada', 'wc-moova'), true);
        }

        $logo_url = Helper::get_assets_folder_url() . '/img/logo.png';
        ?>
        <div class="moova-form-wrapper wrap">
            <div class="settings-header">
                <img src="<?php echo $logo_url; ?>" class="logo">
            </div>
            <form action="options-general.php?page=wc-moova-settings" method="post" class="form-wrapper">
                <?php
                        settings_fields('wc-moova-settings');
                        do_settings_sections('wc-moova-settings');
                        submit_button(__('Guardar', 'wc-moova'));
                        ?>
            </form>
        </div>
<?php

    }
}
