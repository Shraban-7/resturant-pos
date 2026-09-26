<?php

namespace App\Enums;

enum OrderType: string
{
    case RETAIL = 'retail';
    case DINE_IN = 'dine_in';
    case COUNTER = 'counter';
    case TAKEAWAY = 'takeaway';
    case DELIVERY = 'delivery';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::RETAIL => 'Retail',
            self::DINE_IN => 'Dine in',
            self::COUNTER => 'Counter',
            self::TAKEAWAY => 'Takeaway',
            self::DELIVERY => 'Delivery',
        };
    }

    public static function posModes(): array
    {
        return [self::DINE_IN->value, self::COUNTER->value];
    }
}