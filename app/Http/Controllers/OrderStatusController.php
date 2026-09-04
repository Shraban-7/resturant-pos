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
            ->with(['items', 'kitchenTickets.items', 'table'])
            ->firstOrFail();

        $ticket = $sale->kitchenTickets->sortByDesc('id')->first();
        $status = $ticket?->status ?? KitchenStatus::PENDING;
        $token = $sale->table?->qr_code_token;

        $steps = [
            ['key' => 'received', 'label' => 'Order Received', 'statuses' => [KitchenStatus::PENDING]],
            ['key' => 'preparing', 'label' => 'In Kitchen', 'statuses' => [KitchenStatus::PREPARING]],
            ['key' => 'ready', 'label' => 'Food Ready', 'statuses' => [KitchenStatus::READY]],
            ['key' => 'served', 'label' => 'Served', 'statuses' => [KitchenStatus::SERVED]],
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


