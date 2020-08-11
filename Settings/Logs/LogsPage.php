<?php

namespace Ecomerciar\Moova\Settings\Logs;

use Ecomerciar\Moova\Helper\Helper;


defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class LogsPage
{

    public static function initPage()
    {
        $logo_url = Helper::get_assets_folder_url() . '/img/logo.png';
        $upload_dir   = wp_upload_dir();
        $logPath = $upload_dir["basedir"] . "/wc-logs/WooCommerce-Moova-*";
        $logs = array_reverse(glob($logPath));

        $fileContent = null;
        $fileLogName = null;
        if (sizeof($logs) > 0) {
            $fileLogName = $logs[0];
            $fileContent = nl2br(htmlentities(file_get_contents($fileLogName)));
        }
?>
        <div class="moova-form-wrapper wrap">
            <div class="settings-header">
                <img src="<?php echo $logo_url; ?>" class="logo">
            </div>

            <div class="form-wrapper">
                <h1> <?php echo __('Logs', 'wc-moova') ?></h1>
                <?php
                if ($fileLogName) {
                ?>
                    <div class="form-group">
                        <label for="exampleFormControlSelect1">Reading from <?php echo $fileLogName ?> </label>
                        </br></br>
                        <p id="log_code">
                            <?php echo $fileContent ?>
                        </p>
                    </div>
                <?php
                }
                ?>


            </div>
        </div>
<?php
    }
}
