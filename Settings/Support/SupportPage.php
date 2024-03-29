<?php

namespace Moova\Settings\Support;

use Moova\Helper\Helper;


defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class SupportPage
{

    public static function initPage()
    {
        $logo_url = Helper::get_assets_folder_url() . '/img/logo.png';
?>
        <div class="moova-form-wrapper wrap">
            <div class="settings-header">
                <img src="<?php echo esc_url($logo_url); ?>" class="logo">
            </div>

            <div class="form-wrapper">
                <h1> <?php echo esc_textarea(__('Help', 'moova-for-woocommerce')) ?></h1>
                <p> <?php echo esc_textarea(__('If you are not sure of how to get the required fields for this integration, what some fields means please check our awesome documentation
                    click', 'moova-for-woocommerce')) ?>
                    <a target="_blank" href="https://moova1.atlassian.net/servicedesk/customer/portal/3/topic/5c404312-979b-47ce-8152-5978b023f4aa/article/459767812">
                        <?php echo esc_textarea(__('here to check documentation', 'moova-for-woocommerce')) ?></a>
                </p>

                <p><?php echo esc_textarea(__('Also you can send us there any question you might have!', 'moova-for-woocommerce')) ?></p>
            </div>

            <div class="form-wrapper">
                <h1> <?php echo esc_textarea(__(' News', 'moova-for-woocommerce')) ?></h1>
                <p>Ahora puedes informarle a tus clientes cuando le van a llegar sus envios! Con el texto dinamico si tu cliente hace la cotizacion antes de la hora corte, vera que el pedido le llega hoy, si no vera que le llega mañana</p>
                <p>Mira el video para ver como configurar estos nuevos ajustes!</p>
                <img></img>
            </div>
    <?php
    }
}
