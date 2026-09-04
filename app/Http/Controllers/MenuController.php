<?php

namespace App\Http\Controllers;

use App\Actions\CreateKitchenTicketAction;
use App\Actions\DeductRecipeStockAction;
use App\Actions\ResolveProductModifiersAction;
use App\Http\Requests\PlaceQrOrderRequest;
use App\Models\DiningTable;
use App\Models\KitchenTicket;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class MenuController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected DeductRecipeStockAction $deductRecipeStock,
        protected CreateKitchenTicketAction $createKitchenTicket,
        protected ResolveProductModifiersAction $resolveModifiers,
    ) {
    }

    public function index(DiningTable $table)
    {
        $table->ensureQrToken();

        $categories = ProductCategory::query()
            ->where('seller_id', $table->seller_id)
            ->with(['products' => function ($q) use ($table) {
                $q->where('seller_id', $table->seller_id)
                    ->where('is_active', 1)
                    ->with([
                        'unit',
                        'modifiers' => fn ($mq) => $mq
                            ->where('modifiers.is_active', true)
                            ->orderBy('group_name')
                            ->orderBy('sort_order'),
                    ]);
            }])
            ->get();

        $productModifiersMap = [];
        foreach ($categories as $category) {
            foreach ($category->products as $product) {
                $productModifiersMap[$product->id] = $product->modifiers->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'group_name' => $m->group_name,
                    'price' => (float) $m->price,
                    'is_required' => (bool) ($m->pivot->is_required ?? false),
                ])->values()->all();
            }
        }

        $business = \App\Models\BusinessSetting::query()
            ->where('user_id', $table->seller_id)
            ->first();

        return view('digital-menu', compact('table', 'categories', 'productModifiersMap', 'business'));
    }

    public function placeOrder(PlaceQrOrderRequest $request, DiningTable $table)
    {
        try {
            return DB::transaction(function () use ($request, $table) {
                $table->ensureQrToken();

                $subtotal = 0;
                $saleItems = [];
                $deductions = [];

                foreach ($request->items as $item) {
                    $product = Product::query()
                        ->with(['unit', 'recipe.ingredients.ingredientProduct', 'modifiers'])
                        ->whereKey($item['id'])
                        ->where('seller_id', $table->seller_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (! $this->deductRecipeStock->usesRecipe($product)
                        && ! $this->stockService->hasAvailableStock($product, $item['quantity'])) {
                        throw new RuntimeException("Insufficient stock for item: {$product->name}");
                    }

                    $requestedIds = collect($item['modifiers'] ?? [])->all();
                    [$modifiersJson, $unitPrice] = $this->resolveModifiers->execute($product, $requestedIds);

                    $total = $unitPrice * $item['quantity'];
                    $subtotal += $total;

                    $saleItems[] = [
                        'seller_id' => $table->seller_id,
                        'item_id' => $product->id,
                        'item_name' => $product->name,
                        'buying_price' => $product->buying_price,
                        'unit_price' => $unitPrice,
                        'unit' => $product->unit ? $product->unit->short_name : 'pcs',
                        'quantity' => $item['quantity'],
                        'total_price' => $total,
                        'note' => $item['note'] ?? null,
                        'modifiers_json' => $modifiersJson ?: null,
                    ];

                    $deductions[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                    ];
                }

                foreach ($deductions as $deduction) {
                    $this->deductRecipeStock->execute($deduction['product'], $deduction['quantity']);
                }

                $sale = Sale::create([
                    'seller_id' => $table->seller_id,
                    'branch_id' => $table->branch_id,
                    'order_id' => generateOrderId(),
                    'sale_date' => now(),
                    'subtotal' => $subtotal,
                    'payable' => $subtotal,
                    'paid' => 0,
                    'due' => $subtotal,
                    'dining_table_id' => $table->id,
                ]);

                foreach ($saleItems as $saleItem) {
                    $sale->items()->create($saleItem);
                }

                DiningTable::query()
                    ->whereKey($table->id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->update(['status' => DiningTable::OCCUPIED]);

                $sale->load(['items', 'table']);
                $this->createKitchenTicket->execute($sale);

                return response()->json([
                    'status' => true,
                    'message' => 'Order placed successfully',
                    'order_id' => $sale->order_id,
                    'tracker_url' => route('menu.tracker', [
                        'token' => $table->fresh()->qr_code_token,
                        'order' => $sale->order_id,
                    ]),
                ]);
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function tracker(Request $request, string $token)
    {
        $table = DiningTable::query()
            ->where('qr_code_token', $token)
            ->firstOrFail();

        $orderId = $request->query('order');

        $saleQuery = Sale::query()
            ->where('dining_table_id', $table->id)
            ->with(['items', 'kitchenTickets.items'])
            ->latest('id');

        if ($orderId) {
            $sale = (clone $saleQuery)->where('order_id', $orderId)->first();
        } else {
            $sale = $saleQuery->first();
        }

        $ticket = $sale?->kitchenTickets->sortByDesc('id')->first();
        $status = $ticket?->status ?? ($sale ? KitchenTicket::PENDING : null);

        $steps = [
            ['key' => 'received', 'label' => 'Order Received', 'statuses' => [KitchenTicket::PENDING]],
            ['key' => 'preparing', 'label' => 'In Kitchen', 'statuses' => [KitchenTicket::PREPARING]],
            ['key' => 'ready', 'label' => 'Food Ready', 'statuses' => [KitchenTicket::READY]],
            ['key' => 'served', 'label' => 'Served', 'statuses' => [KitchenTicket::SERVED]],
        ];

        return view('order-status', [
            'table' => $table,
            'sale' => $sale,
            'ticket' => $ticket,
            'status' => $status,
            'steps' => $steps,
            'token' => $token,
        ]);
    }
}
