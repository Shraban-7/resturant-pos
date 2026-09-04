<?php

namespace Database\Seeders;

use App\Enums\ProductType;

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
                ['admin_id' => $ownerId, 'phone' => $data['phone']],
                $data + ['admin_id' => $ownerId]
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
                ['admin_id' => $ownerId, 'name' => $data['name']],
                $data + ['admin_id' => $ownerId, 'is_active' => true, 'sort_order' => $i]
            );
            $modifierIds[] = $m->id;
        }

        // Demo recipes consume RAW ingredients (never other dishes).
        // Round-robin so every raw gets used by at least one dish.
        $dishes = Product::where('admin_id', $ownerId)->where('type', ProductType::DISH)->take(10)->get();
        $raws = Product::where('admin_id', $ownerId)->rawIngredients()->orderBy('id')->get()->values();

        foreach ($dishes->values() as $index => $product) {
            foreach ($modifierIds as $mid) {
                ProductModifier::firstOrCreate(
                    ['product_id' => $product->id, 'modifier_id' => $mid],
                    ['product_id' => $product->id, 'modifier_id' => $mid]
                );
            }
            $recipe = Recipe::firstOrCreate(
                ['product_id' => $product->id],
                ['product_id' => $product->id, 'admin_id' => $ownerId, 'instructions' => 'Cook fresh and serve hot.']
            );
            // 2 raw ingredients per demo dish, quantities per serving.
            for ($k = 0; $k < min(2, $raws->count()); $k++) {
                $ingredient = $raws[($index * 2 + $k) % $raws->count()];
                RecipeIngredient::firstOrCreate(
                    ['recipe_id' => $recipe->id, 'ingredient_product_id' => $ingredient->id],
                    ['recipe_id' => $recipe->id, 'ingredient_product_id' => $ingredient->id, 'quantity' => rand(1, 3) / 2, 'unit_id' => $ingredient->unit_id]
                );
            }
        }

        foreach (Customer::where('admin_id', $ownerId)->take(3)->get() as $customer) {
            LoyaltyPoint::firstOrCreate(
                ['admin_id' => $ownerId, 'customer_id' => $customer->id, 'type' => 'earned'],
                ['admin_id' => $ownerId, 'customer_id' => $customer->id, 'type' => 'earned', 'points' => 100, 'equivalent_amount' => 100, 'description' => 'Welcome bonus']
            );
        }

        foreach ([['code' => 'GIFT-1000', 'value' => 1000], ['code' => 'GIFT-500', 'value' => 500]] as $data) {
            GiftCard::firstOrCreate(
                ['code' => $data['code']],
                [
                    'admin_id' => $ownerId,
                    'code' => $data['code'],
                    'initial_value' => $data['value'],
                    'balance' => $data['value'],
                    'expiry_date' => now()->addYear()->toDateString(),
                    'is_active' => true,
                ]
            );
        }

        $table = DiningTable::where('admin_id', $ownerId)->first();
        foreach ([1, 2] as $i) {
            if (! $table) {
                break;
            }
            Reservation::firstOrCreate(
                ['admin_id' => $ownerId, 'customer_phone' => "0190000000{$i}"],
                [
                    'admin_id' => $ownerId,
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



