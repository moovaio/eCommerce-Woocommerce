<?php

namespace Ecomerciar\Moova\Settings\Sections;

interface SectionInterface
{
    public function add($field);
    public static function get_fields();
}
