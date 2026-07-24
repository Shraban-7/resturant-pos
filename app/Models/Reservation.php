<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use BelongsToBranch, HasFactory;

    protected $guarded = ['id'];

    public const PENDING = 'pending';
    public const CONFIRMED = 'confirmed';
    public const SEATED = 'seated';
    public const CANCELLED = 'cancelled';

    protected $casts = [
        'reservation_time' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::PENDING,
            self::CONFIRMED,
            self::SEATED,
            self::CANCELLED,
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }
}
