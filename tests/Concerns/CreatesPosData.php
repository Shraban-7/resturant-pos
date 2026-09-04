<?php

namespace Tests\Concerns;

use App\Enums\TableStatus;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Convenience builders for POS/KDS/QR feature tests.
 */
trait CreatesPosData
{
    protected function createAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => 'admin'], $attributes));
    }

    protected function createUnit(string $short = 'pcs'): ProductUnit
    {
        return ProductUnit::create([
            'name' => Str::title($short).' '.Str::random(4),
            'short_name' => $short.'-'.Str::random(3),
        ]);
    }

    protected function createCategory(User $admin): ProductCategory
    {
        return ProductCategory::create([
            'admin_id' => $admin->id,
            'name' => 'Category '.Str::random(4),
        ]);
    }

    protected function createProduct(User $admin, array $attributes = []): Product
    {
        return Product::create(array_merge([
            'admin_id' => $admin->id,
            'category_id' => $this->createCategory($admin)->id,
            'unit_id' => $this->createUnit()->id,
            'name' => 'Product '.Str::random(5),
            'image' => '',
            'buying_price' => 40,
            'selling_price' => 100,
            'stock_in' => 100,
            'stock_out' => 0,
            'is_active' => 1,
        ], $attributes));
    }

    protected function createTable(User $admin, array $attributes = []): DiningTable
    {
        return DiningTable::create(array_merge([
            'admin_id' => $admin->id,
            'name' => 'Table '.Str::random(3),
            'status' => TableStatus::FREE,
        ], $attributes));
    }

    protected function createCart(User $admin, string $orderId = null): Cart
    {
        return Cart::create([
            'admin_id' => $admin->id,
            'order_id' => $orderId ?? generateOrderId(),
        ]);
    }

    protected function addCartItem(Cart $cart, Product $product, float $quantity = 1, array $attributes = []): CartItem
    {
        $unitPrice = $attributes['unit_price'] ?? (float) $product->selling_price;
        $discount = $attributes['discount'] ?? 0;

        return CartItem::create(array_merge([
            'cart_id' => $cart->id,
            'item_id' => $product->id,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'quantity' => $quantity,
            'total_price' => ($unitPrice * $quantity) - $discount,
        ], $attributes));
    }

    protected function attachRecipe(Product $product, array $ingredients): Recipe
    {
        $recipe = Recipe::create([
            'admin_id' => $product->admin_id,
            'product_id' => $product->id,
            'is_active' => true,
        ]);

        foreach ($ingredients as $ingredientProductId => $quantity) {
            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'ingredient_product_id' => $ingredientProductId,
                'quantity' => $quantity,
            ]);
        }

        return $recipe;
    }
}






