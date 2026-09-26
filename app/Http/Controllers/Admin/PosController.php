<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;

use App\Actions\CreateKitchenTicketAction;
use App\Actions\DeductRecipeStockAction;
use App\Actions\ResolveProductAdditionsAction;
use App\Actions\ResolveProductModifiersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CheckoutPosRequest;
use App\Http\Requests\Admin\PosAddItemRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Employee;
use App\Models\GiftCard;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Services\StockService;
use Carbon\Carbon;
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
        protected CreateKitchenTicketAction $createKitchenTicket,
        protected ResolveProductModifiersAction $resolveModifiers,
        protected ResolveProductAdditionsAction $resolveAdditions,
    ) {}

    public function index(Request $request)
    {
        $products = Product::self()
            ->sellable()
            ->with([
                'category',
                'unit',
                'recipe.ingredients.ingredientProduct',
                'addons:id,name,selling_price,is_active',
                'extras:id,name,price,is_active',
                'modifiers' => fn ($q) => $q->where('modifiers.is_active', true)->orderBy('group_name')->orderBy('sort_order'),
            ])
            ->latest('id')
            ->get();
        $customers = Customer::self()->get();

        $recentSales = Sale::self()
            ->forActiveBranch()
            ->with(['customer', 'table', 'waiter'])
            ->where('is_hold', 0)
            ->latest('id')
            ->limit(5)
            ->get();
        $runningSales = Sale::self()
            ->forActiveBranch()
            ->with(['customer', 'table', 'waiter'])
            ->where('is_hold', 1)
            ->latest('id')
            ->limit(5)
            ->get();

        $cart = Cart::query()->firstOrCreate(
            ['admin_id' => panel_owner_id()],
            ['order_id' => generateOrderId()]
        );
        $cart->load(['items.item.unit']);

        $categories = ProductCategory::query()
            ->where('admin_id', panel_owner_id())
            ->withCount(['products' => fn ($q) => $q->where('admin_id', panel_owner_id())])
            ->get();
        $diningTables = DiningTable::self()->forActiveBranch()->with('floor')->get();
        $employees = Employee::self()->forActiveBranch()->get();
        $branches = admin_branches();
        $activeBranch = active_branch();
        $cartItems = $cart->items;
        $saleItems = null;
        $sale = null;

        if ($request->has('sale')) {
            $sale = Sale::query()
                ->where('order_id', request('sale'))
                ->where('admin_id', panel_owner_id())
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

        $productAddonsMap = $products->mapWithKeys(function (Product $product) {            return [
                $product->id => $product->addons
                    ->filter(fn ($a) => (bool) $a->is_active)
                    ->map(fn ($a) => [
                        'id' => $a->id,
                        'name' => $a->name,
                        'price' => (float) $a->selling_price,
                    ])->values(),
            ];
        });

        $recipeProductIds = Product::self()
            ->whereHas('recipe.ingredients')
            ->pluck('id');

        // Pure menu: servings left per recipe, null = always available.
        foreach ($products as $product) {
            $servings = $this->deductRecipeStock->availableServings($product);
            $product->setAttribute('pos_unlimited', $servings === null);
            $product->setAttribute('pos_available', $servings ?? 0);
        }

        // Per-dish extras map for badge-select in the modal.
        $productExtrasMap = $products->mapWithKeys(function (Product $product) {
            return [
                $product->id => $product->extras
                    ->filter(fn ($a) => (bool) $a->is_active)
                    ->map(fn ($a) => [
                        'id' => $a->id,
                        'name' => $a->name,
                        'price' => (float) $a->price,
                    ])->values(),
            ];
        });

        $offlineProducts = $products->map(fn ($product) => [
            'product_id' => $product->id,
            'name' => $product->name,
            'selling_price' => (float) $product->selling_price,
            'buying_price' => (float) $product->buying_price,
            'available_stock' => (float) $product->pos_available,
            'unlimited' => (bool) $product->pos_unlimited,
            'category_id' => $product->product_category_id ?? $product->category_id,
            'unit' => $product->unit?->short_name,
            'image' => $product->image,
            'image_url' => $product->image_url,
            'active' => (bool) $product->is_active,
            'modifiers' => $productModifiersMap[$product->id] ?? [],
        ])->values();

        $offlineCategories = $categories->map(fn ($category) => [
            'category_id' => $category->id,
            'name' => $category->name,
        ])->values();

        $offlineTables = $diningTables->map(fn ($table) => [
            'table_id' => $table->id,
            'name' => $table->name,
            'status' => $table->status,
            'floor_id' => $table->floor_id,
        ])->values();

        $offlineFloors = $diningTables->pluck('floor')->filter()->unique('id')->map(fn ($floor) => [
            'floor_id' => $floor->id,
            'name' => $floor->name,
        ])->values();

        $offlineCustomers = $customers->take(100)->map(fn ($customer) => [
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
        ])->values();

        return view('admin.pos', compact(
            'products',
            'cart',
            'customers',
            'recentSales',
            'runningSales',
            'categories',
            'diningTables',
            'employees',
            'branches',
            'activeBranch',
            'sale',
            'saleItems',
            'productModifiersMap',
            'productAddonsMap',
            'productExtrasMap',
            'recipeProductIds',
            'offlineProducts',
            'offlineCategories',
            'offlineTables',
            'offlineFloors',
            'offlineCustomers'
        ));
    }

    public function addItem(PosAddItemRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $product = Product::query()
                    ->with(['recipe.ingredients.ingredientProduct', 'modifiers'])
                    ->whereKey($request->product_id)
                    ->where('admin_id', panel_owner_id())
                    ->lockForUpdate()
                    ->firstOrFail();

                $cart = Cart::where('order_id', $request->order_id)
                    ->where('admin_id', panel_owner_id())
                    ->firstOrFail();

                $modifiers = collect($request->input('modifiers', []));
                [$modifiers, $lineUnit] = $this->resolveModifiers->execute(
                    $product,
                    $modifiers->all()
                );

                [$additions, $additionsTotal] = $this->resolveAdditions->execute(
                    $product,
                    $request->input('additions', [])
                );
                $lineUnit += $additionsTotal;

                $qty = (float) $request->quantity;
                $discount = (float) $request->discount;
                $totalPrice = ($qty * $lineUnit) - $discount;

                if ($product->isIngredient()) {
                    throw new RuntimeException('Ingredient not for sale.');
                }

                // Pure menu: recipe-less dishes are always available;
                // ingredient shortages surface from execute() below.

                CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $product->id,
                    'unit_price' => $lineUnit,
                    'discount' => $discount,
                    'quantity' => $qty,
                    'total_price' => $totalPrice,
                    'modifiers_json' => $modifiers ?: null,
                    'additions_json' => $additions ?: null,
                ]);

                $this->deductRecipeStock->execute($product, $qty);

                $cart_items = CartItem::where('cart_id', $cart->id)->with('item')->get();
                $itemHtml = '';

                foreach ($cart_items as $item) {
                    $itemHtml .= View::make('components.pos.cart-item', ['item' => $item])->render();
                }

                $product->refresh();
                $product->loadMissing(['recipe.ingredients.ingredientProduct']);
                $servings = $this->deductRecipeStock->availableServings($product);

                return apiResponse([
                    'item' => [
                        'id' => $product->id,
                        'stock' => $servings ?? 0,
                        'unlimited' => $servings === null,
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
        try {
            return DB::transaction(function () use ($request) {
                $cart_item = CartItem::whereHas('cart', function ($q) {
                    $q->where('admin_id', panel_owner_id());
                })->with(['item.recipe.ingredients.ingredientProduct'])->findOrFail($request->cart_item_id);

                $item = $cart_item->item;
                $this->deductRecipeStock->restore($item, $cart_item->quantity);
                $item->loadMissing(['recipe.ingredients.ingredientProduct']);
                $servings = $this->deductRecipeStock->availableServings($item->fresh());

                $response = [
                    'item' => [
                        'id' => $item->id,
                        'stock' => $servings ?? 0,
                        'unlimited' => $servings === null,
                    ],
                ];

                $cart_item->delete();

                return apiResponse($response, 'Item removed successfully');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function updateQuantity(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $cart_item = CartItem::whereHas('cart', function ($q) {
                    $q->where('admin_id', panel_owner_id());
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

                $item->loadMissing(['recipe.ingredients.ingredientProduct']);
                $servings = $this->deductRecipeStock->availableServings($item->fresh());

                $response = [
                    'item' => ['id' => $item->id, 'stock' => $servings ?? 0, 'unlimited' => $servings === null],
                    'cart_item' => ['total_price' => $cart_item->total_price],
                ];

                return apiResponse($response, 'Cart updated successfully');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
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
                        'admin_id' => panel_owner_id(),
                        'name' => $customer_name,
                        'phone' => $customer_phone,
                    ]);
                    $customer_id = $newCustomer->id;
                }

                $cart = Cart::where('order_id', $request->order_id)
                    ->where('admin_id', panel_owner_id())
                    ->with('items.item.unit')
                    ->lockForUpdate()
                    ->first();

                if (! $cart || count($cart->items) == 0) {
                    throw new RuntimeException('Cart is empty.');
                }

                $subTotal = 0;
                $saleItems = [];

                foreach ($cart->items as $item) {
                    $subTotal += $item->total_price;
                    $saleItems[] = [
                        'admin_id' => $cart->admin_id,
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->name,
                        'buying_price' => $item->item->buying_price,
                        'unit_price' => $item->unit_price,
                        'unit' => $item->item->unit->short_name,
                        'quantity' => $item->quantity,
                        'total_price' => $item->total_price,
                        'modifiers_json' => $item->modifiers_json,
                        'additions_json' => $item->additions_json,
                    ];
                }

                $discount = $request->discount_amount ?? 0;
                $paid = (float) ($request->paid_amount ?? 0);
                $payable = ($subTotal - $discount);

                $giftCard = null;
                $giftCardPaid = 0.0;
                if ($request->filled('gift_card_code') && $payable > 0) {
                    $giftCard = GiftCard::query()
                        ->where('admin_id', panel_owner_id())
                        ->where('code', mb_strtoupper($request->gift_card_code))
                        ->lockForUpdate()
                        ->first();

                    if ($giftCard && $giftCard->isRedeemable()) {
                        $giftCardPaid = min((float) $giftCard->balance, $payable);
                        $giftCard->redeem($giftCardPaid);
                        $paid = min($paid + $giftCardPaid, $payable);
                    }
                }

                $paymentOption = $request->payment_type ?? 'cash';
                if ($giftCard && $giftCardPaid >= $payable) {
                    $paymentOption = 'gift_card';
                }

                // POS mode split: dine-in orders live on a table, counter orders never touch tables.
                $mode = in_array($request->order_type, \App\Enums\OrderType::posModes(), true)
                    ? $request->order_type
                    : \App\Enums\OrderType::DINE_IN->value;

                // Prefer request aliases used by POS UI (table_id / employee_id) with dining_* fallbacks.
                $tableId = $request->dining_table_id ?? $request->table_id;
                $employeeId = $request->employee_id ?? $request->employee_id;

                if ($mode === \App\Enums\OrderType::COUNTER->value) {
                    $tableId = null;
                } else {
                    $tableForOwner = $tableId
                        ? DiningTable::self()->whereKey($tableId)->first()
                        : null;

                    if (! $tableForOwner) {
                        throw new RuntimeException('Select a table for dine-in order.');
                    }
                }

                if ($request->client_order_id) {
                    $existingSale = Sale::query()
                        ->where('admin_id', $cart->admin_id)
                        ->where('client_order_id', $request->client_order_id)
                        ->first();

                    if ($existingSale) {
                        return successResponse('Sale completed.');
                    }
                }

$saleData = [
                    'admin_id' => $cart->admin_id,
                    'customer_id' => $customer_id,
                    'customer_name' => $customer_name !== '' ? $customer_name : null,
                    'customer_phone' => $customer_phone !== '' ? $customer_phone : null,
                    'order_type' => $mode,
                    'order_id' => $cart->order_id,
                    'client_order_id' => $request->client_order_id,
                    'device_id' => $request->device_id,
                    'created_at_client' => $request->created_at_client
                        ? Carbon::parse($request->created_at_client)
                        : null,
                    'synced_at' => $request->client_order_id ? now() : null,
                    'sale_date' => date('Y-m-d'),
                    'subtotal' => $subTotal,
                    'discount' => $discount,
                    'payable' => $payable,
                    'paid' => $paid,
                    'due' => ($payable - $paid),
                    'amount_paid_by_gift_card' => $giftCardPaid,
                    'gift_card_id' => $giftCard?->id,
                    'payment_option' => $paymentOption,
                    'note' => $request->note,
                    'branch_id' => active_branch_id(),
                ];

                if ($tableId) {
                    $saleData['dining_table_id'] = $tableId;
                    $tableForBranch = DiningTable::self()->whereKey($tableId)->first();
                    if ($tableForBranch?->branch_id) {
                        $saleData['branch_id'] = $tableForBranch->branch_id;
                    }
                }
                if ($employeeId) {
                    $saleData['employee_id'] = $employeeId;
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
                        $table->update(['status' => TableStatus::OCCUPIED]);
                    }
                }

                $sale->load(['items', 'table']);
                $this->createKitchenTicket->execute($sale);

                return successResponse('Sale completed.');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function holdOrder(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $cart = Cart::where('order_id', $request->order_id)
                    ->where('admin_id', panel_owner_id())
                    ->with('items.item.unit')
                    ->lockForUpdate()
                    ->first();

                if (! $cart || count($cart->items) == 0) {
                    throw new RuntimeException('Cart is empty.');
                }

                $customer_id = $request->customer_id ?: null;
                $customer_name = $request->customer_name ?? '';
                $customer_phone = $request->customer_phone ?? '';

                if ($customer_name != '' && $customer_phone != '') {
                    $newCustomer = Customer::create([
                        'admin_id' => panel_owner_id(),
                        'name' => $customer_name,
                        'phone' => $customer_phone,
                    ]);
                    $customer_id = $newCustomer->id;
                }

                $subTotal = 0;
                $saleItems = [];

                foreach ($cart->items as $item) {
                    $subTotal += $item->total_price;
                    $saleItems[] = [
                        'admin_id' => $cart->admin_id,
                        'item_id' => $item->item_id,
                        'item_name' => $item->item->name,
                        'buying_price' => $item->item->buying_price,
                        'unit_price' => $item->unit_price,
                        'unit' => $item->item->unit->short_name,
                        'quantity' => $item->quantity,
                        'total_price' => $item->total_price,
                        'modifiers_json' => $item->modifiers_json,
                        'additions_json' => $item->additions_json,
                    ];
                }

                $tableId = $request->dining_table_id ?? $request->table_id;
                $employeeId = $request->employee_id ?? $request->employee_id;
                $payable = $subTotal;

                // POS mode split: dine-in holds live on a table, counter holds never touch tables.
                $mode = in_array($request->order_type, \App\Enums\OrderType::posModes(), true)
                    ? $request->order_type
                    : \App\Enums\OrderType::DINE_IN->value;

                if ($mode === \App\Enums\OrderType::COUNTER->value) {
                    $tableId = null;
                } elseif ($tableId && ! DiningTable::self()->whereKey($tableId)->exists()) {
                    throw new RuntimeException('Select a table for dine-in order.');
                }

$saleData = [
                    'admin_id' => $cart->admin_id,
                    'customer_id' => $customer_id,
                    'customer_name' => $customer_name !== '' ? $customer_name : null,
                    'customer_phone' => $customer_phone !== '' ? $customer_phone : null,
                    'order_type' => $mode,
                    'is_hold' => 1,
                    'order_id' => $cart->order_id,
                    'sale_date' => date('Y-m-d'),
                    'subtotal' => $subTotal,
                    'discount' => 0,
                    'payable' => $payable,
                    'paid' => 0,
                    'due' => $payable,
                    'note' => $request->note,
                    'branch_id' => active_branch_id(),
                ];

                if ($tableId) {
                    $saleData['dining_table_id'] = $tableId;
                    $tableForBranch = DiningTable::self()->whereKey($tableId)->first();
                    if ($tableForBranch?->branch_id) {
                        $saleData['branch_id'] = $tableForBranch->branch_id;
                    }
                }
                if ($employeeId) {
                    $saleData['employee_id'] = $employeeId;
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
                        $table->update(['status' => TableStatus::OCCUPIED]);
                    }
                }

                $sale->load(['items', 'table']);
                $this->createKitchenTicket->execute($sale);

                return successResponse('Sale on hold.');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }
}








