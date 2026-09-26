<?php

namespace Tests\Feature;

use App\Models\BusinessSetting;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesPosData;
use Tests\TestCase;

class PosVatTest extends TestCase
{
    use RefreshDatabase, CreatesPosData;

    private function checkoutWithVat(?array $vat, float $sellingPrice = 100): Sale
    {
        $admin = $this->createAdmin();

        if ($vat !== null) {
            BusinessSetting::create(['user_id' => $admin->id] + $vat);
        }

        $product = $this->createProduct($admin, ['selling_price' => $sellingPrice]);
        $cart = $this->createCart($admin);
        $this->addCartItem($cart, $product, 1, ['unit_price' => $sellingPrice]);

        $this->actingAs($admin)->postJson(route('admin.pos.checkout'), [
            'order_id' => $cart->order_id,
            'order_type' => 'counter',
            'payment_type' => 'cash',
            'paid_amount' => 1000,
        ])->assertOk()->assertJson(['status' => true]);

        return Sale::where('order_id', $cart->order_id)->firstOrFail();
    }

    public function test_disabled_vat_by_default(): void
    {
        $sale = $this->checkoutWithVat(null);

        $this->assertSame('disabled', $sale->vat_mode);
        $this->assertEquals(0, $sale->vat_amount);
        $this->assertEquals(100, $sale->payable);
    }

    public function test_exclusive_vat_added_on_top(): void
    {
        $sale = $this->checkoutWithVat(['vat_enabled' => true, 'vat_rate' => 10, 'vat_mode' => 'exclusive']);

        $this->assertSame('exclusive', $sale->vat_mode);
        $this->assertEquals(10, $sale->vat_rate);
        $this->assertEquals(10, $sale->vat_amount);
        $this->assertEquals(110, $sale->payable);
    }

    public function test_inclusive_vat_extracted_from_price(): void
    {
        $sale = $this->checkoutWithVat(['vat_enabled' => true, 'vat_rate' => 10, 'vat_mode' => 'inclusive']);

        $this->assertSame('inclusive', $sale->vat_mode);
        $this->assertEquals(9.09, $sale->vat_amount);
        $this->assertEquals(100, $sale->payable);
    }

    public function test_pos_page_exposes_vat_row_and_config(): void
    {
        $admin = $this->createAdmin();
        BusinessSetting::create(['user_id' => $admin->id, 'vat_enabled' => true, 'vat_rate' => 5, 'vat_mode' => 'exclusive']);
        $this->createProduct($admin);

        $response = $this->actingAs($admin)->get(route('admin.pos.index'))->assertOk();
        $response->assertSee('vatRow', false);
        $response->assertSee('vatAmount', false);
        $response->assertSee('"mode":"exclusive"', false);
        $response->assertSee('"rate":5', false);
    }

    public function test_hold_order_freezes_vat(): void
    {
        $admin = $this->createAdmin();
        BusinessSetting::create(['user_id' => $admin->id, 'vat_enabled' => true, 'vat_rate' => 5, 'vat_mode' => 'exclusive']);

        $product = $this->createProduct($admin, ['selling_price' => 200]);
        $cart = $this->createCart($admin);
        $this->addCartItem($cart, $product, 1, ['unit_price' => 200]);

        $this->actingAs($admin)->postJson(route('admin.pos.hold'), [
            'order_id' => $cart->order_id,
            'order_type' => 'counter',
        ])->assertOk()->assertJson(['status' => true]);

        $sale = Sale::where('order_id', $cart->order_id)->firstOrFail();
        $this->assertEquals(10, $sale->vat_amount);
        $this->assertEquals(210, $sale->payable);
    }
}
