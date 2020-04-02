<?php

namespace Ecomerciar\Moova\Helper;

trait PageTrait
{
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

    public static function renderPage($pageName, $fields)
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            die('what are you doing here?');
        }

        $nonce = $_REQUEST['_wpmoovanonce'] ?? null;
        if (!empty($_POST) && $nonce && !wp_verify_nonce($nonce, 'wc-moova-save-preferences')) {
            die('what are you doing here?');
        }
        $settings_saved = self::save_settings($_POST, $fields);
        if ($settings_saved) {
            Helper::add_success(__('Settings saved', 'wc-moova'), true);
        }

        $logo_url = Helper::get_assets_folder_url() . '/img/logo.png';
?>
        <div class="moova-form-wrapper wrap">
            <div class="settings-header">
                <img src="<?php echo $logo_url; ?>" class="logo">
            </div>
            <form action="admin.php?page=<?php echo $pageName ?>" method="post" class="form-wrapper">
                <?php
                settings_fields($pageName);
                do_settings_sections($pageName);
                wp_nonce_field('_wpmoovanonce', 'wc-moova-save-preferences');
                submit_button(__('Save', 'wc-moova'));


                ?>
            </form>
        </div>
<?php
    }

    /**
     * Saves all our fields, and sanitizes them.
     *
     * @param array $post_data
     * @return bool
     */
    public static function save_settings(array $post_data, $settings_fields)
    {
        $saved = false;
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