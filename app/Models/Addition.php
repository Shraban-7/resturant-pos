<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Addition extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'addition_product', 'addition_id', 'product_id')
            ->withTimestamps();
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
