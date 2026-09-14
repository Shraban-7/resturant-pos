<?php

namespace App\Enums;

enum WaitlistStatus: string
{
    case WAITING = 'waiting';
    case SEATED = 'seated';
    case CANCELLED = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}