<?php

namespace App\Http\Controllers\Admin;

use App\Events\KitchenStatusUpdatedEvent;
use App\Http\Controllers\Controller;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    public function index()
    {
        $tickets = KitchenTicket::self()
            ->activeQueue()
            ->with(['items', 'diningTable', 'sale.waiter'])
            ->orderBy('fired_at')
            ->orderBy('created_at')
            ->get();

        return view('admin.kds.index', [
            'tickets' => $tickets,
            'sellerId' => panel_owner_id(),
        ]);
    }

    public function updateStatus(Request $request, KitchenTicket $ticket)
    {
        abort_unless((int) $ticket->seller_id === (int) panel_owner_id(), 403);

        $request->validate([
            'status' => 'required|in:' . implode(',', KitchenTicket::statuses()),
        ]);

        $status = $request->string('status')->toString();
        $data = ['status' => $status];

        if ($status === KitchenTicket::PREPARING) {
            $data['fired_at'] = $ticket->fired_at ?? now();
            $ticket->items()
                ->where('status', KitchenTicketItem::PENDING)
                ->update(['status' => KitchenTicketItem::PREPARING]);
        }

        if ($status === KitchenTicket::READY) {
            $data['prepared_at'] = now();
            $ticket->items()
                ->whereIn('status', [KitchenTicketItem::PENDING, KitchenTicketItem::PREPARING])
                ->update(['status' => KitchenTicketItem::READY]);
        }

        if ($status === KitchenTicket::SERVED) {
            $data['served_at'] = now();
        }

        $ticket->update($data);
        $ticket->load(['items', 'diningTable', 'sale.waiter']);

        event(new KitchenStatusUpdatedEvent($ticket));

        if ($request->expectsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'Ticket updated',
                'ticket' => $this->ticketPayload($ticket),
            ]);
        }

        return back()->with('success', 'Ticket status updated.');
    }

    protected function ticketPayload(KitchenTicket $ticket): array
    {
        return [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'status' => $ticket->status,
            'sale_id' => $ticket->sale_id,
            'order_id' => $ticket->sale?->order_id,
            'table_id' => $ticket->dining_table_id,
            'table_name' => $ticket->diningTable?->name,
            'waiter_name' => $ticket->sale?->waiter?->name,
            'order_type' => $ticket->sale?->order_type ?? 'dine_in',
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



