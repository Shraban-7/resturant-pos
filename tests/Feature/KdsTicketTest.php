<?php

namespace Tests\Feature;

use App\Enums\KitchenStatus;

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

    private function makeSaleWithTicket(\App\Models\User $admin): KitchenTicket
    {
        $product = $this->createProduct($admin);
        $table = $this->createTable($admin);

        $sale = Sale::create([
            'admin_id' => $admin->id,
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
            'admin_id' => $admin->id,
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
        $admin = $this->createAdmin();
        $ticket = $this->makeSaleWithTicket($admin);

        $this->assertInstanceOf(KitchenTicket::class, $ticket);
        $this->assertSame(KitchenStatus::PENDING, $ticket->status);
        $this->assertSame($admin->id, (int) $ticket->admin_id);
        $this->assertCount(1, $ticket->items);
        $this->assertSame(KitchenStatus::PENDING, $ticket->items->first()->status);
        $this->assertNotNull($ticket->fired_at);
    }

    public function test_admin_can_advance_ticket_to_preparing(): void
    {
        $admin = $this->createAdmin();
        $ticket = $this->makeSaleWithTicket($admin);

        $response = $this->actingAs($admin)->postJson(
            route('admin.kds.updateStatus', $ticket),
            ['status' => KitchenStatus::PREPARING]
        );

        $response->assertOk()->assertJson(['status' => true]);
        $ticket->refresh();
        $this->assertSame(KitchenStatus::PREPARING, $ticket->status);
        $this->assertNotNull($ticket->fired_at);
        $this->assertSame(
            KitchenStatus::PREPARING,
            $ticket->items()->first()->status
        );
    }

    public function test_marking_ready_sets_prepared_at_and_item_statuses(): void
    {
        $admin = $this->createAdmin();
        $ticket = $this->makeSaleWithTicket($admin);

        $response = $this->actingAs($admin)->postJson(
            route('admin.kds.updateStatus', $ticket),
            ['status' => KitchenStatus::READY]
        );

        $response->assertOk();
        $ticket->refresh();
        $this->assertSame(KitchenStatus::READY, $ticket->status);
        $this->assertNotNull($ticket->prepared_at);
        $this->assertSame(KitchenStatus::READY, $ticket->items()->first()->status);
    }

    public function test_admins_share_single_store_dataset(): void
    {
        // Single restaurant = single dataset: every admin resolves to the
        // canonical (first) admin, so a second admin sees the same tickets.
        $owner = $this->createAdmin();
        $ticket = $this->makeSaleWithTicket($owner);
        $coAdmin = $this->createAdmin();

        $response = $this->actingAs($coAdmin)->postJson(
            route('admin.kds.updateStatus', $ticket),
            ['status' => KitchenStatus::READY]
        );

        $response->assertOk();
        $this->assertSame(KitchenStatus::READY, $ticket->fresh()->status);
    }

    public function test_update_status_rejects_invalid_status(): void
    {
        $admin = $this->createAdmin();
        $ticket = $this->makeSaleWithTicket($admin);

        $response = $this->actingAs($admin)->postJson(
            route('admin.kds.updateStatus', $ticket),
            ['status' => 'flying']
        );

        $response->assertStatus(422);
    }
}






