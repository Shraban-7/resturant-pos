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

    /** Minimum gap between two bookings on the same table (minutes). */
    public const SLOT_MINUTES = 120;

    /**
     * Find an active booking on the same table overlapping the requested time.
     * Cancelled reservations never block. Returns null when the slot is free.
     */
    public static function conflictingBooking(int $tableId, $time, ?int $ignoreId = null): ?self
    {
        $time = \Carbon\Carbon::parse($time);
        $window = self::SLOT_MINUTES;

        return static::query()
            ->where('table_id', $tableId)
            ->where('status', '!=', self::CANCELLED)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->whereBetween('reservation_time', [
                $time->copy()->subMinutes($window)->toDateTimeString(),
                $time->copy()->addMinutes($window)->toDateTimeString(),
            ])
            ->orderBy('reservation_time')
            ->first();
    }

    public static function conflictMessage(self $conflict): string
    {
        $when = $conflict->reservation_time
            ? \Carbon\Carbon::parse($conflict->reservation_time)->format('d M Y, h:i A')
            : 'that time';

        return "Table {$conflict->table?->name} is already booked around {$when} ({$conflict->customer_name}). Please pick another table or time.";
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
        return $query->where('seller_id', panel_owner_id());
    }
}

