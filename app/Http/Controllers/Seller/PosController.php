<?php

namespace App\Http\Controllers\Seller;

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
    protected StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    public function index(Request $request)
    {
        $products = Product::self()->with(['category', 'unit'])->latest('id')->get();
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

        return view('seller.pos', compact('products', 'cart', 'customers', 'recentSales', 'runningSales', 'categories', 'diningTables', 'employees', 'sale', 'saleItems'));
    }

    public function addItem(PosAddItemRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $product = Product::query()->whereKey($request->product_id)->lockForUpdate()->firstOrFail();
                $cart = Cart::where('order_id', $request->order_id)
                    ->where('seller_id', auth()->id())
                    ->firstOrFail();

                if (!$this->stockService->hasAvailableStock($product, $request->quantity)) {
                    throw new RuntimeException('Insufficient stock!');
                }

                CartItem::create([
                    'cart_id' => $cart->id,
                    'item_id' => $product->id,
                    'unit_price' => $request->unit_price,
                    'discount' => $request->discount,
                    'quantity' => $request->quantity,
                    'total_price' => ($request->quantity * $request->unit_price) - $request->discount,
                ]);

                $this->stockService->deductStock($product, $request->quantity);

                $cart_items = CartItem::where('cart_id', $cart->id)->with('item')->get();
                $itemHtml = '';

                foreach ($cart_items as $item) {
                    $itemHtml .= View::make('components.pos.cart-item', ['item' => $item])->render();
                }

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
            })->findOrFail($request->cart_item_id);

            $item = $cart_item->item;
            $this->stockService->restoreStock($item, $cart_item->quantity);

            $response = [
                'item' => [
                    'id' => $item->id,
                    'stock' => $item->availableStock
                ]
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
            })->with('item')->findOrFail($request->cart_item_id);

            $item = $cart_item->item;
            $quantity = $request->quantity;

            if ($quantity > $cart_item->quantity) {
                $diff = $quantity - $cart_item->quantity;
                $this->stockService->deductStock($item, $diff);
            } elseif ($quantity < $cart_item->quantity) {
                $diff = $cart_item->quantity - $quantity;
                $this->stockService->restoreStock($item, $diff);
            }

            $cart_item->quantity = $quantity;
            $cart_item->total_price = ($cart_item->unit_price * $quantity);
            $cart_item->save();

            $response = [
                'item' => ['id' => $item->id, 'stock' => $item->availableStock],
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

                if (!$cart || count($cart->items) == 0) {
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
                    ];
                }

                $discount = $request->discount_amount ?? 0;
                $paid = $request->paid_amount ?? 0;
                $payable = ($subTotal - $discount);

                // Prefer request aliases used by POS UI (table_id / employee_id) with dining_* fallbacks.
                $tableId = $request->dining_table_id ?? $request->table_id;
                $employeeId = $request->seller_employee_id ?? $request->employee_id;

                $saleData = [
                    'seller_id' => $cart->seller_id,
                    'customer_id' => $customer_id,
                    'order_id' => $cart->order_id,
                    'sale_date' => date('Y-m-d'),
                    'subtotal' => $subTotal,
                    'discount' => $discount,
                    'payable' => $payable,
                    'paid' => $paid,
                    'due' => ($payable - $paid),
                    'payment_type' => $request->payment_type ?? 'cash',
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

            if (!$cart || count($cart->items) == 0) {
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

            return successResponse('Sale held successfully');
        });
    }
}
