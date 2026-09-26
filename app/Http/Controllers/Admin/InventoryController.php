<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    /**
     * Raw materials only (ingredients). Menu dishes/buffets live
     * under Products (ProductController). All stock lives here.
     */
    public function index(Request $request)
    {
        $products = Product::self()
            ->rawIngredients()
            ->with(['category', 'unit', 'supplier'])
            ->active()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory.index', compact('products'));
    }

    public function create()
    {
        $units = ProductUnit::get();
        $suppliers = Supplier::self()->active()->orderBy('name')->get();

        return view('admin.inventory.create', compact('units', 'suppliers'));
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'name' => 'required|min:2',
            'unit_id' => 'required',
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'reorder_level' => 'nullable|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'stock_in' => 'required|numeric|min:0',
        ]);

        // Category is hidden from this form (DB still requires one).
        $category = ProductCategory::firstOrCreate(
            ['admin_id' => panel_owner_id(), 'name' => 'Raw Materials'],
            ['admin_id' => panel_owner_id(), 'name' => 'Raw Materials']
        );

        $product = Product::create([
            'admin_id' => panel_owner_id(),
            'category_id' => $category->id,
            'unit_id' => $input['unit_id'],
            'supplier_id' => $input['supplier_id'] ?? null,
            'reorder_level' => $input['reorder_level'] ?? 0,
            'name' => $input['name'],
            'type' => ProductType::INGREDIENT,
            'meal_times' => null,
            'buying_price' => $input['buying_price'],
            'selling_price' => 0,
            'stock_in' => $input['stock_in'],
            'stock_out' => 0,
            'image' => null,
            'is_active' => 1,
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'admin_id' => $product->admin_id,
            'type' => 'increment',
            'quantity' => $input['stock_in'],
            'old_stock' => 0,
            'new_stock' => $input['stock_in'],
            'buying_price' => $input['buying_price'],
            'selling_price' => 0,
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Product saved.');
    }

    public function edit(Product $product)
    {
        abort_if(! $product->isIngredient(), 404);
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);

        $units = ProductUnit::get();
        $suppliers = Supplier::self()->active()->orderBy('name')->get();

        return view('admin.inventory.edit', compact('product', 'units', 'suppliers'));
    }

    public function update(Product $product, Request $request)
    {
        abort_if(! $product->isIngredient(), 404);
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);

        $input = $request->validate([
            'name' => 'required|min:2',
            'unit_id' => 'required',
            'supplier_id' => [
                'nullable',
                Rule::exists('suppliers', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'reorder_level' => 'nullable|numeric|min:0',
            'buying_price' => 'required|numeric|min:0',
            'stock_in' => 'required|numeric|min:0',
        ]);

        $this->updateStock($product, $request);

        $product->update([
            'unit_id' => $input['unit_id'],
            'supplier_id' => $input['supplier_id'] ?? null,
            'reorder_level' => $input['reorder_level'] ?? 0,
            'name' => $input['name'],
            'buying_price' => $input['buying_price'],
            'stock_in' => $input['stock_in'],
        ]);

        return redirect()->route('admin.inventory.index')->with('success', 'Product saved.');
    }

    private function updateStock($product, $request)
    {
        $newStock = $request->stock_in;
        $oldStockQuantity = $product->stock_in;

        if ($newStock == $oldStockQuantity) {
            return;
        }

        $productStock = new ProductStock;
        $productStock->admin_id = $product->admin_id;
        $productStock->product_id = $product->id;
        $productStock->old_stock = $oldStockQuantity;
        $productStock->buying_price = $request->buying_price;
        $productStock->selling_price = $product->selling_price;
        $productStock->new_stock = $newStock;

        if ($newStock > $oldStockQuantity) {
            $productStock->type = 'increment';
            $productStock->quantity = $newStock - $oldStockQuantity;
        }

        if ($newStock < $oldStockQuantity) {
            $productStock->type = 'decrement';
            $productStock->quantity = $oldStockQuantity - $newStock;
        }

        $productStock->save();
    }

    public function delete(Product $product)
    {
        abort_if(! $product->isIngredient(), 404);
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);

        if ($product->recipe()->exists() || \App\Models\RecipeIngredient::where('ingredient_product_id', $product->id)->exists()) {
            return redirect()->back()->with('error', 'Cannot delete: used in a recipe.');
        }

        $product->is_active = false;
        $product->save();

        return redirect()->back()->with('success', 'Product deleted.');
    }
}
