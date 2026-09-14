<?php

namespace App\Http\Controllers;

use App\Enums\KitchenStatus;

use App\Models\KitchenTicket;
use App\Models\Sale;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function show(string $order)
    {
        $sale = Sale::query()
            ->where('order_id', $order)
            ->with(['items', 'kitchenTickets.items', 'table', 'diningTable'])
            ->firstOrFail();

        $ticket = $sale->kitchenTickets->sortByDesc('id')->first();
        $status = $ticket?->status ?? KitchenStatus::PENDING;
        $tableForView = $sale->getRelationValue('diningTable') ?? $sale->getRelationValue('table') ?? $sale->diningTable ?? $sale->table;

        $steps = [
            ['key' => 'received', 'label' => 'Order Received', 'statuses' => [KitchenStatus::PENDING]],
            ['key' => 'preparing', 'label' => 'In Kitchen', 'statuses' => [KitchenStatus::PREPARING]],
            ['key' => 'ready', 'label' => 'Food Ready', 'statuses' => [KitchenStatus::READY]],
            ['key' => 'served', 'label' => 'Served', 'statuses' => [KitchenStatus::SERVED]],
        ];

        return view('order-status', [
            'table' => $tableForView,
            'sale' => $sale,
            'ticket' => $ticket,
            'status' => $status instanceof KitchenStatus ? $status->value : $status,
            'steps' => $steps,
        ]);
    }

    /**
     * Latest kitchen status, polled by the order-status tracker page.
     */
    public function status(Request $request, string $order)
    {
        $sale = Sale::query()
            ->where('order_id', $order)
            ->with('kitchenTickets')
            ->firstOrFail();

        $ticket = $sale->kitchenTickets->sortByDesc('id')->first();
        $status = $ticket?->status ?? KitchenStatus::PENDING;

        return response()->json([
            'status' => $status instanceof KitchenStatus ? $status->value : $status,
            'order_id' => $sale->order_id,
        ]);
    }
}


