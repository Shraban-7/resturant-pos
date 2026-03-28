<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierSaleItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public function item() : BelongsTo
    {
        return $this->belongsTo(SupplierProduct::class);
    }
}
