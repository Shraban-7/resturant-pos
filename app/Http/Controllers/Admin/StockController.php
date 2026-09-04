<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $stocks = ProductStock::self()->latest('id')->paginate(20)->withQueryString();

        return view('admin.stocks.index', compact('stocks'));
    }

    public function create()
    {
        $products = Product::self()->get();

        return view('admin.stocks.create', compact('products'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => 'required',
            'stock_in' => 'required|numeric',
            'buying_price' => 'nullable|numeric',
            'selling_price' => 'nullable|numeric',
        ]);

        $product = Product::find($request->product_id);

        $quantity = $request->stock_in;
        $oldStock = $product->stock_in;
        $newStock = $oldStock + $quantity;

        if ($request->buying_price) {
            $buying_price = $request->buying_price;
        } else {
            $buying_price = $product->buying_price;
        }

        if ($request->selling_price) {
            $selling_price = $request->selling_price;
        } else {
            $selling_price = $product->selling_price;
        }

        ProductStock::create([
            'product_id' => $product->id,
            'admin_id' => $product->admin_id,
            'type' => 'increment',
            'quantity' => $quantity,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'buying_price' => $buying_price,
            'selling_price' => $selling_price,
        ]);

        $product->update([
            'buying_price' => $buying_price,
            'selling_price' => $selling_price,
            'stock_in' => $newStock,
        ]);

        return redirect()->back()->with('success', 'Stock Updated Sucessfully');
    }
}



