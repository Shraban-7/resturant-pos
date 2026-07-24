<?php

namespace App\Actions;

use App\Events\OrderPlacedEvent;
use App\Events\TableStatusChangedEvent;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use App\Models\Sale;
use Illuminate\Support\Str;

class CreateKitchenTicketAction
{
    public function execute(Sale $sale): ?KitchenTicket
    {
        $sale->loadMissing(['items', 'table']);

        if ($sale->items->isEmpty()) {
            return null;
        }

        $ticket = KitchenTicket::create([
            'seller_id' => $sale->seller_id,
            'sale_id' => $sale->id,
            'dining_table_id' => $sale->dining_table_id,
            'ticket_number' => 'KOT-' . strtoupper(Str::random(6)),
            'status' => KitchenTicket::PENDING,
            'fired_at' => now(),
        ]);

        foreach ($sale->items as $saleItem) {
            KitchenTicketItem::create([
                'kitchen_ticket_id' => $ticket->id,
                'sale_item_id' => $saleItem->id,
                'product_id' => $saleItem->item_id,
                'product_name' => $saleItem->item_name,
                'quantity' => $saleItem->quantity,
                'modifiers_json' => $saleItem->modifiers_json,
                'special_instructions' => $saleItem->note,
                'status' => KitchenTicketItem::PENDING,
            ]);
        }

        $ticket->load(['items', 'diningTable', 'sale.waiter']);

        event(new OrderPlacedEvent($ticket));

        if ($sale->table) {
            event(new TableStatusChangedEvent($sale->table->fresh(), $sale->id));
        }

        return $ticket;
    }
}
