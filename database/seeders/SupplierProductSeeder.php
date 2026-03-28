<?php

namespace Database\Seeders;

use App\Models\ProductUnit;
use App\Models\SupplierProduct;
use App\Models\SupplierProductCategory;
use App\Models\SupplierProductStock;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierProductSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            'Grains & Rice' => ['Basmati Rice', 'Brown Rice', 'Parboiled Rice', 'Jasmine Rice', 'Wheat Flour', 'Maida', 'Semolina', 'Corn Flour', 'Oats', 'Barley'],
            'Dairy & Eggs' => ['Full Cream Milk', 'Skim Milk', 'Cheddar Cheese', 'Mozzarella Cheese', 'Butter', 'Yogurt', 'Cream', 'Condensed Milk', 'Eggs - Large', 'Eggs - Medium'],
            'Meat & Poultry' => ['Chicken Breast', 'Whole Chicken', 'Beef Mince', 'Mutton Curry Cut', 'Duck Meat', 'Chicken Wings', 'Chicken Sausages', 'Boneless Beef', 'Turkey Slices', 'Chicken Liver'],
            'Seafood' => ['Frozen Shrimp', 'Tilapia Fillet', 'Salmon', 'Canned Tuna', 'Crab Meat', 'Squid Rings', 'Fish Fillet', 'Anchovies', 'Prawns', 'Fish Roe'],
            'Spices & Seasonings' => ['Black Pepper', 'Cumin', 'Coriander Powder', 'Turmeric', 'Chili Powder', 'Garam Masala', 'Cinnamon', 'Cloves', 'Bay Leaf', 'Paprika'],
            'Oil & Sauces' => ['Sunflower Oil', 'Mustard Oil', 'Soybean Oil', 'Olive Oil', 'Tomato Ketchup', 'Soy Sauce', 'Vinegar', 'Mayonnaise', 'Chili Sauce', 'Barbecue Sauce'],
            'Packaging Supplies' => ['Plastic Containers', 'Paper Bags', 'Aluminum Foil', 'Cling Film', 'Plastic Wrap', 'Napkins', 'Disposable Cups', 'Takeaway Boxes', 'Carry Bags', 'Straws'],
            'Cleaning Supplies' => ['Dishwashing Liquid', 'Surface Cleaner', 'Hand Sanitizer', 'Garbage Bags', 'Sponges', 'Mop', 'Disinfectant', 'Bleach', 'Gloves', 'Detergent Powder'],
            'Baking Supplies' => ['Baking Powder', 'Baking Soda', 'Yeast', 'Vanilla Essence', 'Cake Mix', 'Whipping Cream', 'Cornstarch', 'Gelatin', 'Chocolate Chips', 'Sprinkles'],
            'Utensils & Tools' => ['Cutting Board', 'Knives', 'Tongs', 'Spatula', 'Ladle', 'Peeler', 'Grater', 'Measuring Cups', 'Kitchen Scissors', 'Mixing Bowl']
        ];

        $unitId = ProductUnit::where('name', 'PIECES')->first()->id;
        $supplierId = User::supplier()->first()->id;

        foreach ($categories as $categoryName => $products) {
            $category = SupplierProductCategory::create([
                'name' => $categoryName
            ]);

            foreach ($products as $productName) {
                $buyingPrice = rand(50, 300);
                $sellingPrice = $buyingPrice + rand(10, 100);

                $product = SupplierProduct::create([
                    'supplier_id' => $supplierId,
                    'category_id' => $category->id,
                    'name' => $productName,
                    'unit_id' => $unitId,
                    'buying_price' => $buyingPrice,
                    'selling_price' => $sellingPrice,
                    'stock_in' => 100,
                    'stock_out' => 0,
                    'image' => 'images/products/supplier/' . str_slug($categoryName) . '.jpg',
                ]);

                SupplierProductStock::create([
                    'product_id' => $product->id,
                    'supplier_id' => $product->supplier_id,
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
