<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenTicket extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const PENDING = 'pending';
    public const PREPARING = 'preparing';
    public const READY = 'ready';
    public const SERVED = 'served';
    public const CANCELLED = 'cancelled';

    protected $casts = [
        'fired_at' => 'datetime',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::PENDING,
            self::PREPARING,
            self::READY,
            self::SERVED,
            self::CANCELLED,
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }

    /** Alias for Blade/KDS */
    public function table(): BelongsTo
    {
        return $this->diningTable();
    }

    public function items(): HasMany
    {
        return $this->hasMany(KitchenTicketItem::class, 'kitchen_ticket_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }

    public function scopeActiveQueue($query)
    {
        return $query->whereIn('status', [self::PENDING, self::PREPARING, self::READY]);
    }
}
