<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modifier;
use App\Models\Product;
use App\Models\ProductModifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProductModifierController extends Controller
{
    public function index(Product $product)
    {
        abort_unless($product->admin_id === panel_owner_id(), 403);

        $product->load(['productModifiers.modifier']);

        $attachedIds = $product->productModifiers->pluck('modifier_id')->all();

        $availableModifiers = Modifier::self()
            ->where('is_active', true)
            ->whereNotIn('id', $attachedIds)
            ->orderBy('group_name')
            ->orderBy('name')
            ->get();

        $allModifiers = Modifier::self()
            ->orderBy('group_name')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('group_name');

        return view('admin.products.modifiers', compact('product', 'availableModifiers', 'allModifiers'));
    }

    public function store(Request $request, Product $product)
    {
        abort_unless($product->admin_id === panel_owner_id(), 403);

        if ($request->filled('modifier_id')) {
            $data = $request->validate([
                'modifier_id' => [
                    'required',
                    Rule::exists('modifiers', 'id')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
                ],
                'is_required' => 'nullable|boolean',
            ]);

            ProductModifier::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'modifier_id' => $data['modifier_id'],
                ],
                [
                    'is_required' => $request->boolean('is_required'),
                ]
            );

            return redirect()
                ->route('admin.products.modifiers.index', $product)
                ->with('success', 'Modifier attached to product.');
        }

        $data = $request->validate([
            'group_name' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $modifier = Modifier::create([
            'admin_id' => panel_owner_id(),
            'group_name' => $data['group_name'],
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
            'sort_order' => 0,
        ]);

        ProductModifier::create([
            'product_id' => $product->id,
            'modifier_id' => $modifier->id,
            'is_required' => $request->boolean('is_required'),
        ]);

        return redirect()
            ->route('admin.products.modifiers.index', $product)
            ->with('success', 'Modifier created and attached.');
    }

    public function update(Request $request, Product $product, ProductModifier $productModifier)
    {
        abort_unless($product->admin_id === panel_owner_id(), 403);
        abort_unless($productModifier->product_id === $product->id, 404);

        $data = $request->validate([
            'is_required' => 'nullable|boolean',
        ]);

        $productModifier->update([
            'is_required' => $request->boolean('is_required'),
        ]);

        return redirect()
            ->route('admin.products.modifiers.index', $product)
            ->with('success', 'Modifier attachment updated.');
    }

    public function destroy(Product $product, ProductModifier $productModifier)
    {
        abort_unless($product->admin_id === panel_owner_id(), 403);
        abort_unless($productModifier->product_id === $product->id, 404);

        $productModifier->delete();

        return redirect()
            ->route('admin.products.modifiers.index', $product)
            ->with('success', 'Modifier removed from product.');
    }
}




