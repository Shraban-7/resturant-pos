<?php

namespace Tests\Feature;

use App\Actions\CreateKitchenTicketAction;
use App\Models\KitchenTicket;
use App\Models\KitchenTicketItem;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class KdsTicketTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    private function makeSaleWithTicket(\App\Models\User $seller): KitchenTicket
    {
        $product = $this->createProduct($seller);
        $table = $this->createTable($seller);

        $sale = Sale::create([
            'seller_id' => $seller->id,
            'dining_table_id' => $table->id,
            'order_id' => generateOrderId(),
            'sale_date' => now()->toDateString(),
            'subtotal' => 100,
            'discount' => 0,
            'payable' => 100,
            'paid' => 100,
            'due' => 0,
            'payment_option' => 'cash',
        ]);

        $sale->items()->create([
            'seller_id' => $seller->id,
            'item_id' => $product->id,
            'item_name' => $product->name,
            'buying_price' => $product->buying_price,
            'unit_price' => 100,
            'unit' => 'pcs',
            'quantity' => 2,
            'total_price' => 200,
        ]);

        return app(CreateKitchenTicketAction::class)->execute($sale->fresh(['items', 'table']));
    }

    public function test_action_creates_ticket_with_line_items_from_sale(): void
    {
        $seller = $this->createSeller();
        $ticket = $this->makeSaleWithTicket($seller);

        $this->assertInstanceOf(KitchenTicket::class, $ticket);
        $this->assertSame(KitchenTicket::PENDING, $ticket->status);
        $this->assertSame($seller->id, (int) $ticket->seller_id);
        $this->assertCount(1, $ticket->items);
        $this->assertSame(KitchenTicketItem::PENDING, $ticket->items->first()->status);
        $this->assertNotNull($ticket->fired_at);
    }

    public function test_seller_can_advance_ticket_to_preparing(): void
    {
        $seller = $this->createSeller();
        $ticket = $this->makeSaleWithTicket($seller);

        $response = $this->actingAs($seller)->postJson(
            route('seller.kds.updateStatus', $ticket),
            ['status' => KitchenTicket::PREPARING]
        );

        $response->assertOk()->assertJson(['status' => true]);
        $ticket->refresh();
        $this->assertSame(KitchenTicket::PREPARING, $ticket->status);
        $this->assertNotNull($ticket->fired_at);
        $this->assertSame(
            KitchenTicketItem::PREPARING,
            $ticket->items()->first()->status
        );
    }

    public function test_marking_ready_sets_prepared_at_and_item_statuses(): void
    {
        $seller = $this->createSeller();
        $ticket = $this->makeSaleWithTicket($seller);

        $response = $this->actingAs($seller)->postJson(
            route('seller.kds.updateStatus', $ticket),
            ['status' => KitchenTicket::READY]
        );

        $response->assertOk();
        $ticket->refresh();
        $this->assertSame(KitchenTicket::READY, $ticket->status);
        $this->assertNotNull($ticket->prepared_at);
        $this->assertSame(KitchenTicketItem::READY, $ticket->items()->first()->status);
    }

    public function test_seller_cannot_update_another_sellers_ticket(): void
    {
        $owner = $this->createSeller();
        $ticket = $this->makeSaleWithTicket($owner);
        $intruder = $this->createSeller();

        $response = $this->actingAs($intruder)->postJson(
            route('seller.kds.updateStatus', $ticket),
            ['status' => KitchenTicket::READY]
        );

        $response->assertStatus(403);
        $this->assertSame(KitchenTicket::PENDING, $ticket->fresh()->status);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $seller = $this->createSeller();
        $ticket = $this->makeSaleWithTicket($seller);

        $response = $this->actingAs($seller)->postJson(
            route('seller.kds.updateStatus', $ticket),
            ['status' => 'flying']
        );

        $response->assertStatus(422);
    }
}
