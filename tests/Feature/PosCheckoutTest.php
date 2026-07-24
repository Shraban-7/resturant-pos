<?php

namespace Tests\Feature;

use App\Models\DiningTable;
use App\Models\KitchenTicket;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    public function test_checkout_creates_sale_items_and_kitchen_ticket(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller, ['selling_price' => 100, 'stock_in' => 50]);
        $table = $this->createTable($seller);
        $cart = $this->createCart($seller);
        $this->addCartItem($cart, $product, 2, ['unit_price' => 100]);

        $response = $this->actingAs($seller)->postJson(route('seller.pos.checkout'), [
            'order_id' => $cart->order_id,
            'payment_type' => 'cash',
            'paid_amount' => 200,
            'dining_table_id' => $table->id,
        ]);

        $response->assertOk()->assertJson(['status' => true]);

        $sale = Sale::where('order_id', $cart->order_id)->first();
        $this->assertNotNull($sale);
        $this->assertSame($seller->id, (int) $sale->seller_id);
        $this->assertEquals(200, (float) $sale->payable);
        $this->assertSame('cash', $sale->payment_option);
        $this->assertSame($table->id, (int) $sale->dining_table_id);

        $this->assertCount(1, $sale->items->all());
        $this->assertEquals(2, (float) $sale->items->first()->quantity);

        // Cart is consumed after checkout.
        $this->assertDatabaseMissing('carts', ['id' => $cart->id]);
        $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);

        // A kitchen ticket + line items are routed for the sale.
        $ticket = KitchenTicket::where('sale_id', $sale->id)->first();
        $this->assertNotNull($ticket);
        $this->assertSame(KitchenTicket::PENDING, $ticket->status);
        $this->assertSame($seller->id, (int) $ticket->seller_id);
        $this->assertCount(1, $ticket->items->all());

        // Table is locked to occupied.
        $this->assertSame(DiningTable::OCCUPIED, $table->fresh()->status);
    }

    public function test_checkout_fails_when_cart_is_empty(): void
    {
        $seller = $this->createSeller();
        $cart = $this->createCart($seller);

        $response = $this->actingAs($seller)->postJson(route('seller.pos.checkout'), [
            'order_id' => $cart->order_id,
            'payment_type' => 'cash',
            'paid_amount' => 0,
        ]);

        $response->assertStatus(400)->assertJson(['status' => false]);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_checkout_is_idempotent_for_repeated_client_order_id(): void
    {
        $seller = $this->createSeller();
        $product = $this->createProduct($seller);
        $clientOrderId = (string) Str::uuid();

        // Simulate an already-synced sale for this client order id.
        Sale::create([
            'seller_id' => $seller->id,
            'order_id' => generateOrderId(),
            'client_order_id' => $clientOrderId,
            'sale_date' => now()->toDateString(),
            'subtotal' => 100,
            'discount' => 0,
            'payable' => 100,
            'paid' => 100,
            'due' => 0,
            'payment_option' => 'cash',
        ]);

        $cart = $this->createCart($seller);
        $this->addCartItem($cart, $product, 1, ['unit_price' => 100]);

        $response = $this->actingAs($seller)->postJson(route('seller.pos.checkout'), [
            'order_id' => $cart->order_id,
            'payment_type' => 'cash',
            'paid_amount' => 100,
            'client_order_id' => $clientOrderId,
        ]);

        $response->assertOk()->assertJson(['status' => true]);

        // No duplicate sale created; the original cart is left untouched.
        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
    }

    public function test_checkout_requires_seller_authentication(): void
    {
        $response = $this->postJson(route('seller.pos.checkout'), [
            'order_id' => 'missing',
            'payment_type' => 'cash',
            'paid_amount' => 0,
        ]);

        // Unauthenticated JSON requests must not be treated as a valid checkout.
        $this->assertContains($response->status(), [401, 403, 422]);
        $this->assertDatabaseCount('sales', 0);
    }
}
