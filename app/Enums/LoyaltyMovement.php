<?php

namespace App\Enums;

enum LoyaltyMovement: string
{
    case EARNED = 'earned';
    case REDEEMED = 'redeemed';
    case ADJUSTED = 'adjusted';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
