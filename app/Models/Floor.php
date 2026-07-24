<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Floor extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function tables(): HasMany
    {
        return $this->hasMany(DiningTable::class, 'floor_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }
}
