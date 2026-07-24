<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoyaltyPoint;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    public function index()
    {
        $customers = Customer::self()->latest('id')->paginate(15);
        $logs = LoyaltyPoint::where('seller_id', auth()->id())->with('customer', 'sale')->latest('id')->limit(20)->get();

        return view('seller.loyalty.index', compact('customers', 'logs'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|numeric',
            'description' => 'nullable|string',
        ]);

        $customer = Customer::self()->findOrFail($request->customer_id);

        LoyaltyPoint::create([
            'seller_id' => auth()->id(),
            'customer_id' => $customer->id,
            'type' => 'adjusted',
            'points' => $request->points,
            'description' => $request->description ?: 'Manual Adjustment',
        ]);

        $customer->increment('loyalty_points_balance', $request->points);

        return back()->with('success', 'Loyalty points adjusted successfully');
    }
}
