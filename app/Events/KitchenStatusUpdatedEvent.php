<?php

namespace App\Events;

use App\Models\KitchenTicket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KitchenStatusUpdatedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public KitchenTicket $ticket)
    {
        $this->ticket->loadMissing(['diningTable', 'items', 'sale']);
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("seller.{$this->ticket->seller_id}.pos"),
            new PrivateChannel("seller.{$this->ticket->seller_id}.kds"),
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
        return 'KitchenStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'sale_id' => $this->ticket->sale_id,
            'order_id' => $this->ticket->sale?->order_id,
            'table_id' => $this->ticket->dining_table_id,
            'table_name' => $this->ticket->diningTable?->name,
            'status' => $this->ticket->status,
            'items' => $this->ticket->items->map(fn ($item) => [
                'id' => $item->id,
                'status' => $item->status,
            ])->values()->all(),
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
