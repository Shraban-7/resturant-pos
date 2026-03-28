<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ProductUnit;
use App\Models\User;

class CategoryProductSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Set Menu',
            'Appetizer',
            'Pasta',
            'Soft Drinks',
            'Main Course',
            'Desserts',
            'Seafood',
            'Salads',
            'Soups',
            'BBQ'
        ];

        $unitId = ProductUnit::where('name', 'PIECES')->first()->id;
        $sellerId = User::seller()->first()->id;

        foreach ($categories as $categoryName) {
            $category = ProductCategory::create([
                'name' => $categoryName
            ]);

            for ($i = 1; $i <= 10; $i++) {
                $productName = $this->generateProductName($categoryName, $i);

                $buyingPrice = rand(100, 500);
                $sellingPrice = $buyingPrice + rand(50, 200);

                $product = Product::create([
                    'seller_id' => $sellerId,
                    'category_id' => $category->id,
                    'unit_id' => $unitId,
                    'name' => $productName,
                    'buying_price' => $buyingPrice,
                    'selling_price' => $sellingPrice,
                    'stock_in' => 100,
                    'stock_out' => 0,
                    'image' => 'images/products/' . str_slug($categoryName) . '.jpg',
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

    private function generateProductName($category, $index): string
    {
        $baseNames = [
            'Set Menu' => ['Family Set', 'Solo Set', 'Value Set'],
            'Appetizer' => ['Spring Rolls', 'Garlic Bread', 'Chicken Wings'],
            'Pasta' => ['Spaghetti', 'Fettuccine', 'Penne Alfredo'],
            'Soft Drinks' => ['Coke', 'Sprite', 'Fanta', 'Pepsi'],
            'Main Course' => ['Grilled Chicken', 'Steak', 'Butter Chicken'],
            'Desserts' => ['Chocolate Cake', 'Ice Cream', 'Brownie'],
            'Seafood' => ['Fried Shrimp', 'Grilled Salmon', 'Crab Curry'],
            'Salads' => ['Caesar Salad', 'Greek Salad', 'Garden Mix'],
            'Soups' => ['Tomato Soup', 'Chicken Corn Soup', 'Hot & Sour'],
            'BBQ' => ['Beef Kebab', 'BBQ Chicken', 'Mutton Chops'],
        ];

        $randomName = $baseNames[$category][array_rand($baseNames[$category])];

        return "$randomName $index";
    }
}
