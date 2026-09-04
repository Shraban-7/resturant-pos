<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffNotification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public const TYPE_RESERVATION = 'reservation';
    public const TYPE_ORDER = 'order';
    public const TYPE_SYSTEM = 'system';

    public function scopeForOwner($query, ?int $ownerId = null)
    {
        return $query->where('seller_id', $ownerId ?? panel_owner_id());
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public static function iconFor(string $type): string
    {
        return match ($type) {
            self::TYPE_RESERVATION => 'ri-calendar-check-line',
            self::TYPE_ORDER => 'ri-receipt-2-line',
            default => 'ri-notification-3-line',
        };
    }

    public static function colorFor(string $type): string
    {
        return match ($type) {
            self::TYPE_RESERVATION => 'bg-amber-100 text-amber-600',
            self::TYPE_ORDER => 'bg-sky-100 text-sky-600',
            default => 'bg-slate-100 text-slate-500',
        };
    }

    public static function notify(int $sellerId, string $type, string $title, ?string $body = null, ?array $data = null): self
    {
        return static::create([
            'seller_id' => $sellerId,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
