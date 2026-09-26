<?php

namespace Tests\Feature;

use App\Enums\OrderType;
use App\Enums\TableStatus;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class PosOrderModeTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    public function test_dine_in_checkout_requires_a_table(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct($admin);
        $cart = $this->createCart($admin);
        $this->addCartItem($cart, $product, 1, ['unit_price' => 100]);

        $response = $this->actingAs($admin)->postJson(route('admin.pos.checkout'), [
            'order_id' => $cart->order_id,
            'order_type' => 'dine_in',
            'payment_type' => 'cash',
            'paid_amount' => 100,
        ]);

        $response->assertStatus(400)->assertJson(['status' => false]);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_dine_in_checkout_occupies_table(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct($admin);
        $table = $this->createTable($admin);
        $cart = $this->createCart($admin);
        $this->addCartItem($cart, $product, 1, ['unit_price' => 100]);

        $response = $this->actingAs($admin)->postJson(route('admin.pos.checkout'), [
            'order_id' => $cart->order_id,
            'order_type' => 'dine_in',
            'dining_table_id' => $table->id,
            'payment_type' => 'cash',
            'paid_amount' => 100,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('sales', [
            'order_id' => $cart->order_id,
            'order_type' => OrderType::DINE_IN,
            'dining_table_id' => $table->id,
        ]);
        $this->assertSame(TableStatus::OCCUPIED, $table->fresh()->status);
    }

    public function test_counter_checkout_needs_no_table_and_touches_none(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct($admin);
        $table = $this->createTable($admin);
        $cart = $this->createCart($admin);
        $this->addCartItem($cart, $product, 1, ['unit_price' => 100]);

        // Even if a table id is sent, counter orders must not link or occupy it.
        $response = $this->actingAs($admin)->postJson(route('admin.pos.checkout'), [
            'order_id' => $cart->order_id,
            'order_type' => 'counter',
            'dining_table_id' => $table->id,
            'payment_type' => 'cash',
            'paid_amount' => 100,
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $sale = Sale::where('order_id', $cart->order_id)->firstOrFail();
        $this->assertTrue($sale->order_type === OrderType::COUNTER);
        $this->assertNull($sale->dining_table_id);
        $this->assertSame(TableStatus::FREE, $table->fresh()->status);
    }

    public function test_counter_hold_parks_without_table(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct($admin);
        $cart = $this->createCart($admin);
        $this->addCartItem($cart, $product, 1, ['unit_price' => 100]);

        $response = $this->actingAs($admin)->postJson(route('admin.pos.hold'), [
            'order_id' => $cart->order_id,
            'order_type' => 'counter',
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $this->assertDatabaseHas('sales', [
            'order_id' => $cart->order_id,
            'order_type' => OrderType::COUNTER,
            'is_hold' => 1,
        ]);
    }

    public function test_offline_counter_order_syncs_as_counter(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        $product = $this->createProduct($admin, ['selling_price' => 100, 'stock_in' => 50]);
        $clientOrderId = (string) Str::uuid();

        $response = $this->postJson(route('api.admin.pos.offline-sync'), [
            'orders' => [[
                'client_order_id' => $clientOrderId,
                'device_id' => (string) Str::uuid(),
                'channel' => 'counter',
                'customer_id' => null,
                'items' => [[
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price_snapshot' => 100,
                ]],
                'amounts' => [
                    'subtotal' => 100,
                    'discount' => 0,
                    'payable' => 100,
                    'paid' => 100,
                    'due' => 0,
                    'payment_type' => 'cash',
                ],
                'created_at_client' => now()->toISOString(),
                'schema_version' => 1,
            ]],
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('sales', [
            'client_order_id' => $clientOrderId,
            'order_type' => OrderType::COUNTER,
        ]);
    }
}
