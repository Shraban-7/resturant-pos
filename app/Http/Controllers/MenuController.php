<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use Illuminate\Http\Request;

class MenuController extends Controller
{
        public function index(DiningTable $table)
    {
        $categories = ProductCategory::with(['products' => function ($q) {
            $q->where('is_active', 1);
        }])->get();

        return view('digital-menu', compact('table', 'categories'));
    }

    public function placeOrder(Request $request, DiningTable $table)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        $subtotal = 0;
        $saleItems = [];

        foreach ($request->items as $item) {
            $product = Product::find($item['id']);

            $total = $product->selling_price * $item['quantity'];
            $subtotal += $total;

            $saleItems[] = [
                'seller_id' => $table->seller_id,
                'item_id' => $product->id,
                'item_name' => $product->name,
                'buying_price' => $product->buying_price,
                'unit_price' => $product->selling_price,
                'unit' => $product->unit->short_name,
                'quantity' => $item['quantity'],
                'total_price' => $total,
            ];

            $product->increment('stock_out', $item['quantity']);
        }

        $sale = Sale::create([
            'seller_id' => $table->seller_id,
            'order_id' => generateOrderId(),
            'sale_date' => now(),
            'subtotal' => $subtotal,
            'payable' => $subtotal,
            'paid' => 0,
            'due' => $subtotal,
            'dining_table_id' => $table->id,
            'status' => 'pending',
        ]);

        foreach ($saleItems as $saleItem) {
            $sale->items()->create($saleItem);
        }

        $table->update(['status' => DiningTable::OCCUPIED]);

        return response()->json([
            'order_id' => $sale->order_id
        ]);
    }
}
