<?php

namespace App\Events;

use App\Models\KitchenTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlacedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public KitchenTicket $ticket)
    {
        $this->ticket->loadMissing(['items', 'diningTable', 'sale.waiter']);
    }

    public function broadcastOn(): array
    {
        $sellerId = $this->ticket->seller_id;
        $channels = [
            new PrivateChannel("seller.{$sellerId}.kds"),
            new PrivateChannel("seller.{$sellerId}.pos"),
        ];

        $token = $this->ticket->diningTable?->qr_code_token;
        if ($token) {
            // Public channel: knowing the table token authorizes guest trackers.
            $channels[] = new Channel("table.{$token}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'OrderPlaced';
    }

    public function broadcastWith(): array
    {
        $ticket = $this->ticket;
        $sale = $ticket->sale;
        $table = $ticket->diningTable;

        return [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status,
            'sale_id' => $ticket->sale_id,
            'order_id' => $sale?->order_id,
            'table_id' => $ticket->dining_table_id,
            'table_name' => $table?->name,
            'waiter_name' => $sale?->waiter?->name,
            'order_type' => $sale?->order_type ?? 'dine_in',
            'items' => $ticket->items->map(fn ($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product_name,
                'quantity' => (float) $item->quantity,
                'modifiers' => collect($item->modifiers_json ?? [])->pluck('name')->filter()->values()->all(),
                'special_instructions' => $item->special_instructions,
                'status' => $item->status,
            ])->values()->all(),
            'created_at' => optional($ticket->created_at)?->toIso8601String(),
            'fired_at' => optional($ticket->fired_at ?? $ticket->created_at)?->toIso8601String(),
        ];
    }
}
