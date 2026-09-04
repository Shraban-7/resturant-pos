<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_recipe_flow(): void
    {
        $this->seed();

        $admin = User::where('email', 'admin@gmail.com')->first();
        $this->assertNotNull($admin);

        $product = Product::where('admin_id', $admin->id)->where('type', 'dish')->first();
        $ingredient = Product::where('admin_id', $admin->id)->rawIngredients()->first();
        // Raw materials must never be directly sellable.
        $this->assertFalse($product->isIngredient());
        $this->assertNotNull($product);
        $this->assertNotNull($ingredient);

        $get = $this->actingAs($admin)->get(route('admin.products.recipe.edit', $product));
        $get->assertOk();

        $put = $this->actingAs($admin)->put(route('admin.products.recipe.update', $product), [
            'is_active' => true,
            'preparation_time_minutes' => 20,
            'instructions' => 'Cook well.',
            'ingredients' => [
                ['ingredient_product_id' => $ingredient->id, 'quantity' => 1.5],
            ],
        ]);
        $put->assertRedirect();

        $this->assertDatabaseHas('recipes', ['product_id' => $product->id]);
        $this->assertDatabaseHas('recipe_ingredients', [
            'ingredient_product_id' => $ingredient->id,
        ]);
    }
}

