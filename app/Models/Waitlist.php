<?php

namespace App\Models;

use App\Enums\WaitlistStatus;
use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    use BelongsToBranch, HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'status' => WaitlistStatus::class,
    ];

    public static function statuses(): array
    {
        return WaitlistStatus::values();
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class, 'table_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }
}