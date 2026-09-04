<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $service;

    private int $unitId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StockService();
        $this->unitId = ProductUnit::create(['name' => 'Piece', 'short_name' => 'pcs'])->id;
    }

    private function product(int $stockIn = 100, int $stockOut = 0): Product
    {
        return Product::create([
            'admin_id' => 1,
            'category_id' => 1,
            'unit_id' => $this->unitId,
            'name' => 'Test Product',
            'image' => '',
            'buying_price' => 50,
            'selling_price' => 80,
            'stock_in' => $stockIn,
            'stock_out' => $stockOut,
            'is_active' => 1,
        ]);
    }

    public function test_available_quantity_is_stock_in_minus_stock_out(): void
    {
        $product = $this->product(stockIn: 100, stockOut: 30);

        $this->assertSame(70.0, $this->service->availableQuantity($product));
    }

    public function test_has_available_stock_returns_true_when_enough(): void
    {
        $product = $this->product(stockIn: 10, stockOut: 0);

        $this->assertTrue($this->service->hasAvailableStock($product, 10));
        $this->assertTrue($this->service->hasAvailableStock($product, 5));
    }

    public function test_has_available_stock_returns_false_when_insufficient(): void
    {
        $product = $this->product(stockIn: 5, stockOut: 0);

        $this->assertFalse($this->service->hasAvailableStock($product, 6));
    }

    public function test_has_available_stock_returns_false_for_non_positive_quantity(): void
    {
        $product = $this->product();

        $this->assertFalse($this->service->hasAvailableStock($product, 0));
        $this->assertFalse($this->service->hasAvailableStock($product, -3));
    }

    public function test_deduct_stock_increments_stock_out(): void
    {
        $product = $this->product(stockIn: 100, stockOut: 0);

        $returned = $this->service->deductStock($product, 25);

        $this->assertSame(25, (int) $returned->stock_out);
        $this->assertSame(75.0, $this->service->availableQuantity($product->fresh()));
    }

    public function test_deduct_stock_throws_when_quantity_exceeds_available(): void
    {
        $product = $this->product(stockIn: 10, stockOut: 0);

        $this->expectException(InvalidArgumentException::class);
        $this->service->deductStock($product, 11);
    }

    public function test_deduct_stock_throws_for_non_positive_quantity(): void
    {
        $product = $this->product();

        $this->expectException(InvalidArgumentException::class);
        $this->service->deductStock($product, 0);
    }

    public function test_restore_stock_decrements_stock_out(): void
    {
        $product = $this->product(stockIn: 100, stockOut: 40);

        $this->service->restoreStock($product, 15);

        $this->assertSame(25, (int) $product->fresh()->stock_out);
    }

    public function test_restore_stock_never_goes_below_zero(): void
    {
        $product = $this->product(stockIn: 100, stockOut: 10);

        $this->service->restoreStock($product, 50);

        $this->assertSame(0, (int) $product->fresh()->stock_out);
    }

    public function test_restore_stock_is_noop_for_non_positive_quantity(): void
    {
        $product = $this->product(stockIn: 100, stockOut: 20);

        $this->service->restoreStock($product, 0);

        $this->assertSame(20, (int) $product->fresh()->stock_out);
    }
}

