<?php

namespace App\Enums;

enum NotificationType: string
{
    case RESERVATION = 'reservation';
    case ORDER = 'order';
    case SYSTEM = 'system';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
