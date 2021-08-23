<?php

namespace Moova\Orders;

use Moova\Helper\Helper;
use Moova\Sdk\MoovaSdk;

defined('ABSPATH') || exit;

class TrackingShortcode
{
    /**
     * Handles tracking shortcode html
     *
     * @return string
     */
    public static function output()
    {
        $content = '<h2 class="moova-tracking-form-title">' . __('Numero de seguimiento', 'moova-for-woocommerce') . '</h2>
        <form method="get" class="moova-tracking-form">
        <input type="text" name="moova_tracking_id" style="width:40%" class="moova-tracking-form-field"><br>
        <br />
        <input name="submit_button" type="submit"  value="' . __('Rastree su envio', 'moova-for-woocommerce') . '"  id="update_button"  class="moova-tracking-form-submit update_button" style="cursor: pointer;background-color: #4fa0ff;border: 1px solid #4fa0ff;color: white;padding: 5px 10px;display: inline-block;border-radius: 4px;font-weight: 600;margin-bottom: 10px;text-align: center;"/>
        </form>';
        if (empty(sanitize_text_field($_GET['moova_tracking_id']))) {
            return $content;
        }
        $moova_id = sanitize_text_field($_GET['moova_tracking_id']);
        $moovaSdk = new MoovaSdk();
        $tracking_statuses = $moovaSdk->get_tracking($moova_id);
        if ($tracking_statuses === false) {
            $content .= '<h3  class="moova-tracking-error">' . __('Hubo un error, por favor intente nuevamente', 'moova-for-woocommerce') . '</h3>';
        } else {
            if (!empty($tracking_statuses)) {
                $content .= '<h3>' . __('Numero de seguimiento', 'moova-for-woocommerce') . ': ' . $moova_id . '</h3>';
                $content .= '<table>';
                $content .= '<tr>';
                $content .= '<th width="30%">' . __('Fecha', 'moova-for-woocommerce') . '</th>';
                $content .= '<th width="70%">' . __('Estado', 'moova-for-woocommerce') . '</th>';
                $content .= '</tr>';
                foreach ($tracking_statuses as $tracking_status) {
                    $content .= '<tr>';
                    $content .= '<td>' . $tracking_status['createdAt'] . '</td>';
                    $content .= '<td>' . $tracking_status['status'] . '</td>';
                    $content .= '</tr>';
                }
                $content .= '</table>';
            } else {
                $content .= '<h2>' . ('envio sin estado') . '</h2>';
            }
        }
        return $content;
    }
}
