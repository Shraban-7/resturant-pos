<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffNotification extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'type' => NotificationType::class,
    ];

    public function scopeForOwner($query, ?int $ownerId = null)
    {
        return $query->where('admin_id', $ownerId ?? panel_owner_id());
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public static function iconFor(NotificationType|string $type): string
    {
        $type = $type instanceof NotificationType ? $type : NotificationType::tryFrom($type);

        return match ($type) {
            NotificationType::RESERVATION => 'ri-calendar-check-line',
            NotificationType::ORDER => 'ri-receipt-2-line',
            default => 'ri-notification-3-line',
        };
    }

    public static function colorFor(NotificationType|string $type): string
    {
        $type = $type instanceof NotificationType ? $type : NotificationType::tryFrom($type);

        return match ($type) {
            NotificationType::RESERVATION => 'bg-amber-100 text-amber-600',
            NotificationType::ORDER => 'bg-sky-100 text-sky-600',
            default => 'bg-slate-100 text-slate-500',
        };
    }

    public static function notify(int $ownerId, NotificationType|string $type, string $title, ?string $body = null, ?array $data = null): self
    {
        return static::create([
            'admin_id' => $ownerId,
            'type' => $type instanceof NotificationType ? $type : NotificationType::from($type),
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}


