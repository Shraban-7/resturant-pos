<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class ProductAddonTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    public function test_can_attach_and_detach_addon(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $burger = $this->createProduct($admin, ['name' => 'Burger', 'type' => 'dish']);
        $coke = $this->createProduct($admin, ['name' => 'Coke', 'type' => 'dish']);

        $this->get(route('admin.products.addons.index', $burger))->assertOk();

        $this->post(route('admin.products.addons.store', $burger), [
            'addon_product_id' => $coke->id,
        ])->assertRedirect(route('admin.products.addons.index', $burger));

        $this->assertDatabaseHas('product_addons', [
            'product_id' => $burger->id,
            'addon_product_id' => $coke->id,
        ]);

        // Duplicate attach stays idempotent.
        $this->post(route('admin.products.addons.store', $burger), [
            'addon_product_id' => $coke->id,
        ])->assertRedirect();
        $this->assertSame(1, $burger->fresh()->addons()->count());

        $this->delete(route('admin.products.addons.destroy', [$burger, $coke]))->assertRedirect();
        $this->assertDatabaseMissing('product_addons', [
            'product_id' => $burger->id,
            'addon_product_id' => $coke->id,
        ]);
    }

    public function test_addon_rejects_self_and_raw_and_foreign_items(): void
    {
        $admin = $this->createAdmin();
        $other = $this->createAdmin();
        $this->actingAs($admin);
        $burger = $this->createProduct($admin, ['type' => 'dish']);
        $raw = $this->createProduct($admin, ['type' => 'ingredient']);
        $foreign = $this->createProduct($other, ['type' => 'dish']);

        // Self suggestion.
        $this->post(route('admin.products.addons.store', $burger), [
            'addon_product_id' => $burger->id,
        ])->assertStatus(422);

        // Raw material can never be an add-on.
        $this->post(route('admin.products.addons.store', $burger), [
            'addon_product_id' => $raw->id,
        ])->assertSessionHasErrors('addon_product_id');

        // Another admin's item.
        $this->post(route('admin.products.addons.store', $burger), [
            'addon_product_id' => $foreign->id,
        ])->assertSessionHasErrors('addon_product_id');

        // Raw parent page itself 404s.
        $this->get(route('admin.products.addons.index', $raw))->assertNotFound();
    }

    public function test_pos_suggests_attached_addons(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $burger = $this->createProduct($admin, ['name' => 'Burger', 'type' => 'dish']);
        $coke = $this->createProduct($admin, ['name' => 'Coke XYZ', 'type' => 'dish', 'selling_price' => 50]);
        $burger->addons()->attach($coke->id);

        $response = $this->get(route('admin.pos.index'))->assertOk();
        $response->assertSee('Coke XYZ');
    }

    public function test_quick_added_addon_lands_as_own_sale_line(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $burger = $this->createProduct($admin, ['type' => 'dish']);
        $coke = $this->createProduct($admin, ['type' => 'dish', 'selling_price' => 50, 'stock_in' => 0]);
        $burger->addons()->attach($coke->id);
        $cart = $this->createCart($admin);

        $response = $this->postJson(route('admin.pos.addItem'), [
            'order_id' => $cart->order_id,
            'product_id' => $coke->id,
            'quantity' => 1,
            'unit_price' => 50,
            'discount' => 0,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('cart_items', ['cart_id' => $cart->id, 'item_id' => $coke->id]);
    }
}
