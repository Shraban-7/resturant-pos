<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\HasCommonScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use BelongsToBranch, HasFactory, HasCommonScopes;

    protected $guarded = ['id'];

    protected $casts = [
        'created_at_client' => 'datetime',
        'synced_at' => 'datetime',
        'sale_date' => 'date',
        'is_hold' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function table()
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }

    public function waiter()
    {
        return $this->belongsTo(SellerEmployee::class, 'seller_employee_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function kitchenTickets()
    {
        return $this->hasMany(KitchenTicket::class, 'sale_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('seller_id', auth()->id());
    }
}
