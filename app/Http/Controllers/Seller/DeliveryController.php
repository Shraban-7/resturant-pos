<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\DeliveryOrder;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index()
    {
        $deliveries = DeliveryOrder::self()
            ->with('sale.customer')
            ->latest('id')
            ->paginate(15);

        return view('seller.deliveries.index', compact('deliveries'));
    }

    public function updateStatus(Request $request, DeliveryOrder $delivery)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,out_for_delivery,delivered,cancelled',
            'driver_name' => 'nullable|string',
            'driver_phone' => 'nullable|string',
        ]);

        $data = [
            'status' => $request->status,
        ];

        if ($request->driver_name) $data['driver_name'] = $request->driver_name;
        if ($request->driver_phone) $data['driver_phone'] = $request->driver_phone;

        if ($request->status === 'out_for_delivery' && !$delivery->dispatched_at) {
            $data['dispatched_at'] = now();
        } elseif ($request->status === 'delivered' && !$delivery->delivered_at) {
            $data['delivered_at'] = now();
        }

        $delivery->update($data);

        return back()->with('success', 'Delivery status updated successfully');
    }
}
