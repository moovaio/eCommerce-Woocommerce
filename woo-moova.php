<?php

use Ecomerciar\Moova\Helper\Helper;

/**
 * Plugin Name: Moova for WooCommerce
 * Description: Integration between Moova and WooCommerce
 * Version: 1.0.6
 * Requires PHP: 7.0
 * Author: Moova.io
 * Author URI: https://moova.io/
 * Text Domain: wc-moova
 * WC requires at least: 3.3
 * WC tested up to: 4.0
 */

defined('ABSPATH') || exit;

/**
 * Plugin's base Class
 */
class WCMoova
{
    const PLUGIN_NAME = 'Moova';
    const MAIN_FILE = __FILE__;
    const MAIN_DIR = __DIR__;

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'setScripts']);
        add_action('admin_menu', [$this, 'setMenuPages'], 11);
        add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
    }
    /**
     * Checks system requirements
     *
     * @return bool
     */
    public static function check_system()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $system = self::check_components();

        if ($system['flag']) {
            deactivate_plugins(plugin_basename(__FILE__));
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . sprintf(__('<strong>%s/strong> Requires at least %s version %s or greater.', 'wc-moova'), self::PLUGIN_NAME, $system['flag'], $system['version']) . '</p>';
            echo '</div>';
            return false;
        }

        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . sprintf(__('WooCommerce must be active before using <strong>%s</strong>', 'wc-moova'), self::PLUGIN_NAME) . '</p>';
            echo '</div>';
            return false;
        }

        return true;
    }

    /**
     * Check the components required for the plugin to work (PHP, WordPress and WooCommerce)
     *
     * @return array
     */
    private static function check_components()
    {

        global $wp_version;
        $flag = $version = false;

        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $flag = 'PHP';
            $version = '7.0';
        } elseif (version_compare($wp_version, '4.9', '<')) {
            $flag = 'WordPress';
            $version = '4.9';
        } elseif (!defined('WC_VERSION') || version_compare(WC_VERSION, '3.3', '<')) {
            $flag = 'WooCommerce';
            $version = '3.3';
        }

        return [
            'flag' => $flag,
            'version' => $version
        ];
    }

    /**
     * Inits our plugin
     *
     * @return void
     */
    public function setScripts()
    {
        if (!self::check_system()) {
            return false;
        }

        spl_autoload_register(
            function ($class) {
                if (strpos($class, 'Moova') === false) {
                    return;
                }

                $name = str_replace('\\', '/', $class);
                $name = str_replace('Ecomerciar/Moova/', '', $name);
                require_once plugin_dir_path(__FILE__) . $name . '.php';
            }

        );
        include_once __DIR__ . '/Hooks.php';
        Helper::init();
        self::load_textdomain();
    }

    public function setMenuPages()
    {
        add_menu_page(
            'Configuracion general',
            'Moova',
            'manage_options',
            'wc-moova-settings',
            ['\Ecomerciar\Moova\Settings\GeneralSettings\GeneralSettingsPage', 'initPage'],
            plugin_dir_url( __FILE__ ) . 'assets/img/icon-menu.svg'
        );

        add_submenu_page(
            'wc-moova-settings',
            'Mapeo',
            'Mapeo',
            'manage_options',
            'wc-moova-mapping',
            ['\Ecomerciar\Moova\Settings\Mapping\MappingPage', 'initPage']
        );

        add_submenu_page(
            'wc-moova-settings',
            'Ayuda',
            'Ayuda',
            'manage_options',
            'wc-moova-help',
            ['\Ecomerciar\Moova\Settings\Support\SupportPage', 'initPage']
        );
    }

    /**
     * Registers all scripts to be loaded laters
     *
     * @return void
     */
    public static function register_scripts()
    {
        wp_enqueue_style('wc-moova-settings-css', Helper::get_assets_folder_url() . '/css/settings.css');
        wp_enqueue_script('wc-moova-orders-js', Helper::get_assets_folder_url() . '/js/orders.min.js');
    }

    /**
     * Create a link to the settings page, in the plugins page
     *
     * @param array $links
     * @return array
     */
    public static function create_settings_link(array $links)
    {
        $link = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wc-moova-settings')) . '">' . __('Settings', 'wc-moova') . '</a>';
        array_unshift($links, $link);
        return $links;
    }

    /**
     * Adds our shipping method to WooCommerce
     *
     * @param array $shipping_methods
     * @return array
     */
    public static function add_shipping_method($shipping_methods)
    {
        $shipping_methods['moova'] = '\Ecomerciar\Moova\ShippingMethod\WC_Moova';
        return $shipping_methods;
    }

    /**
     * Loads the plugin text domain
     *
     * @return void
     */
    public static function load_textdomain()
    {
        load_plugin_textdomain('wc-moova', false, basename(dirname(__FILE__)) . '/i18n/languages');
    }
}
$settings_page = new WCMoova();
