<?php

namespace App\Enums;

enum EmployeeRole: string
{
    case CHEF = 'chef';
    case WAITER = 'waiter';
    case MANAGER = 'manager';
    case CLEANER = 'cleaner';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
