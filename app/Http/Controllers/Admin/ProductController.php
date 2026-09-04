<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MealSlot;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::self()->with(['category', 'recipe.ingredients'])->active()->latest('id')->paginate(20)->withQueryString();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $units = ProductUnit::get();
        $categories = ProductCategory::get();

        return view('admin.products.create', compact('units', 'categories'));
    }

    public function store(Request $request)
    {
        $input = $request->validate([
            'category_id' => 'required',
            'unit_id' => 'required',
            'name' => 'required|min:2',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_in' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'meal_times' => 'nullable|array',
            'meal_times.*' => 'in:breakfast,lunch,dinner',
            'type' => 'nullable|in:dish,buffet,ingredient',
        ]);

        $input['admin_id'] = panel_owner_id();
        $input['meal_times'] = $this->normalizeMealTimes($request->input('meal_times'));

        if (empty($input['type'])) {
            $input['type'] = ProductType::DISH;
        }

        if ($request->hasFile('image')) {

            $input['image'] = upload_file($request->file('image'), 'images/products');
        }

        $product = Product::create($input);

        ProductStock::create([
            'product_id' => $product->id,
            'admin_id' => $product->admin_id,
            'type' => 'increment',
            'quantity' => $request->stock_in,
            'old_stock' => 0,
            'new_stock' => $request->stock_in,
            'buying_price' => $request->buying_price,
            'selling_price' => $request->selling_price,
        ]);

        return redirect()->back()->with('success', 'Product Created');
    }

    public function edit(Product $product)
    {
        $units = ProductUnit::get();
        $categories = ProductCategory::get();

        return view('admin.products.edit', compact('product', 'units', 'categories'));
    }

    public function update(Product $product, Request $request)
    {
        $input = $request->validate([
            'category_id' => 'required',
            'unit_id' => 'required',
            'name' => 'required|min:2',
            'buying_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock_in' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'meal_times' => 'nullable|array',
            'meal_times.*' => 'in:breakfast,lunch,dinner',
            'type' => 'nullable|in:dish,buffet,ingredient',
        ]);

        $input['meal_times'] = $this->normalizeMealTimes($request->input('meal_times'));

        if (empty($input['type'])) {
            unset($input['type']); // keep existing / DB default
        }

        if ($request->hasFile('image')) {
            if ($product->image != null) {
                delete_file($product->image);
            }

            $input['image'] = upload_file($request->file('image'), 'images/products');
        }

        $this->updateStock($product, $request);

        $product->update($input);

        return redirect()->back()->with('success', 'Product Updated');
    }

    /**
     * Empty or all-slots-selected both mean "served all day" (stored as NULL).
     */
    private function normalizeMealTimes(?array $slots): ?array
    {
        $slots = array_values(array_intersect($slots ?? [], MealSlot::values()));

        if (empty($slots) || count($slots) === count(MealSlot::values())) {
            return null;
        }

        return $slots;
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
        $productStock->selling_price = $request->selling_price;
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
        $product->is_active = false;
        $product->save();

        return redirect()->back()->with('success', 'Product Deleted');
    }
}







