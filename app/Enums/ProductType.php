<?php

namespace App\Enums;

enum ProductType: string
{
    case DISH = 'dish';
    case BUFFET = 'buffet';
    case INGREDIENT = 'ingredient';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::DISH => 'Dish',
            self::BUFFET => 'Buffet',
            self::INGREDIENT => 'Raw Ingredient',
        };
    }
}
