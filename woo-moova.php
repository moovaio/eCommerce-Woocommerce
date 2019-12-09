<?php

use Ecomerciar\Moova\Helper\Helper;

/**
 * Plugin Name: Moova for WooCommerce
 * Description: Integration between Moova and WooCommerce
 * Version: 1.0.0
 * Requires PHP: 7.0
 * Author: Ecomerciar
 * Author URI: https://ecomerciar.com
 * Text Domain: wc-moova
 * WC requires at least: 3.3
 * WC tested up to: 3.8
 */

defined('ABSPATH') || exit;

add_action('plugins_loaded', ['WCMoova', 'init']);
add_action('admin_enqueue_scripts', ['WCMoova', 'register_scripts']);

/**
 * Plugin's base Class
 */
class WCMoova
{
    const PLUGIN_NAME = 'Moova';
    const MAIN_FILE = __FILE__;
    const MAIN_DIR = __DIR__;

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
    public static function init()
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
    }

    /**
     * Registers all scripts to be loaded laters
     *
     * @return void
     */
    public static function register_scripts()
    {
        wp_register_style('wc-moova-settings-css', Helper::get_assets_folder_url() . '/css/settings.css');
        wp_register_script('wc-moova-orders-js', Helper::get_assets_folder_url() . '/js/orders.min.js');
    }

    /**
     * Create a link to the settings page, in the plugins page
     *
     * @param array $links
     * @return array
     */
    public static function create_settings_link(array $links)
    {
        $link = '<a href="' . esc_url(get_admin_url(null, 'options-general.php?page=wc-moova-settings')) . '">' . __('Ajustes', 'wc-moova') . '</a>';
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
}
