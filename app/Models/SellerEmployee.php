<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerEmployee extends Model
{
    use BelongsToBranch, HasFactory;

    protected $guarded = ['id'];

    const CHEF = 'chef';
    const WAITER = 'waiter';
    const MANAGER = 'manager';
    const CLEANER = 'cleaner';

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function scopeWaiter($query)
    {
        return $query->where('role', $this::WAITER);
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }

    public static function roles(): array
    {
        return [
            static::CHEF,
            static::WAITER,
            static::MANAGER,
            static::CLEANER,
        ];
    }
}
