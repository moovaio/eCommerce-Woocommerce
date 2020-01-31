<?php

namespace Ecomerciar\Moova\Settings\Fields;

final class DescriptionField extends Field
{
    protected $data = [
        'name' => '',
        'slug' => '',
        'type' => 'description',
        'description' => '',
    ];

    public function __construct(array $args)
    {
        $this->data = wp_parse_args($args, $this->data);
    }
}
