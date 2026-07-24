<?php

namespace App\Http\Controllers\Seller;

use App\Actions\CreateKitchenTicketAction;
use App\Actions\DeductRecipeStockAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\CheckoutPosRequest;
use App\Http\Requests\Seller\PosAddItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SellerEmployee;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use RuntimeException;

class PosController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected DeductRecipeStockAction $deductRecipeStock,
        protected CreateKitchenTicketAction $createKitchenTicket
    ) {}

    public function index(Request $request)
    {
        $products = Product::self()
            ->with([
                'category',
                'unit',
                'modifiers' => fn ($q) => $q->where('modifiers.is_active', true)->orderBy('group_name')->orderBy('sort_order'),
            ])
            ->latest('id')
            ->get();
        $customers = Customer::self()->get();

        $recentSales = Sale::self()
            ->with(['customer', 'table', 'waiter'])
            ->where('is_hold', 0)
            ->latest('id')
            ->limit(5)
            ->get();
        $runningSales = Sale::self()
            ->with(['customer', 'table', 'waiter'])
            ->where('is_hold', 1)
            ->latest('id')
            ->limit(5)
            ->get();

        $cart = Cart::query()->firstOrCreate(
            ['seller_id' => auth()->id()],
            ['order_id' => generateOrderId()]
        );
        $cart->load(['items.item.unit']);

        $categories = ProductCategory::query()
            ->where('seller_id', auth()->id())
            ->withCount(['products' => fn ($q) => $q->where('seller_id', auth()->id())])
            ->get();
        $diningTables = DiningTable::self()->get();
        $employees = SellerEmployee::self()->get();
        $cartItems = $cart->items;
        $saleItems = null;
        $sale = null;

        if ($request->has('sale')) {
            $sale = Sale::query()
                ->where('order_id', request('sale'))
                ->where('seller_id', auth()->id())
                ->with(['items.product.unit', 'customer', 'table', 'waiter'])
                ->first();
            if ($sale) {
                $saleItems = $sale->items;
                $saleItems = $saleItems->merge($cartItems);
            }
        }

        $productModifiersMap = $products->mapWithKeys(function (Product $product) {
            return [
                $product->id => $product->modifiers->map(fn ($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'group_name' => $m->group_name,
                    'price' => (float) $m->price,
                    'is_required' => (bool) $m->pivot->is_required,
                ])->values(),
            ];
        });

        $recipeProductIds = Product::self()
            ->whereHas('recipe.ingredients')
            ->pluck('id');

        return view('seller.pos', compact(
            'products',
            'cart',
            'customers',
            'recentSales',
            'runningSales',
            'categories',
            'diningTables',
            'employees',
            'sale',
            'saleItems',
            'productModifiersMap',
            'recipeProductIds'
        ));
    }

    public function addItem(PosAddItemRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $product = Product::query()
                    ->with(['recipe.ingredients.ingredientProduct'])
                    ->whereKey($request->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $cart = Cart::where('order_id', $request->order_id)
                    ->where('seller_id', auth()->id())
                    ->firstOrFail();

                $modifiers = collect($request->input('modifiers', []))
                    ->map(fn ($m) => [
                        'id' => (int) ($m['id'] ?? 0),
                        'name' => $m['name'] ?? '',
                        'group_name' => $m['group_name'] ?? '',
                        'price' => (float) ($m['price'] ?? 0),
                    ])
                    ->filter(fn ($m) => $m['id'] > 0)
                    ->values()
                    ->all();

                $modifierTotal = collect($modifiers)->sum('price');
                $unitPrice = (float) $request->unit_price;
                $lineUnit = $unitPrice + $modifierTotal;
                $qty = (float) $request->quantity;
                $discount = (float) $request->discount;
                $totalPrice = ($qty * $lineUnit) - $discount;

                // Availability: finished goods when no recipe; otherwise ingredients checked inside action.
                if (! $this->deductRecipeStock->usesRecipe($product)
                    && ! $this->stockService->hasAvailableStock($product, $qty)) {
                    throw new RuntimeException('Insufficient stock!');
                }

                CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $product->id,
                    'unit_price' => $lineUnit,
                    'discount' => $discount,
                    'quantity' => $qty,
                    'total_price' => $totalPrice,
                    'note' => $request->input('note'),
                    'modifiers_json' => $modifiers ?: null,
                ]);

                $this->deductRecipeStock->execute($product, $qty);

                $cart_items = CartItem::where('cart_id', $cart->id)->with('item')->get();
                $itemHtml = '';

                foreach ($cart_items as $item) {
                    $itemHtml .= View::make('components.pos.cart-item', ['item' => $item])->render();
                }

                $product->refresh();

                return apiResponse([
                    'item' => [
                        'id' => $product->id,
                        'stock' => $this->stockService->availableQuantity($product),
                    ],
                    'cart_item_html' => $itemHtml,
                ], 'Item added successfully');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function removeItem(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $cart_item = CartItem::whereHas('cart', function ($q) {
                $q->where('seller_id', auth()->id());
            })->with(['item.recipe.ingredients.ingredientProduct'])->findOrFail($request->cart_item_id);

            $item = $cart_item->item;
            $this->deductRecipeStock->restore($item, $cart_item->quantity);

            $response = [
                'item' => [
                    'id' => $item->id,
                    'stock' => $this->stockService->availableQuantity($item->fresh()),
                ],
            ];

            $cart_item->delete();

            return apiResponse($response, 'Item removed successfully');
        });
    }

    public function updateQuantity(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $cart_item = CartItem::whereHas('cart', function ($q) {
                $q->where('seller_id', auth()->id());
            })->with(['item.recipe.ingredients.ingredientProduct'])->findOrFail($request->cart_item_id);

            $item = $cart_item->item;
            $quantity = (float) $request->quantity;

            if ($quantity > $cart_item->quantity) {
                $diff = $quantity - $cart_item->quantity;
                $this->deductRecipeStock->execute($item, $diff);
            } elseif ($quantity < $cart_item->quantity) {
                $diff = $cart_item->quantity - $quantity;
                $this->deductRecipeStock->restore($item, $diff);
            }

            $cart_item->quantity = $quantity;
            $cart_item->total_price = ($cart_item->unit_price * $quantity) - ($cart_item->discount ?? 0);
            $cart_item->save();

            $response = [
                'item' => ['id' => $item->id, 'stock' => $this->stockService->availableQuantity($item->fresh())],
                'cart_item' => ['total_price' => $cart_item->total_price],
            ];

            return apiResponse($response, 'Cart updated successfully');
        });
    }

    public function checkout(CheckoutPosRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $customer_id = $request->customer_id ?: null;
                $customer_name = $request->customer_name ?? '';
                $customer_phone = $request->customer_phone ?? '';

                if ($customer_name != '' && $customer_phone != '') {
                    $newCustomer = Customer::create([
                        'seller_id' => auth()->id(),
                        'name' => $customer_name,
                        'phone' => $customer_phone,
                    ]);
                    $customer_id = $newCustomer->id;
                }

                $cart = Cart::where('order_id', $request->order_id)
                    ->where('seller_id', auth()->id())
                    ->with('items.item.unit')
                    ->lockForUpdate()
                    ->first();

                if (! $cart || count($cart->items) == 0) {
                    throw new RuntimeException('No items added!');
                }

                $subTotal = 0;
                $saleItems = [];

                foreach ($cart->items as $item) {
                    $subTotal += $item->total_price;
                    $saleItems[] = [
                        'seller_id' => $cart->seller_id,
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->name,
                        'buying_price' => $item->item->buying_price,
                        'unit_price' => $item->unit_price,
                        'unit' => $item->item->unit->short_name,
                        'quantity' => $item->quantity,
                        'total_price' => $item->total_price,
                        'note' => $item->note,
                        'modifiers_json' => $item->modifiers_json,
                    ];
                }

                $discount = $request->discount_amount ?? 0;
                $paid = $request->paid_amount ?? 0;
                $payable = ($subTotal - $discount);

                // Prefer request aliases used by POS UI (table_id / employee_id) with dining_* fallbacks.
                $tableId = $request->dining_table_id ?? $request->table_id;
                $employeeId = $request->seller_employee_id ?? $request->employee_id;

                if ($request->client_order_id) {
                    $existingSale = Sale::query()
                        ->where('seller_id', $cart->seller_id)
                        ->where('client_order_id', $request->client_order_id)
                        ->first();

                    if ($existingSale) {
                        return successResponse('Sale already completed');
                    }
                }

                $saleData = [
                    'seller_id' => $cart->seller_id,
                    'customer_id' => $customer_id,
                    'order_id' => $cart->order_id,
                    'client_order_id' => $request->client_order_id,
                    'device_id' => $request->device_id,
                    'created_at_client' => $request->created_at_client,
                    'synced_at' => $request->client_order_id ? now() : null,
                    'sale_date' => date('Y-m-d'),
                    'subtotal' => $subTotal,
                    'discount' => $discount,
                    'payable' => $payable,
                    'paid' => $paid,
                    'due' => ($payable - $paid),
                    'payment_option' => $request->payment_type ?? 'cash',
                    'note' => $request->note,
                ];

                if ($tableId) {
                    $saleData['dining_table_id'] = $tableId;
                }
                if ($employeeId) {
                    $saleData['seller_employee_id'] = $employeeId;
                }

                $sale = Sale::create($saleData);

                foreach ($saleItems as $saleItem) {
                    $sale->items()->create($saleItem);
                }

                $cart->items()->delete();
                $cart->delete();

                if ($tableId) {
                    $table = DiningTable::self()->where('id', $tableId)->lockForUpdate()->first();
                    if ($table) {
                        $table->update(['status' => DiningTable::OCCUPIED]);
                    }
                }

                $sale->load(['items', 'table']);
                $this->createKitchenTicket->execute($sale);

                return successResponse('Sale complete');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function holdOrder(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $cart = Cart::where('order_id', $request->order_id)
                ->where('seller_id', auth()->id())
                ->with('items.item.unit')
                ->first();

            if (! $cart || count($cart->items) == 0) {
                return errorResponse('No items added!');
            }

            $subTotal = 0;
            $saleItems = [];

            foreach ($cart->items as $item) {
                $subTotal += $item->total_price;
                $saleItems[] = [
                    'seller_id' => $cart->seller_id,
                    'item_id' => $item->item_id,
                    'item_name' => $item->item->name,
                    'buying_price' => $item->item->buying_price,
                    'unit_price' => $item->unit_price,
                    'unit' => $item->item->unit->short_name,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                    'note' => $item->note,
                    'modifiers_json' => $item->modifiers_json,
                ];
            }

            $payable = $subTotal;

            $saleData = [
                'seller_id' => $cart->seller_id,
                'is_hold' => 1,
                'order_id' => $cart->order_id,
                'sale_date' => date('Y-m-d'),
                'subtotal' => $subTotal,
                'discount' => 0,
                'payable' => $payable,
                'paid' => 0,
                'due' => $payable,
                'note' => $request->note,
            ];

            $sale = Sale::create($saleData);

            foreach ($saleItems as $saleItem) {
                $sale->items()->create($saleItem);
            }

            $cart->items()->delete();
            $cart->delete();

            $sale->load(['items', 'table']);
            $this->createKitchenTicket->execute($sale);

            return successResponse('Sale held successfully');
        });
    }
}
