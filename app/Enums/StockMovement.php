<?php

namespace App\Enums;

enum StockMovement: string
{
    case INCREMENT = 'increment';
    case DECREMENT = 'decrement';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
