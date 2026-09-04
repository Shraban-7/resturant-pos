<?php

namespace App\Models;

use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierSale extends Model
{
    use HasFactory, HasCommonScopes;

    protected $guarded = ['id'];

    public function items(): HasMany
    {
        return $this->hasMany(SupplierSaleItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('supplier_id', panel_owner_id());
    }
}

