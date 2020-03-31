<?php

namespace Ecomerciar\Moova\Helper;

trait StatusTrait
{
    public static function moova_status()
    {
        return [
            'DRAFT', 'READY', 'BLOCKED', 'WAITING',
            'CONFIRMED', 'PICKEDUP', 'INTRANSIT', 'DELIVERED', 'INCIDENCE',
            'CANCELED', 'RETURNED', 'TO,BERETURNED', 'WAITINGCLIENT'
        ];
    }
}
