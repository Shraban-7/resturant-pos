<?php

namespace App\Actions;

use App\Enums\KitchenStatus;

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
            'admin_id' => $sale->admin_id,
            'sale_id' => $sale->id,
            'dining_table_id' => $sale->dining_table_id,
            'ticket_number' => 'KOT-' . strtoupper(Str::random(6)),
            'status' => KitchenStatus::PENDING,
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
                'status' => KitchenStatus::PENDING,
            ]);
        }

        $ticket->load(['items', 'diningTable', 'sale.waiter']);

        if ($ticket->diningTable) {
            $ticket->diningTable->ensureQrToken();
            $ticket->setRelation('diningTable', $ticket->diningTable->fresh());
        }

        event(new OrderPlacedEvent($ticket));

        if ($sale->table) {
            $sale->table->ensureQrToken();
            event(new TableStatusChangedEvent($sale->table->fresh(), $sale->id));
        }

        return $ticket;
    }

    /**
     * Fire a follow-up KOT for newly added sale lines (running / held tab).
     *
     * @param  iterable<\App\Models\SaleItem>  $saleItems
     */
    public function fireAdditionalItems(Sale $sale, iterable $saleItems): ?KitchenTicket
    {
        $sale->loadMissing('table');
        $items = collect($saleItems)->filter();

        if ($items->isEmpty()) {
            return null;
        }

        $ticket = KitchenTicket::create([
            'admin_id' => $sale->admin_id,
            'sale_id' => $sale->id,
            'dining_table_id' => $sale->dining_table_id,
            'ticket_number' => 'KOT-' . strtoupper(Str::random(6)),
            'status' => KitchenStatus::PENDING,
            'fired_at' => now(),
        ]);

        foreach ($items as $saleItem) {
            KitchenTicketItem::create([
                'kitchen_ticket_id' => $ticket->id,
                'sale_item_id' => $saleItem->id,
                'product_id' => $saleItem->item_id,
                'product_name' => $saleItem->item_name,
                'quantity' => $saleItem->quantity,
                'modifiers_json' => $saleItem->modifiers_json,
                'special_instructions' => $saleItem->note,
                'status' => KitchenStatus::PENDING,
            ]);
        }

        $ticket->load(['items', 'diningTable', 'sale.waiter']);

        if ($ticket->diningTable) {
            $ticket->diningTable->ensureQrToken();
            $ticket->setRelation('diningTable', $ticket->diningTable->fresh());
        }

        event(new OrderPlacedEvent($ticket));

        return $ticket;
    }
}



