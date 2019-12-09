<?php

namespace Ecomerciar\Moova\Helper;

class Helper
{
    use NoticesTrait;
    use LoggerTrait;
    use SettingsTrait;
    use WooCommerceTrait;
    use DatabaseTrait;

    public static function get_assets_folder_url()
    {
        return plugin_dir_url(\WCMoova::MAIN_FILE) . 'assets';
    }
}
