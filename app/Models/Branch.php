<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(DiningTable::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(SellerEmployee::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', panel_owner_id());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

