<?php

namespace Database\Seeders;

use App\Enums\ProductType;
use App\Models\Addition;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds both add-on systems (idempotent, multi-admin safe):
 *
 * 1. Priced extras (additions catalog, e.g. Extra Cheese) + attaches
 *    them to every active sellable dish via `addition_product`.
 * 2. Suggested items (`product_addons`, e.g. Coke with Burger) by
 *    linking each dish to the next 2 sellable dishes.
 */
class AddonSeeder extends Seeder
{
    public function run(): void
    {
        $admins = User::admin()->orderBy('id')->get();

        if ($admins->isEmpty()) {
            return;
        }

        $catalog = [
            ['name' => 'Extra Cheese', 'price' => 60],
            ['name' => 'Chicken Add-on', 'price' => 120],
            ['name' => 'Egg Add-on', 'price' => 30],
            ['name' => 'Extra Sauce', 'price' => 25],
            ['name' => 'Extra Spicy', 'price' => 0],
        ];

        foreach ($admins as $admin) {
            $ownerId = $admin->id;

            foreach (array_values($catalog) as $i => $data) {
                Addition::firstOrCreate(
                    ['admin_id' => $ownerId, 'name' => $data['name']],
                    [
                        'admin_id' => $ownerId,
                        'name' => $data['name'],
                        'price' => $data['price'],
                        'is_active' => true,
                        'sort_order' => $i,
                    ]
                );
            }

            $dishes = Product::query()
                ->where('admin_id', $ownerId)
                ->whereIn('type', [ProductType::DISH, ProductType::BUFFET])
                ->where('is_active', 1)
                ->orderBy('id')
                ->get();

            if ($dishes->isEmpty()) {
                continue;
            }

            // 1. Every dish gets every active addition as a selectable extra.
            $additionIds = Addition::query()
                ->where('admin_id', $ownerId)
                ->where('is_active', true)
                ->pluck('id')
                ->all();

            foreach ($dishes as $dish) {
                if (! empty($additionIds)) {
                    $dish->extras()->syncWithoutDetaching($additionIds);
                }
            }

            // 2. Each dish suggests the next 2 sellable dishes (never itself).
            if ($dishes->count() < 2) {
                continue;
            }

            $ids = $dishes->pluck('id')->all();

            foreach ($dishes as $index => $dish) {
                $suggestions = [];
                for ($k = 1; $k <= min(2, count($ids) - 1); $k++) {
                    $suggestions[] = $ids[($index + $k) % count($ids)];
                }
                if (! empty($suggestions)) {
                    $dish->addons()->syncWithoutDetaching($suggestions);
                }
            }
        }
    }
}
