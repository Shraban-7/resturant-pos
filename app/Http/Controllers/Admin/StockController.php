<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\ProductType;

class StockController extends Controller
{
    /**
     * Raw-material stock ledger only. Menu products carry no inventory.
     */
    public function index(Request $request)
    {
        $stocks = ProductStock::self()
            ->whereHas('product', fn ($q) => $q->where('type', ProductType::INGREDIENT))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stocks.index', compact('stocks'));
    }

    public function create()
    {
        $products = Product::self()->rawIngredients()->with('unit')->orderBy('name')->get();

        return view('admin.stocks.create', compact('products'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($q) => $q
                    ->where('admin_id', panel_owner_id())
                    ->where('type', ProductType::INGREDIENT)),
            ],
            'stock_in' => 'required|numeric|min:0.001',
            'buying_price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::self()->whereKey($request->product_id)->firstOrFail();

        $quantity = $request->stock_in;
        $oldStock = $product->stock_in;
        $newStock = $oldStock + $quantity;

        $buying_price = $request->filled('buying_price')
            ? $request->buying_price
            : $product->buying_price;

        ProductStock::create([
            'product_id' => $product->id,
            'admin_id' => $product->admin_id,
            'type' => 'increment',
            'quantity' => $quantity,
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'buying_price' => $buying_price,
            'selling_price' => $product->selling_price,
        ]);

        $product->update([
            'buying_price' => $buying_price,
            'stock_in' => $newStock,
        ]);

        return redirect()->back()->with('success', 'Stock updated.');
    }
}
