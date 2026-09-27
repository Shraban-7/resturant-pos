<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Seeds MENU products from database/seeders/data/products.json with product images.
 *
 * Pure menu: dishes/buffets carry no inventory of their own
 * (stock_in 0, buying_price 0, no ledger rows). Stock lives on
 * raw materials (IngredientSeeder) and is consumed via recipes.
 * Ingredient-type JSON items are skipped here — IngredientSeeder owns them.
 *
 * JSON fields per item:
 *   name, category, selling_price, unit, meal, type, image
 *   meal = "all" (NULL = served all day) or array like ["breakfast","lunch"].
 *   type = "dish" (default) or "buffet" (per-person, unlimited).
 *   image = relative path inside storage (e.g. "images/products/chicken-kacchi-biryani.jpg")
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

        // Ensure product image assets are synchronized to storage/app/public/images/products
        $seedImagesDir = database_path('seeders/data/images/products');
        $targetImagesDir = storage_path('app/public/images/products');

        if (File::isDirectory($seedImagesDir)) {
            File::ensureDirectoryExists($targetImagesDir);
            foreach (File::files($seedImagesDir) as $file) {
                $targetFile = $targetImagesDir . DIRECTORY_SEPARATOR . $file->getFilename();
                if (! file_exists($targetFile)) {
                    File::copy($file->getPathname(), $targetFile);
                }
            }
        }

        // Branch specials: every 6th item belongs to one branch, rest are chain-wide (NULL).
        $branchIds = Branch::where('admin_id', $ownerId)->orderBy('id')->pluck('id')->all();

        foreach (array_values($items) as $index => $item) {
            $type = ProductType::from($item['type'] ?? ProductType::DISH->value);

            // Raw ingredients are owned by IngredientSeeder.
            if ($type === ProductType::INGREDIENT) {
                continue;
            }

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
            $itemImage = $item['image'] ?? null;

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
                    'name_bn' => $item['name_bn'] ?? null,
                    'buying_price' => 0,
                    'selling_price' => $item['selling_price'],
                    'stock_in' => 0,
                    'stock_out' => 0,
                    'image' => $itemImage,
                    'is_active' => 1,
                ]
            );

            // Backfill meal times + type + Bangla name + image on older rows.
            $backfill = [];
            if (isset($item['name_bn']) && $product->name_bn !== $item['name_bn']) {
                $backfill['name_bn'] = $item['name_bn'];
            }
            if ($itemImage && $product->image !== $itemImage) {
                $backfill['image'] = $itemImage;
            }
            $currentMeals = $product->meal_times instanceof \Illuminate\Support\Collection
                ? $product->meal_times->map(fn ($m) => $m instanceof \App\Enums\MealSlot ? $m->value : (string) $m)->all()
                : $product->meal_times;
            if ($currentMeals != $mealTimes) {
                $backfill['meal_times'] = $mealTimes;
            }
            if (($product->type ?? ProductType::DISH) !== $type) {
                $backfill['type'] = $type;
            }
            if ($backfill) {
                $product->update($backfill);
            }

            // Pure menu: no ledger rows for dishes/buffets.
        }
    }
}
