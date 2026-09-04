<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierProductStock extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function currentStock(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->old_stock + $this->new_stock
        );
    }

    public function product() : BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
    
    public function scopeSelf($query)
    {
        return $query->where('supplier_id', panel_owner_id());
    }
}

