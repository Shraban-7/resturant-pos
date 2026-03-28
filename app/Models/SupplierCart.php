<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierCart extends Model
{
    use HasFactory;
    protected $table = 'supplier_carts';
    
    protected $guarded = ['id'];

    public function items() : HasMany
    {
        return $this->hasMany(SupplierCartItem::class, 'cart_id');
    }


}
