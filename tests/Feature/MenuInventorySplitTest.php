<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class MenuInventorySplitTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    public function test_menu_and_inventory_pages_render(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->get(route('admin.products.index'))->assertOk();
        $this->get(route('admin.products.create'))->assertOk();
        $this->get(route('admin.inventory.index'))->assertOk();
        $this->get(route('admin.inventory.create'))->assertOk();
        $this->get(route('admin.categories.index'))->assertOk();
        $this->get(route('admin.stocks.index'))->assertOk();
        $this->get(route('admin.stocks.create'))->assertOk();
        $this->get(route('admin.purchases.index'))->assertOk();
        $this->get(route('admin.pos.index'))->assertOk();
    }

    public function test_menu_crud_creates_pure_menu_row(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $category = $this->createCategory($admin);
        $unit = $this->createUnit();

        $response = $this->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'name' => 'Test Dish',
            'selling_price' => 250,
            'type' => 'dish',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'admin_id' => $admin->id,
            'name' => 'Test Dish',
            'type' => 'dish',
            'selling_price' => 250,
            'buying_price' => 0,
            'stock_in' => 0,
            'stock_out' => 0,
        ]);
        $this->assertDatabaseCount('product_stocks', 0);
    }

    public function test_menu_rejects_ingredient_type(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $response = $this->post(route('admin.products.store'), [
            'category_id' => $this->createCategory($admin)->id,
            'unit_id' => $this->createUnit()->id,
            'name' => 'Bad Raw',
            'selling_price' => 10,
            'type' => 'ingredient',
        ]);

        $response->assertSessionHasErrors('type');
    }

    public function test_inventory_crud_creates_raw_with_ledger(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $supplier = \App\Models\Supplier::create(['admin_id' => $admin->id, 'name' => 'Fresh Farm']);

        $response = $this->post(route('admin.inventory.store'), [
            'unit_id' => $this->createUnit()->id,
            'supplier_id' => $supplier->id,
            'reorder_level' => 10,
            'name' => 'Raw Chicken',
            'buying_price' => 180,
            'stock_in' => 50,
        ]);

        $response->assertRedirect(route('admin.inventory.index'));
        $this->assertDatabaseHas('products', [
            'admin_id' => $admin->id,
            'name' => 'Raw Chicken',
            'type' => 'ingredient',
            'supplier_id' => $supplier->id,
            'reorder_level' => 10,
        ]);
        $this->assertDatabaseHas('product_stocks', ['quantity' => 50, 'type' => 'increment']);
    }

    public function test_inventory_rejects_other_admin_supplier(): void
    {
        $admin = $this->createAdmin();
        $other = $this->createAdmin();
        $this->actingAs($admin);
        $foreign = \App\Models\Supplier::create(['admin_id' => $other->id, 'name' => 'Stranger']);

        $response = $this->post(route('admin.inventory.store'), [
            'unit_id' => $this->createUnit()->id,
            'supplier_id' => $foreign->id,
            'name' => 'Bad Link',
            'buying_price' => 10,
            'stock_in' => 5,
        ]);

        $response->assertSessionHasErrors('supplier_id');
    }

    public function test_menu_edit_blocks_raw_and_inventory_edit_blocks_dish(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $dish = $this->createProduct($admin, ['type' => 'dish']);
        $raw = $this->createProduct($admin, ['type' => 'ingredient']);

        $this->get(route('admin.products.edit', $dish))->assertOk();
        $this->get(route('admin.products.edit', $raw))->assertNotFound();
        $this->get(route('admin.inventory.edit', $raw))->assertOk();
        $this->get(route('admin.inventory.edit', $dish))->assertNotFound();
    }

    public function test_recipe_less_dish_always_sellable_at_pos(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        // Zero finished stock, no recipe.
        $dish = $this->createProduct($admin, ['type' => 'dish', 'stock_in' => 0, 'stock_out' => 0]);
        $cart = $this->createCart($admin);

        $response = $this->postJson(route('admin.pos.addItem'), [
            'order_id' => $cart->order_id,
            'product_id' => $dish->id,
            'quantity' => 2,
            'unit_price' => 100,
            'discount' => 0,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $response->assertJsonPath('data.item.unlimited', true);
        $this->assertSame(0, (int) $dish->fresh()->stock_out);
    }

    public function test_recipe_dish_blocked_when_ingredient_short(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $dish = $this->createProduct($admin, ['type' => 'dish', 'stock_in' => 0]);
        $raw = $this->createProduct($admin, ['type' => 'ingredient', 'stock_in' => 1, 'stock_out' => 0]);
        $this->attachRecipe($dish, [$raw->id => 2]); // needs 2 per serving
        $cart = $this->createCart($admin);

        $response = $this->postJson(route('admin.pos.addItem'), [
            'order_id' => $cart->order_id,
            'product_id' => $dish->id,
            'quantity' => 1,
            'unit_price' => 100,
            'discount' => 0,
        ]);

        $response->assertStatus(400)->assertJson(['status' => false]);
    }

    public function test_category_crud(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->post(route('admin.categories.store'), ['name' => 'Beverages'])->assertRedirect();
        $this->assertDatabaseHas('product_categories', ['admin_id' => $admin->id, 'name' => 'Beverages']);
    }
}
