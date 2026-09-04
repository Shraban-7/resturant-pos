<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RecipeController extends Controller
{
    public function edit(Product $product)
    {
        abort_unless((int) $product->seller_id === (int) panel_owner_id(), 403);

        $product->load(['recipe.ingredients.ingredientProduct', 'unit']);

        $ingredients = Product::self()
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->get(['id', 'name', 'stock_in', 'stock_out']);

        return view('admin.products.recipe', compact('product', 'ingredients'));
    }

    public function update(Request $request, Product $product)
    {
        abort_unless((int) $product->seller_id === (int) panel_owner_id(), 403);

        $data = $request->validate([
            'is_active' => 'nullable|boolean',
            'preparation_time_minutes' => 'nullable|integer|min:1|max:480',
            'instructions' => 'nullable|string|max:5000',
            'ingredients' => 'nullable|array',
            'ingredients.*.ingredient_product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn ($q) => $q->where('seller_id', panel_owner_id())),
            ],
            'ingredients.*.quantity' => 'required|numeric|min:0.001',
        ]);

        DB::transaction(function () use ($product, $data, $request) {
            $recipe = Recipe::withTrashed()->firstOrNew(['product_id' => $product->id]);
            $recipe->seller_id = panel_owner_id();
            $recipe->is_active = $request->boolean('is_active', true);
            $recipe->preparation_time_minutes = $data['preparation_time_minutes'] ?? 15;
            $recipe->instructions = $data['instructions'] ?? null;

            if ($recipe->trashed()) {
                $recipe->restore();
            }

            $recipe->save();

            $recipe->ingredients()->delete();

            $seen = [];
            foreach ($data['ingredients'] ?? [] as $line) {
                $ingredientId = (int) $line['ingredient_product_id'];
                if ($ingredientId === (int) $product->id || isset($seen[$ingredientId])) {
                    continue;
                }
                $seen[$ingredientId] = true;

                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'ingredient_product_id' => $ingredientId,
                    'quantity' => $line['quantity'],
                    'unit_id' => Product::query()->whereKey($ingredientId)->value('unit_id'),
                ]);
            }
        });

        return redirect()
            ->route('admin.products.recipe.edit', $product)
            ->with('success', 'Recipe saved.');
    }

    public function destroy(Product $product)
    {
        abort_unless((int) $product->seller_id === (int) panel_owner_id(), 403);

        $recipe = $product->recipe;
        if ($recipe) {
            $recipe->ingredients()->delete();
            $recipe->delete();
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Recipe removed. Product will deduct finished-goods stock.');
    }
}



