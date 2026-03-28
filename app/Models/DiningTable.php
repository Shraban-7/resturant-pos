<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiningTable extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const FREE = 'free';
    const OCCUPIED = 'occupied';
    const RESERVED = 'reserved';

    public static function statuses(): array
    {
        return [
            self::FREE,
            self::OCCUPIED,
            self::RESERVED,
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }
}
