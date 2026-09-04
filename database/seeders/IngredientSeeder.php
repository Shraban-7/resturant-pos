<?php

namespace Database\Seeders;

use App\Enums\ProductType;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Raw ingredients driven by YOUR current products:
 *  1. Ensures every "Raw Ingredients" JSON item exists as an ingredient product.
 *  2. Reads every dish recipe and tops raw stock up to cover total demand
 *     (qty per serving x dish opening stock) plus a 20% buffer, with ledger entries.
 * Idempotent: only adds missing stock, never reduces.
 */
class IngredientSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::admin()->orderBy('id')->first()->id;

        $items = json_decode(file_get_contents(database_path(ProductSeeder::JSON_PATH)), true);

        if (! is_array($items)) {
            $this->command->error('products.json is invalid.');

            return;
        }

        foreach ($items as $item) {
            if (($item['type'] ?? 'dish') !== ProductType::INGREDIENT) {
                continue;
            }

            $category = ProductCategory::firstOrCreate(
                ['admin_id' => $ownerId, 'name' => $item['category']],
                ['admin_id' => $ownerId, 'name' => $item['category']]
            );

            $unit = ProductUnit::firstOrCreate(
                ['name' => $item['unit']],
                ['short_name' => strtolower(substr($item['unit'], 0, 3))]
            );

            $product = Product::firstOrCreate(
                ['admin_id' => $ownerId, 'name' => $item['name']],
                [
                    'admin_id' => $ownerId,
                    'branch_id' => null,
                    'type' => ProductType::INGREDIENT,
                    'meal_times' => null,
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'name' => $item['name'],
                    'buying_price' => $item['buying_price'],
                    'selling_price' => $item['selling_price'],
                    'stock_in' => $item['stock_in'],
                    'stock_out' => 0,
                    'image' => null,
                    'is_active' => 1,
                ]
            );

            if (! ProductStock::where('product_id', $product->id)->exists()) {
                ProductStock::create([
                    'product_id' => $product->id,
                    'admin_id' => $ownerId,
                    'type' => 'increment',
                    'quantity' => $product->stock_in,
                    'old_stock' => 0,
                    'new_stock' => $product->stock_in,
                    'buying_price' => $product->buying_price,
                    'selling_price' => $product->selling_price,
                ]);
            }
        }

        // Demand from current recipes: per-serving qty x each dish's opening stock.
        $demand = [];
        $lines = RecipeIngredient::query()
            ->join('recipes', 'recipes.id', '=', 'recipe_ingredients.recipe_id')
            ->join('products as dishes', 'dishes.id', '=', 'recipes.product_id')
            ->where('dishes.admin_id', $ownerId)
            ->selectRaw('recipe_ingredients.ingredient_product_id, SUM(recipe_ingredients.quantity * dishes.stock_in) as total')
            ->groupBy('recipe_ingredients.ingredient_product_id')
            ->get();

        foreach ($lines as $line) {
            $demand[$line->ingredient_product_id] = (float) $line->total;
        }

        foreach ($demand as $ingredientId => $needed) {
            $ingredient = Product::where('admin_id', $ownerId)->whereKey($ingredientId)->first();

            if (! $ingredient || $ingredient->type !== ProductType::INGREDIENT) {
                continue;
            }

            $target = (int) ceil($needed * 1.2); // demand + 20% buffer
            $available = $ingredient->stock_in - $ingredient->stock_out;

            if ($available >= $target) {
                continue;
            }

            $topUp = $target - $available;
            $ingredient->increment('stock_in', $topUp);

            ProductStock::create([
                'product_id' => $ingredient->id,
                'admin_id' => $ownerId,
                'type' => 'increment',
                'quantity' => $topUp,
                'old_stock' => $available,
                'new_stock' => $available + $topUp,
                'buying_price' => $ingredient->buying_price,
                'selling_price' => $ingredient->selling_price,
            ]);
        }

        $this->command->info('Ingredients ensured: '.Product::where('admin_id', $ownerId)->rawIngredients()->count().', demand lines: '.count($demand));
    }
}


