<?php

namespace Moova\Settings\Fields;

interface FieldInterface
{
    public function get_name();
    public function get_slug();
    public function get_type();
    public function render();
}
