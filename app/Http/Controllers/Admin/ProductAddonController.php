<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductAddonController extends Controller
{
    /**
     * Suggested extra items for a menu item (e.g. Burger → Coke).
     * Both sides must be sellable menu items; raw materials excluded.
     */
    public function index(Product $product)
    {
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);
        abort_if($product->isIngredient(), 404);

        $product->load(['addons.unit']);

        $attachedIds = $product->addons->pluck('id')->all();

        $availableAddons = Product::self()
            ->sellable()
            ->active()
            ->whereKeyNot(array_merge([$product->id], $attachedIds))
            ->orderBy('name')
            ->get(['id', 'name', 'selling_price', 'type']);

        return view('admin.products.addons', compact('product', 'availableAddons'));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);
        abort_if($product->isIngredient(), 404);

        $data = $request->validate([
            'addon_product_id' => [
                'required',
                'integer',
                'different:product_id',
                Rule::exists('products', 'id')->where(fn ($q) => $q
                    ->where('admin_id', panel_owner_id())
                    ->where('is_active', 1)
                    ->whereIn('type', [ProductType::DISH, ProductType::BUFFET])),
            ],
        ]);

        abort_if((int) $data['addon_product_id'] === (int) $product->id, 422, 'An item cannot suggest itself.');

        $product->addons()->syncWithoutDetaching([$data['addon_product_id']]);

        return redirect()
            ->route('admin.products.addons.index', $product)
            ->with('success', 'Add-on attached.');
    }

    public function destroy(Product $product, Product $addon)
    {
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);
        abort_if($product->isIngredient(), 404);

        $product->addons()->detach($addon->id);

        return redirect()
            ->route('admin.products.addons.index', $product)
            ->with('success', 'Add-on removed.');
    }
}
