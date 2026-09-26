<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addition;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdditionController extends Controller
{
    public function index()
    {
        $additions = Addition::query()
            ->where('admin_id', panel_owner_id())
            ->withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.additions.index', compact('additions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('additions', 'name')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'is_active' => 'nullable|boolean',
        ]);

        Addition::create([
            'admin_id' => panel_owner_id(),
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : true,
        ]);

        return redirect()->back()->with('success', 'Addition saved.');
    }

    /**
     * Create an addition on the fly from the POS item modal.
     * Optionally attaches it to the given product.
     */
    public function quickStore(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('additions', 'name')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'product_id' => 'nullable|integer|exists:products,id',
        ]);

        $addition = Addition::create([
            'admin_id' => panel_owner_id(),
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'is_active' => true,
        ]);

        if (! empty($data['product_id'])) {
            $product = Product::whereKey($data['product_id'])
                ->where('admin_id', panel_owner_id())
                ->first();

            if ($product && ! $product->isIngredient()) {
                $product->extras()->syncWithoutDetaching([$addition->id]);
            }
        }

        return apiResponse([
            'addition' => [
                'id' => $addition->id,
                'name' => $addition->name,
                'price' => (float) $addition->price,
            ],
        ], 'Addition saved.');
    }

    public function update(Request $request, Addition $addition)
    {
        abort_unless((int) $addition->admin_id === (int) panel_owner_id(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('additions', 'name')
                    ->where(fn ($q) => $q->where('admin_id', panel_owner_id()))
                    ->ignore($addition->id),
            ],
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'is_active' => 'nullable|boolean',
        ]);

        $addition->update([
            'name' => $data['name'],
            'price' => $data['price'] ?? 0,
            'is_active' => array_key_exists('is_active', $data) ? (bool) $data['is_active'] : $addition->is_active,
        ]);

        return redirect()->back()->with('success', 'Addition saved.');
    }

    public function destroy(Addition $addition)
    {
        abort_unless((int) $addition->admin_id === (int) panel_owner_id(), 403);

        $addition->products()->detach();
        $addition->delete();

        return redirect()->back()->with('success', 'Addition deleted.');
    }
}
