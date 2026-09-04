<?php

namespace App\Events;

use App\Models\Reservation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReservationPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Reservation $reservation)
    {
        $this->reservation->loadMissing(['table', 'branch']);
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel("admin.{$this->reservation->seller_id}.reservations")];
    }

    public function broadcastAs(): string
    {
        return 'ReservationPlaced';
    }

    public function broadcastWith(): array
    {
        $r = $this->reservation;

        return [
            'reservation_id' => $r->id,
            'customer_name' => $r->customer_name,
            'customer_phone' => $r->customer_phone,
            'guest_count' => $r->guest_count,
            'reservation_time' => optional($r->reservation_time)?->toIso8601String(),
            'table_name' => $r->table?->name,
            'branch_name' => $r->branch?->name,
            'status' => $r->status,
            'created_at' => optional($r->created_at)?->toIso8601String(),
        ];
    }
}

