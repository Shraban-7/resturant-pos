<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds product categories only (one per cuisine).
 * Products themselves come from ProductSeeder (database/seeders/data/products.json).
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $sellerId = User::admin()->orderBy('id')->first()->id;

        $categories = collect(json_decode(
            file_get_contents(database_path('seeders/data/products.json')),
            true
        ))->pluck('category')->unique()->values();

        foreach ($categories as $name) {
            ProductCategory::firstOrCreate(
                ['seller_id' => $sellerId, 'name' => $name],
                ['seller_id' => $sellerId, 'name' => $name]
            );
        }
    }
}
