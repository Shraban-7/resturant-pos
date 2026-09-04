<?php

namespace Database\Seeders;

use App\Enums\ProductType;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds products from database/seeders/data/products.json.
 *
 * JSON fields per item:
 *   name, category, buying_price, selling_price, stock_in, unit, meal, type
 *   meal = "all" (NULL = served all day) or array like ["breakfast","lunch"].
 *   type = "dish" (default) or "buffet" (per-person, unlimited, no stock impact).
 * Image is always stored as NULL (no placeholder images).
 */
class ProductSeeder extends Seeder
{
    public const JSON_PATH = 'seeders/data/products.json';

    public function run(): void
    {
        $ownerId = User::admin()->orderBy('id')->first()->id;

        $items = json_decode(file_get_contents(database_path(self::JSON_PATH)), true);

        if (! is_array($items)) {
            $this->command->error('products.json is invalid.');

            return;
        }

        // Branch specials: every 6th item belongs to one branch, rest are chain-wide (NULL).
        $branchIds = \App\Models\Branch::where('admin_id', $ownerId)->orderBy('id')->pluck('id')->all();

        foreach (array_values($items) as $index => $item) {
            $branchId = null;
            if ($branchIds && $index % 6 === 5) {
                $branchId = $branchIds[(int) ($index / 6) % count($branchIds)];
            }

            $category = ProductCategory::firstOrCreate(
                ['admin_id' => $ownerId, 'name' => $item['category']],
                ['admin_id' => $ownerId, 'name' => $item['category']]
            );

            $unit = ProductUnit::firstOrCreate(
                ['name' => $item['unit']],
                ['short_name' => strtolower(substr($item['unit'], 0, 3))]
            );

            $mealTimes = ($item['meal'] ?? 'all') === 'all' ? null : array_values($item['meal']);
            $type = ProductType::from($item['type'] ?? ProductType::DISH->value);

            $product = Product::firstOrCreate(
                ['admin_id' => $ownerId, 'name' => $item['name']],
                [
                    'admin_id' => $ownerId,
                    'branch_id' => $branchId,
                    'type' => $type,
                    'meal_times' => $mealTimes,
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

            // Backfill meal times + type on rows seeded before these features existed.
            $backfill = [];
            if ($product->meal_times != $mealTimes) {
                $backfill['meal_times'] = $mealTimes;
            }
            if (($product->type ?? ProductType::DISH) !== $type) {
                $backfill['type'] = $type;
            }
            if ($backfill) {
                $product->update($backfill);
            }

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
    }
}




