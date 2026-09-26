<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addition;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductExtraController extends Controller
{
    /**
     * Priced extras selectable for a dish in the POS modal
     * (e.g. Cheese, Extra Tomato on Burger).
     */
    public function index(Product $product)
    {
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);
        abort_if($product->isIngredient(), 404);

        $product->load(['extras']);

        $attachedIds = $product->extras->pluck('id')->all();

        $availableAdditions = Addition::query()
            ->where('admin_id', panel_owner_id())
            ->where('is_active', true)
            ->when(! empty($attachedIds), fn ($q) => $q->whereNotIn('id', $attachedIds))
            ->orderBy('name')
            ->get(['id', 'name', 'price']);

        return view('admin.products.extras', compact('product', 'availableAdditions'));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);
        abort_if($product->isIngredient(), 404);

        $data = $request->validate([
            'addition_id' => [
                'required',
                'integer',
                Rule::exists('additions', 'id')->where(fn ($q) => $q
                    ->where('admin_id', panel_owner_id())
                    ->where('is_active', true)),
            ],
        ]);

        $product->extras()->syncWithoutDetaching([$data['addition_id']]);

        return redirect()
            ->route('admin.products.extras.index', $product)
            ->with('success', 'Extra attached.');
    }

    public function destroy(Product $product, Addition $extra)
    {
        abort_unless((int) $product->admin_id === (int) panel_owner_id(), 403);
        abort_if($product->isIngredient(), 404);

        $product->extras()->detach($extra->id);

        return redirect()
            ->route('admin.products.extras.index', $product)
            ->with('success', 'Extra removed.');
    }
}
