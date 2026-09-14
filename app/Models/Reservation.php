<?php

namespace App\Models;

use App\Enums\ReservationStatus;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use BelongsToBranch, HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'reservation_time' => 'datetime',
        'status' => ReservationStatus::class,
    ];

    public static function statuses(): array
    {
        return ReservationStatus::values();
    }

    public function isCancelled(): bool
    {
        return $this->status === ReservationStatus::CANCELLED;
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
            ->where('status', '!=', ReservationStatus::CANCELLED)
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
            ? \Carbon\Carbon::parse($conflict->reservation_time)->format('d M, h:i A')
            : 'that time';

        // IMPORTANT: never use `$conflict->table` in here. Inside this class
        // `table` resolves to Model::$table (string "reservations"), not the
        // relation, so `?->name` warns "Attempt to read property on string".
        // `diningTable` has no property collision and lazy-loads safely.
        $tableName = $conflict->diningTable?->name ?? 'that table';

        return 'Table ' . $tableName . ' booked at ' . $when . '.';
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function table(): BelongsTo
    {
        return $this->diningTable();
    }

    /**
     * Primary table relation. Named `diningTable` (not `table`) so that
     * `$model->diningTable` works even inside this class scope, where
     * `$model->table` would resolve to Model::$table (string) instead of
     * the BelongsTo relation. `table()` above is kept as an alias so
     * existing `with(['table'])` / Blade `$reservation->table` keep working.
     */
    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }
}



