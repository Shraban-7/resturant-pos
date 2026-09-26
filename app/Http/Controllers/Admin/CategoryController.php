<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = ProductCategory::query()
            ->where('admin_id', panel_owner_id())
            ->withCount(['products' => fn ($q) => $q->where('admin_id', panel_owner_id())])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_categories', 'name')->where(fn ($q) => $q->where('admin_id', panel_owner_id())),
            ],
        ]);

        ProductCategory::create([
            'admin_id' => panel_owner_id(),
            'name' => $data['name'],
        ]);

        return redirect()->back()->with('success', 'Category saved.');
    }

    public function update(Request $request, ProductCategory $category)
    {
        abort_unless((int) $category->admin_id === (int) panel_owner_id(), 403);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('product_categories', 'name')
                    ->where(fn ($q) => $q->where('admin_id', panel_owner_id()))
                    ->ignore($category->id),
            ],
        ]);

        $category->update(['name' => $data['name']]);

        return redirect()->back()->with('success', 'Category saved.');
    }

    public function destroy(ProductCategory $category)
    {
        abort_unless((int) $category->admin_id === (int) panel_owner_id(), 403);

        if ($category->products()->where('admin_id', panel_owner_id())->exists()) {
            return redirect()->back()->with('error', 'Cannot delete: products use this category.');
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted.');
    }
}
