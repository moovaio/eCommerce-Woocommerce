<?php

use Moova\Helper\Helper;

/**
 * Plugin Name: Moova for WooCommerce
 * Description: Integration between Moova and WooCommerce
 * Version: 6.3
 * Requires PHP: 7.0
 * Author: Moova.io
 * Author URI: https://moova.io/
 * Text Domain: moova-for-woocommerce
 * WC requires at least: 3.3
 * WC tested up to: 6.5
 */

defined('ABSPATH') || exit;

/**
 * Plugin's base Class
 */
class WCMoova
{
    public const PLUGIN_NAME = 'Moova';
    public const MAIN_FILE = __FILE__;
    public const MAIN_DIR = __DIR__;
    public const WITHOUT_NEXT_DAY_DELIVERY = [
        'Fri',
        'Sat',
    ];
    public const NOT_WORKING_DAYS = [
        'Sat',
        'Sun',
    ];

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'setScripts']);
        add_action('admin_menu', [$this, 'set_menu_pages'], 11);
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
            echo esc_html('<div class="notice notice-error is-dismissible">'
                . '<p>' . sprintf(__('<strong>%s/strong> Requires at least %s version %s or greater.', 'moova-for-woocommerce'), self::PLUGIN_NAME, $system['flag'], $system['version']) . '</p>'
                . '</div>');
            return false;
        }

        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            echo esc_html('<div class="notice notice-error is-dismissible">'
                . '<p>' . sprintf(__('WooCommerce must be active before using <strong>%s</strong>', 'moova-for-woocommerce'), self::PLUGIN_NAME) . '</p>'
                . '</div>');
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
                $name = str_replace('Moova/', '', $name);
                require_once plugin_dir_path(__FILE__) . $name . '.php';
            }
        );
        include_once __DIR__ . '/Hooks.php';
        Helper::init();
        self::load_textdomain();
    }

    public function set_menu_pages()
    {
        add_menu_page(
            'Ajustes',
            'Moova',
            'manage_options',
            'wc-moova-settings',
            ['\Moova\Settings\GeneralSettings\GeneralSettingsPage', 'initPage'],
            plugin_dir_url(__FILE__) . 'assets/img/icon-menu.svg'
        );

        add_submenu_page(
            'wc-moova-settings',
            'Ajustes',
            'Ajustes',
            'manage_options',
            'wc-moova-settings'
        );

        add_submenu_page(
            'wc-moova-settings',
            'Mapeo',
            'Mapeo',
            'manage_options',
            'wc-moova-mapping',
            ['\Moova\Settings\Mapping\MappingPage', 'initPage']
        );
        add_submenu_page(
            'wc-moova-settings',
            'Logs',
            'Logs',
            'manage_options',
            'wc-moova-logs',
            ['\Moova\Settings\Logs\LogsPage', 'initPage']
        );

        add_submenu_page(
            'wc-moova-settings',
            'Help & News',
            'Help & News',
            'manage_options',
            'wc-moova-help',
            ['\Moova\Settings\Support\SupportPage', 'initPage']
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
        wp_enqueue_style('wc-moova-rate-css', Helper::get_assets_folder_url() . '/css/rate.css');
        wp_register_script('wc-moova-orders-js', Helper::get_assets_folder_url() . '/js/orders.min.js');
        wp_register_script('wc-moova-settings-js', Helper::get_assets_folder_url() . '/js/settings.js');
        wp_enqueue_script('wc-moova-rating-js', Helper::get_assets_folder_url() . '/js/rate.js');
    }

    /**
     * Create a link to the settings page, in the plugins page
     *
     * @param array $links
     * @return array
     */
    public static function create_settings_link(array $links)
    {
        $link = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wc-moova-settings')) . '">' . __('Settings', 'moova-for-woocommerce') . '</a>';
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
        $shipping_methods['moova'] = '\Moova\ShippingMethod\WC_Moova';
        return $shipping_methods;
    }

    public static function customize_label_shipping_checkout($label, $method)
    {
        $imageUrl = Helper::get_assets_folder_url() . '/img/icon-shipping.png';
        if ($method->method_id === 'moova') {
            if (strpos($label, '${shippingDays}') !== false) {
                $delivery_day = self::get_delivery_day();
                $label = str_replace('${shippingDays}', $delivery_day, $label);
                $label = "<span style='vertical-align: top!important;'>$label</span>";
            }

            if (Helper::get_option('show_moova_image_checkout')) {
                $label = "<img width='60px' style='display: inline-block;margin-right: 6px;' src='$imageUrl'>" . $label;
            }

            if ($method->cost == 0) {
                $label .= '<span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">Gratis</span>';
            }
        }
        return $label;
    }

    public static function get_delivery_day()
    {
        $today = current_time('D');
        $canDeliverTomorrow = !in_array($today, self::WITHOUT_NEXT_DAY_DELIVERY);
        $packagingDays = self::get_packaging_days($today);

        if ($packagingDays == 0) {
            return "hoy!";
        } elseif (($packagingDays == 1) && $canDeliverTomorrow) {
            return "mañana";
        }
        $suffix = ($packagingDays == 1) ? 'día hábil' : 'días hábiles';
        return "en {$packagingDays} $suffix!";
    }

    public static function get_packaging_days($today)
    {
        $timezone = new DateTimeZone(wp_timezone_string());
        $timeLimit = new \DateTime("today 15:00", $timezone);
        $currentDate = new \DateTime(current_time("mysql"), $timezone);
        $packagingDays = Helper::get_option('moova_shipping_days', 0);
        if ($packagingDays == 0) {
            $canDeliverToday = !in_array($today, self::NOT_WORKING_DAYS);
            return ($currentDate < $timeLimit) && $canDeliverToday ? 0 : 1;
        }

        return $packagingDays;
    }
    /**
     * Loads the plugin text domain
     *
     * @return void
     */
    public static function load_textdomain()
    {
        load_plugin_textdomain('wc-moova', false, basename(dirname(__FILE__)) . '/languages');
    }

    /**
     * Display a message after 10, 30 and 100 shippings.
     *
     * @author Axel candia
     */
    public static function qualify_application()
    {
        global $wpdb;
        $minShippings = get_option('wc-moova-min-shippings');
        if ($minShippings == null) {
            update_option('wc-moova-min-shippings', 10);
            $minShippings = 10;
        }

        if ($minShippings == -1) {
            return;
        }

        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $query = "SELECT count(*) FROM $order_items_table WHERE order_item_name = 'Moova'  ";
        $shippingsWithMoova = $wpdb->get_var($query);
        if ($shippingsWithMoova < $minShippings) {
            return;
        } ?>
        <div class="notice notice-success" id="moova-rate-app" data-moova-ajax-url=<?php echo(esc_url(admin_url('admin-ajax.php'))) ?> data-moova-ajax-nonce=<?php echo esc_textarea((wp_create_nonce('moova-for-woocommerce'))) ?>>
            <div>
                <p>
                    <?php esc_textarea((sprintf(
            __("Hey! Congratulations for your %d shipping with Moova!! We hope you are enjoying our plugin.
                            Could you please do me a BIG favor and give it a 5-star rating on WordPress?
                            Just to help us spread the word and boost our motivation.", 'moova-for-woocommerce'),
            $minShippings
        ))); ?>
                </p>
                <strong><em>~ Axel Candia</em></strong>
            </div>
            <ul>
                <li><a data-rate-action="rate" href="https://wordpress.org/support/plugin/moova-for-woocommerce/reviews/#postform" target="_blank"><?php echo esc_textarea((__("Yes sure!!", 'moova-for-woocommerce'))) ?></a> </li>
                <li><a data-rate-action="done-rating" href="#"><?php echo esc_textarea((__("I already did", 'moova-for-woocommerce'))) ?></a></li>
                <li><a data-rate-action="deny-rating" href="#"><?php echo esc_textarea((__("No thanks", 'moova-for-woocommerce'))) ?></a></li>
            </ul>
        </div>
<?php
    }
}
$settings_page = new WCMoova();
