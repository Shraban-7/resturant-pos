<?php

namespace Tests\Unit;

use App\Actions\DeductRecipeStockAction;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeductRecipeStockActionTest extends TestCase
{
    use RefreshDatabase;

    private DeductRecipeStockAction $action;

    private int $unitId;

    private int $ownerId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new DeductRecipeStockAction(new StockService());
        $this->unitId = ProductUnit::create(['name' => 'Piece', 'short_name' => 'pcs'])->id;
        $this->ownerId = User::factory()->create(['role' => 'admin'])->id;
    }

    private function product(string $name, int $stockIn = 100, int $stockOut = 0): Product
    {
        return Product::create([
            'admin_id' => $this->ownerId,
            'category_id' => 1,
            'unit_id' => $this->unitId,
            'name' => $name,
            'image' => '',
            'buying_price' => 10,
            'selling_price' => 20,
            'stock_in' => $stockIn,
            'stock_out' => $stockOut,
            'is_active' => 1,
        ]);
    }

    public function test_deducts_finished_product_stock_when_no_recipe(): void
    {
        $product = $this->product('Bottled Water', stockIn: 50);

        $this->action->execute($product, 4);

        $this->assertSame(4, (int) $product->fresh()->stock_out);
        $this->assertFalse($this->action->usesRecipe($product));
    }

    public function test_deducts_ingredient_stock_using_recipe_bom(): void
    {
        $burger = $this->product('Burger', stockIn: 0);
        $bun = $this->product('Bun', stockIn: 100);
        $patty = $this->product('Patty', stockIn: 100);

        $recipe = Recipe::create([
            'admin_id' => $this->ownerId,
            'product_id' => $burger->id,
            'preparation_time_minutes' => 10,
            'is_active' => true,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_product_id' => $bun->id,
            'quantity' => 2,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_product_id' => $patty->id,
            'quantity' => 1,
        ]);

        $this->action->execute($burger->fresh(), 3);

        $this->assertTrue($this->action->usesRecipe($burger->fresh()));
        // 2 buns * 3 orders = 6; 1 patty * 3 = 3.
        $this->assertSame(6, (int) $bun->fresh()->stock_out);
        $this->assertSame(3, (int) $patty->fresh()->stock_out);
        // Finished product should not be touched when a recipe drives deduction.
        $this->assertSame(0, (int) $burger->fresh()->stock_out);
    }

    public function test_restore_reverses_ingredient_deduction(): void
    {
        $burger = $this->product('Burger', stockIn: 0);
        $bun = $this->product('Bun', stockIn: 100, stockOut: 6);
        $patty = $this->product('Patty', stockIn: 100, stockOut: 3);

        $recipe = Recipe::create([
            'admin_id' => $this->ownerId,
            'product_id' => $burger->id,
            'is_active' => true,
        ]);

        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_product_id' => $bun->id,
            'quantity' => 2,
        ]);
        RecipeIngredient::create([
            'recipe_id' => $recipe->id,
            'ingredient_product_id' => $patty->id,
            'quantity' => 1,
        ]);

        $this->action->restore($burger->fresh(), 3);

        $this->assertSame(0, (int) $bun->fresh()->stock_out);
        $this->assertSame(0, (int) $patty->fresh()->stock_out);
    }

    public function test_execute_is_noop_for_non_positive_quantity(): void
    {
        $product = $this->product('Soda', stockIn: 20);

        $this->action->execute($product, 0);

        $this->assertSame(0, (int) $product->fresh()->stock_out);
    }
}




