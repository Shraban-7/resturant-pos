<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DiningTable extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const FREE = 'free';
    const OCCUPIED = 'occupied';
    const RESERVED = 'reserved';
    const CLEANING = 'cleaning';

    public static function statuses(): array
    {
        return [
            self::FREE,
            self::OCCUPIED,
            self::RESERVED,
            self::CLEANING,
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class, 'floor_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'dining_table_id');
    }

    public function kitchenTickets(): HasMany
    {
        return $this->hasMany(KitchenTicket::class, 'dining_table_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id');
    }

    public function ensureQrToken(): string
    {
        if (! $this->qr_code_token) {
            $this->forceFill([
                'qr_code_token' => Str::random(48),
            ])->save();
        }

        return $this->qr_code_token;
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }
}
