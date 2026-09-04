<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::self()
            ->with(['supplier', 'product.unit'])
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalSpent = Purchase::self()->sum('total_price');

        $suppliers = Supplier::self()->active()->orderBy('name')->get();
        $ingredients = Product::self()
            ->rawIngredients()
            ->orderBy('name')
            ->get(['id', 'name', 'buying_price', 'stock_in', 'stock_out']);

        return view('admin.purchases.index', compact('purchases', 'totalSpent', 'suppliers', 'ingredients'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'product_id' => [
                'required',
                Rule::exists('products', 'id')->where(fn ($q) => $q
                    ->where('admin_id', panel_owner_id())
                    ->where('type', ProductType::INGREDIENT)),
            ],
            'quantity' => 'required|numeric|min:0.001',
            'buying_price' => 'required|integer|min:0',
            'purchase_date' => 'required|date|before_or_equal:today',
            'note' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($data) {
            $product = Product::self()->whereKey($data['product_id'])->lockForUpdate()->firstOrFail();
            $oldStock = $product->stock_in - $product->stock_out;

            $purchase = Purchase::create([
                'admin_id' => panel_owner_id(),
                'supplier_id' => $data['supplier_id'],
                'product_id' => $product->id,
                'quantity' => $data['quantity'],
                'buying_price' => $data['buying_price'],
                'total_price' => (int) round($data['quantity'] * $data['buying_price']),
                'purchase_date' => $data['purchase_date'],
                'note' => $data['note'] ?? null,
            ]);

            $product->increment('stock_in', $data['quantity']);

            ProductStock::create([
                'product_id' => $product->id,
                'admin_id' => $product->admin_id,
                'type' => 'increment',
                'quantity' => $data['quantity'],
                'old_stock' => $oldStock,
                'new_stock' => $oldStock + $data['quantity'],
                'buying_price' => $data['buying_price'],
                'selling_price' => $product->selling_price,
            ]);
        });

        return redirect()->route('admin.purchases.index')->with('success', 'Purchase recorded, stock updated.');
    }
}


