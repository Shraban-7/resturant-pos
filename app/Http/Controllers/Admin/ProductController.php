<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MealSlot;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Menu products only (dishes + buffets). Raw ingredients live
     * under Inventory (InventoryController). Stock is tracked on
     * raw materials via recipes — menu rows carry no inventory.
     */
    public function index(Request $request)
    {
        $products = Product::self()
            ->sellable()
            ->with(['category', 'recipe.ingredients'])
            ->withCount('addons')
            ->active()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

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
            'name_bn' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'meal_times' => 'nullable|array',
            'meal_times.*' => 'in:breakfast,lunch,dinner',
            'type' => 'nullable|in:dish,buffet',
        ]);

        $input['admin_id'] = panel_owner_id();
        $input['meal_times'] = $this->normalizeMealTimes($request->input('meal_times'));

        if (empty($input['type'])) {
            $input['type'] = ProductType::DISH;
        }

        // Pure menu row: no inventory of its own.
        $input['buying_price'] = 0;
        $input['stock_in'] = 0;
        $input['stock_out'] = 0;

        if ($request->hasFile('image')) {
            $input['image'] = upload_file($request->file('image'), 'images/products');
        }

        Product::create($input);

        return redirect()->back()->with('success', 'Menu item saved.');
    }

    public function edit(Product $product)
    {
        abort_if($product->isIngredient(), 404);
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);

        $units = ProductUnit::get();
        $categories = ProductCategory::get();

        return view('admin.products.edit', compact('product', 'units', 'categories'));
    }

    public function update(Product $product, Request $request)
    {
        abort_if($product->isIngredient(), 404);
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);

        $input = $request->validate([
            'category_id' => 'required',
            'unit_id' => 'required',
            'name' => 'required|min:2',
            'name_bn' => 'nullable|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3048',
            'meal_times' => 'nullable|array',
            'meal_times.*' => 'in:breakfast,lunch,dinner',
            'type' => 'nullable|in:dish,buffet',
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

        $product->update($input);

        return redirect()->back()->with('success', 'Menu item saved.');
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

    public function delete(Product $product)
    {
        abort_if($product->isIngredient(), 404);
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);

        $product->is_active = false;
        $product->save();

        return redirect()->back()->with('success', 'Menu item deleted.');
    }
}
