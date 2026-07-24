<?php

namespace Tests\Feature;

use App\Models\DiningTable;
use App\Models\KitchenTicket;
use App\Models\Modifier;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class QrOrderTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    public function test_guest_can_place_qr_order_and_table_is_locked(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller, ['selling_price' => 120, 'stock_in' => 10]);
        $table = $this->createTable($seller);

        $response = $this->postJson(route('menu.placeOrder', $table), [
            'items' => [
                ['id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertOk()->assertJson(['status' => true]);
        $response->assertJsonStructure(['order_id', 'tracker_url']);

        $sale = Sale::where('dining_table_id', $table->id)->first();
        $this->assertNotNull($sale);
        $this->assertEquals(240, (float) $sale->subtotal);
        $this->assertCount(1, $sale->items->all());

        // Stock is deducted on commit and the table becomes occupied.
        $this->assertSame(2, (int) $product->fresh()->stock_out);
        $this->assertSame(DiningTable::OCCUPIED, $table->fresh()->status);

        // A kitchen ticket is generated for the kitchen display.
        $this->assertDatabaseHas('kitchen_tickets', [
            'sale_id' => $sale->id,
            'seller_id' => $seller->id,
            'status' => KitchenTicket::PENDING,
        ]);
    }

    public function test_qr_order_applies_selected_modifier_prices(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller, ['selling_price' => 100, 'stock_in' => 10]);
        $table = $this->createTable($seller);

        $modifier = Modifier::create([
            'seller_id' => $seller->id,
            'group_name' => 'Extras',
            'name' => 'Extra Cheese',
            'price' => 20,
            'is_active' => true,
        ]);
        $product->modifiers()->attach($modifier->id, ['is_required' => false, 'max_select' => 1]);

        $response = $this->postJson(route('menu.placeOrder', $table), [
            'items' => [
                [
                    'id' => $product->id,
                    'quantity' => 1,
                    'modifiers' => [
                        ['id' => $modifier->id, 'name' => 'Extra Cheese', 'price' => 20, 'group_name' => 'Extras'],
                    ],
                ],
            ],
        ]);

        $response->assertOk()->assertJson(['status' => true]);

        $sale = Sale::where('dining_table_id', $table->id)->first();
        $item = $sale->items->first();
        // 100 base + 20 modifier.
        $this->assertEquals(120, (float) $item->unit_price);
        $this->assertNotEmpty($item->modifiers_json);
        $this->assertSame('Extra Cheese', $item->modifiers_json[0]['name']);
    }

    public function test_qr_order_rejects_quantity_exceeding_stock(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller, ['selling_price' => 100, 'stock_in' => 3]);
        $table = $this->createTable($seller);

        $response = $this->postJson(route('menu.placeOrder', $table), [
            'items' => [
                ['id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(400)->assertJson(['status' => false]);

        // Nothing is persisted and the table stays free when the order is rejected.
        $this->assertDatabaseCount('sales', 0);
        $this->assertSame(0, (int) $product->fresh()->stock_out);
        $this->assertSame(DiningTable::FREE, $table->fresh()->status);
    }

    public function test_qr_order_requires_at_least_one_item(): void
    {
        $seller = $this->createSeller();
        $table = $this->createTable($seller);

        $response = $this->postJson(route('menu.placeOrder', $table), [
            'items' => [],
        ]);

        $response->assertStatus(422);
    }
}
