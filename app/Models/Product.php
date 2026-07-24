<?php

namespace App\Models;

use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory, HasCommonScopes;

    protected $guarded = ['id'];

    public function scopeSeller($query)
    {
        return $query->where('seller_id', auth()->id());
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }

    public function supplierStocks(): HasMany
    {
        return $this->hasMany(SupplierProductStock::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class, 'product_id');
    }

    public function productModifiers(): HasMany
    {
        return $this->hasMany(ProductModifier::class, 'product_id');
    }

    public function modifiers(): BelongsToMany
    {
        return $this->belongsToMany(Modifier::class, 'product_modifiers', 'product_id', 'modifier_id')
            ->withPivot(['is_required', 'max_select'])
            ->withTimestamps();
    }

    public function availableStock(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->stock_in - $this->stock_out
        );
    }

    public function lastStockEntry()
    {
        return ProductStock::where('product_id', $this->id)
            ->latest('id')
            ->first();
    }
}
