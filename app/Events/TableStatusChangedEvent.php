<?php

namespace App\Events;

use App\Models\DiningTable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TableStatusChangedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public DiningTable $table,
        public ?int $currentSaleId = null,
        public ?int $elapsedSeconds = null
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("admin.{$this->table->admin_id}.tables"),
            new PrivateChannel("admin.{$this->table->admin_id}.pos"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TableStatusChanged';
    }

    public function broadcastWith(): array
    {
        return [
            'table_id' => $this->table->id,
            'name' => $this->table->name,
            'status' => $this->table->status,
            'floor_id' => $this->table->floor_id,
            'current_sale_id' => $this->currentSaleId,
            'elapsed_seconds' => $this->elapsedSeconds,
        ];
    }
}


