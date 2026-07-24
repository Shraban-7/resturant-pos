<?php

namespace App\Http\Controllers;

use App\Actions\CreateKitchenTicketAction;
use App\Models\DeliveryOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnlineOrderController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected CreateKitchenTicketAction $createKitchenTicket
    ) {}

    public function index()
    {
        $categories = ProductCategory::with(['products' => fn ($q) => $q->where('is_active', true)->with('unit')])->get();
        return view('online-order.index', compact('categories'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'delivery_address' => 'required|string',
            'order_type' => 'required|in:pickup,delivery',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $saleItems = [];
            $sellerId = null;

            foreach ($request->items as $item) {
                $product = Product::with('unit')->findOrFail($item['id']);
                $sellerId = $product->seller_id;

                if (!$this->stockService->hasAvailableStock($product, $item['quantity'])) {
                    return errorResponse("Insufficient stock for item: {$product->name}");
                }

                $total = $product->selling_price * $item['quantity'];
                $subtotal += $total;

                $saleItems[] = [
                    'seller_id' => $sellerId,
                    'item_id' => $product->id,
                    'item_name' => $product->name,
                    'buying_price' => $product->buying_price,
                    'unit_price' => $product->selling_price,
                    'unit' => $product->unit?->short_name ?? 'pcs',
                    'quantity' => $item['quantity'],
                    'total_price' => $total,
                ];

                $this->stockService->deductStock($product, $item['quantity']);
            }

            $deliveryFee = $request->order_type === 'delivery' ? 50.00 : 0.00;
            $payable = $subtotal + $deliveryFee;

            $sale = Sale::create([
                'seller_id' => $sellerId,
                'order_id' => generateOrderId(),
                'sale_date' => now(),
                'subtotal' => $subtotal,
                'payable' => $payable,
                'paid' => 0,
                'due' => $payable,
                'payment_type' => 'cash_on_delivery',
                'status' => 'pending',
                'note' => "Online Order ({$request->order_type})",
            ]);

            foreach ($saleItems as $saleItem) {
                $sale->items()->create($saleItem);
            }

            if ($request->order_type === 'delivery') {
                DeliveryOrder::create([
                    'seller_id' => $sellerId,
                    'sale_id' => $sale->id,
                    'customer_phone' => $request->customer_phone,
                    'delivery_address' => $request->delivery_address,
                    'delivery_fee' => $deliveryFee,
                    'status' => 'pending',
                ]);
            }

            $sale->load(['items', 'table']);
            $this->createKitchenTicket->execute($sale);

            return successResponse('Online order placed successfully', [
                'order_id' => $sale->order_id,
            ]);
        });
    }
}
