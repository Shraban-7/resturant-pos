<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\LoyaltyPoint;
use App\Models\Modifier;
use App\Models\Product;
use App\Models\ProductModifier;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Reservation;
use App\Models\DiningTable;
use App\Models\User;
use Illuminate\Database\Seeder;

class CatalogExtrasSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::admin()->first()->id;

        $customers = [
            ['name' => 'Rahim Uddin', 'phone' => '01811111111', 'address' => 'Mirpur 10, Dhaka'],
            ['name' => 'Karim Sheikh', 'phone' => '01822222222', 'address' => 'Uttara, Dhaka'],
            ['name' => 'Fatema Begum', 'phone' => '01833333333', 'address' => 'Dhanmondi, Dhaka'],
            ['name' => 'Walk-in Customer', 'phone' => '01800000000', 'address' => 'Dhaka, Bangladesh'],
        ];
        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['seller_id' => $ownerId, 'phone' => $data['phone']],
                $data + ['seller_id' => $ownerId]
            );
        }

        $modifiers = [
            ['group_name' => 'Add-ons', 'name' => 'Extra Cheese', 'price' => 60],
            ['group_name' => 'Add-ons', 'name' => 'Chicken Add-on', 'price' => 120],
            ['group_name' => 'Add-ons', 'name' => 'Egg Add-on', 'price' => 30],
            ['group_name' => 'Spice', 'name' => 'Extra Spicy', 'price' => 0],
        ];
        $modifierIds = [];
        foreach ($modifiers as $i => $data) {
            $m = Modifier::firstOrCreate(
                ['seller_id' => $ownerId, 'name' => $data['name']],
                $data + ['seller_id' => $ownerId, 'is_active' => true, 'sort_order' => $i]
            );
            $modifierIds[] = $m->id;
        }

        $products = Product::where('seller_id', $ownerId)->take(10)->get();
        foreach ($products as $product) {
            foreach ($modifierIds as $mid) {
                ProductModifier::firstOrCreate(
                    ['product_id' => $product->id, 'modifier_id' => $mid],
                    ['product_id' => $product->id, 'modifier_id' => $mid]
                );
            }
            $recipe = Recipe::firstOrCreate(
                ['product_id' => $product->id],
                ['product_id' => $product->id, 'seller_id' => $ownerId, 'instructions' => 'Cook fresh and serve hot.']
            );
            $ingredient = $products->where('id', '!=', $product->id)->first();
            if ($ingredient) {
                RecipeIngredient::firstOrCreate(
                    ['recipe_id' => $recipe->id, 'ingredient_product_id' => $ingredient->id],
                    ['recipe_id' => $recipe->id, 'ingredient_product_id' => $ingredient->id, 'quantity' => 1, 'unit_id' => $product->unit_id]
                );
            }
        }

        foreach (Customer::where('seller_id', $ownerId)->take(3)->get() as $customer) {
            LoyaltyPoint::firstOrCreate(
                ['seller_id' => $ownerId, 'customer_id' => $customer->id, 'type' => 'earned'],
                ['seller_id' => $ownerId, 'customer_id' => $customer->id, 'type' => 'earned', 'points' => 100, 'equivalent_amount' => 100, 'description' => 'Welcome bonus']
            );
        }

        foreach ([['code' => 'GIFT-1000', 'value' => 1000], ['code' => 'GIFT-500', 'value' => 500]] as $data) {
            GiftCard::firstOrCreate(
                ['code' => $data['code']],
                [
                    'seller_id' => $ownerId,
                    'code' => $data['code'],
                    'initial_value' => $data['value'],
                    'balance' => $data['value'],
                    'expiry_date' => now()->addYear()->toDateString(),
                    'is_active' => true,
                ]
            );
        }

        $table = DiningTable::where('seller_id', $ownerId)->first();
        foreach ([1, 2] as $i) {
            if (! $table) {
                break;
            }
            Reservation::firstOrCreate(
                ['seller_id' => $ownerId, 'customer_phone' => "0190000000{$i}"],
                [
                    'seller_id' => $ownerId,
                    'table_id' => $table->id,
                    'customer_name' => "Guest {$i}",
                    'customer_phone' => "0190000000{$i}",
                    'guest_count' => 2 + $i,
                    'reservation_time' => now()->addDays($i)->setTime(19, 0)->toDateTimeString(),
                    'status' => 'confirmed',
                ]
            );
        }
    }
}
