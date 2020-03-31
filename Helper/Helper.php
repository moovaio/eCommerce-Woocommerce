<?php

namespace Ecomerciar\Moova\Helper;

class Helper
{
    use NoticesTrait;
    use LoggerTrait;
    use SettingsTrait;
    use WooCommerceTrait;
    use DatabaseTrait;
    use PageTrait;
    use StatusTrait;
    /**
     * Returns an url pointing to the main filder of the plugin assets
     *
     * @return string
     */
    public static function get_assets_folder_url()
    {
        return plugin_dir_url(\WCMoova::MAIN_FILE) . 'assets';
    }
}
