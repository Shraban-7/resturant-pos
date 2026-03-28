<?php

namespace App\Models;

use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProduct extends Model
{
    use HasFactory, HasCommonScopes;

    protected $guarded = ['id'];

    public function scopeSelf($query)
    {
        return $query->where('supplier_id', auth()->id());
    }

    public function availableStock(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->stock_in - $this->stock_out
        );
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupplierProductCategory::class);
    }
}
