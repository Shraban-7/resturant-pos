<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlaceQrOrderRequest;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class MenuController extends Controller
{
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(DiningTable $table)
    {
        $categories = ProductCategory::query()
            ->where('seller_id', $table->seller_id)
            ->with(['products' => function ($q) use ($table) {
                $q->where('seller_id', $table->seller_id)
                    ->where('is_active', 1)
                    ->with('unit');
            }])
            ->get();

        return view('digital-menu', compact('table', 'categories'));
    }

    public function placeOrder(PlaceQrOrderRequest $request, DiningTable $table)
    {
        try {
            return DB::transaction(function () use ($request, $table) {
                $subtotal = 0;
                $saleItems = [];
                $deductions = [];

                // Validate & lock all products before mutating stock or creating the sale.
                foreach ($request->items as $item) {
                    $product = Product::query()
                        ->with('unit')
                        ->whereKey($item['id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (!$this->stockService->hasAvailableStock($product, $item['quantity'])) {
                        throw new RuntimeException("Insufficient stock for item: {$product->name}");
                    }

                    $total = $product->selling_price * $item['quantity'];
                    $subtotal += $total;

                    $saleItems[] = [
                        'seller_id' => $table->seller_id,
                        'item_id' => $product->id,
                        'item_name' => $product->name,
                        'buying_price' => $product->buying_price,
                        'unit_price' => $product->selling_price,
                        'unit' => $product->unit ? $product->unit->short_name : 'pcs',
                        'quantity' => $item['quantity'],
                        'total_price' => $total,
                    ];

                    $deductions[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                    ];
                }

                foreach ($deductions as $deduction) {
                    $this->stockService->deductStock($deduction['product'], $deduction['quantity']);
                }

                $sale = Sale::create([
                    'seller_id' => $table->seller_id,
                    'order_id' => generateOrderId(),
                    'sale_date' => now(),
                    'subtotal' => $subtotal,
                    'payable' => $subtotal,
                    'paid' => 0,
                    'due' => $subtotal,
                    'dining_table_id' => $table->id,
                    'status' => 'pending',
                ]);

                foreach ($saleItems as $saleItem) {
                    $sale->items()->create($saleItem);
                }

                DiningTable::query()
                    ->whereKey($table->id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->update(['status' => DiningTable::OCCUPIED]);

                return response()->json([
                    'status' => true,
                    'message' => 'Order placed successfully',
                    'order_id' => $sale->order_id,
                ]);
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }
}
