<?php

namespace App\Http\Controllers;

use App\Models\KitchenTicket;
use App\Models\Sale;
use Illuminate\Http\Request;

class OrderStatusController extends Controller
{
    public function show(string $order)
    {
        $sale = Sale::query()
            ->where('order_id', $order)
            ->with(['items', 'kitchenTickets.items', 'table'])
            ->firstOrFail();

        $ticket = $sale->kitchenTickets->sortByDesc('id')->first();
        $status = $ticket?->status ?? KitchenTicket::PENDING;
        $token = $sale->table?->qr_code_token;

        $steps = [
            ['key' => 'received', 'label' => 'Order Received', 'statuses' => [KitchenTicket::PENDING]],
            ['key' => 'preparing', 'label' => 'In Kitchen', 'statuses' => [KitchenTicket::PREPARING]],
            ['key' => 'ready', 'label' => 'Food Ready', 'statuses' => [KitchenTicket::READY]],
            ['key' => 'served', 'label' => 'Served', 'statuses' => [KitchenTicket::SERVED]],
        ];

        return view('order-status', [
            'table' => $sale->table,
            'sale' => $sale,
            'ticket' => $ticket,
            'status' => $status,
            'steps' => $steps,
            'token' => $token,
        ]);
    }
}
