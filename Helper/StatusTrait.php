<?php

namespace Ecomerciar\Moova\Helper;

trait StatusTrait
{
    public static function moova_status()
    {
        return [
            'READY', 'BLOCKED', 'WAITING',
            'CONFIRMED', 'PICKEDUP', 'INTRANSIT', 'DELIVERED', 'INCIDENCE',
            'CANCELED', 'RETURNED', 'TOBERETURNED', 'WAITINGCLIENT'
        ];
    }
}
