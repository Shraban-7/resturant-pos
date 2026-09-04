<?php

namespace App\Models;

use App\Enums\KitchenStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KitchenTicket extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'fired_at' => 'datetime',
        'prepared_at' => 'datetime',
        'served_at' => 'datetime',
        'status' => KitchenStatus::class,
    ];

    public static function statuses(): array
    {
        return KitchenStatus::values();
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [KitchenStatus::PENDING, KitchenStatus::PREPARING, KitchenStatus::READY], true);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
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
        return $query->where('admin_id', panel_owner_id());
    }

    public function scopeActiveQueue($query)
    {
        return $query->whereIn('status', [KitchenStatus::PENDING, KitchenStatus::PREPARING, KitchenStatus::READY]);
    }
}



