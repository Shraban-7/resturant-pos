<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KitchenTicketItem extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const PENDING = 'pending';
    public const PREPARING = 'preparing';
    public const READY = 'ready';
    public const CANCELLED = 'cancelled';

    protected $casts = [
        'modifiers_json' => 'array',
        'quantity' => 'decimal:2',
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
