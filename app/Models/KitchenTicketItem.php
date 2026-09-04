<?php

namespace App\Models;

use App\Enums\KitchenStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenTicketItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'modifiers_json' => 'array',
        'quantity' => 'decimal:2',
        'status' => KitchenStatus::class,
    ];

    public function kitchenTicket(): BelongsTo
    {
        return $this->belongsTo(KitchenTicket::class, 'kitchen_ticket_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->kitchenTicket();
    }

    public function saleItem(): BelongsTo
    {
        return $this->belongsTo(SaleItem::class, 'sale_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
