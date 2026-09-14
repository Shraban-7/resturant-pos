<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case MOBILE_BANKING = 'mobile_banking';
    case GIFT_CARD = 'gift_card';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Cash',
            self::CARD => 'Card',
            self::MOBILE_BANKING => 'Mobile banking',
            self::GIFT_CARD => 'Gift card',
        };
    }
}