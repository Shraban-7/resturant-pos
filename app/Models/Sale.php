<?php

namespace App\Models;

use App\Enums\OrderType;
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
        'order_type' => OrderType::class,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function table()
    {
        return $this->diningTable();
    }

    /**
     * Primary table relation (see Reservation::diningTable for why the
     * `table` name collides with Model::$table inside model scope).
     */
    public function diningTable()
    {
        return $this->belongsTo(DiningTable::class, 'dining_table_id');
    }

    public function waiter()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function kitchenTickets()
    {
        return $this->hasMany(KitchenTicket::class, 'sale_id');
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class, 'gift_card_id');
    }

    public function scopeSelf($query)
    {
        return $query->where('admin_id', panel_owner_id());
    }
}


