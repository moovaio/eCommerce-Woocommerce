<?php

namespace Ecomerciar\Moova\Settings\Support;

use Ecomerciar\Moova\Helper\Helper;


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
                <img src="<?php echo $logo_url; ?>" class="logo">
            </div>
            <div class="form-wrapper">
                <h1> <?php echo __('Help & Support', 'wc-moova') ?></h1>
                <p> <?php echo __('If you are not sure of how to get the required fields for this integration, what some fields means please check our awesome documentation
                    click', 'wc-moova') ?>
                    <a target="_blank" href="https://moova1.atlassian.net/servicedesk/customer/portal/3/topic/5c404312-979b-47ce-8152-5978b023f4aa/article/459767812">
                        <?php echo __('here to check documentation') ?></a>
                </p>

                <p><?php echo __('Also you can send us there any question you might have!') ?></p>
            </div>
    <?php
    }
}