<?php

namespace Moova\Helper;

trait StatusTrait
{
    public static function moova_status()
    {
        return [
            'CONFIRMED', 'PICKEDUP', 'INTRANSIT', 'DELIVERED', 'INCIDENCE',
            'CANCELED', 'RETURNED', 'TOBERETURNED'
        ];
    }
}
