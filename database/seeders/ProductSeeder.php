<?php

namespace Database\Seeders;

use Faker\Factory as Faker;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $seller_id = User::where('role', 'seller')->first()->id;

        for ($i = 0; $i < 100; $i++) {

            $product = Product::create([
                'name' => $faker->word(),
                'seller_id' => $seller_id,
                'image' => 'images/items/product.jpg',
                'unit_id' => ProductUnit::inRandomOrder()->first()->id,
                'buying_price' => 500,
                'selling_price' => 599,
                'stock_in' => 100,
                'stock_out' => rand(1, 90),
                'is_active' => rand(0, 1),
            ]);

            ProductStock::create([
                'product_id' => $product->id,
                'seller_id' => $product->seller_id,
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
