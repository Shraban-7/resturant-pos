<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SellerEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {

        $products = Product::self()->latest('id')->get();
        $customers = Customer::self()->get();

        $recentSales = Sale::self()->where('is_hold', 0)->latest('id')->limit(5)->get();
        $runningSales = Sale::self()->where('is_hold', 1)->latest('id')->limit(5)->get();

        $cart = Cart::query()->firstOrCreate(
            ['seller_id' => auth()->id()],
            ['order_id' => generateOrderId()]
        );

        $categories = ProductCategory::withCount('products')->get();
        $diningTables = DiningTable::self()->get();
        $employees = SellerEmployee::self()->get();
        $cartItems = $cart->items;
        $saleItems = null;
        $sale = null;

        if ($request->has('sale')) {
            $sale = Sale::where('order_id', request('sale'))->first();
            if ($sale) {
                $saleItems = $sale->items;
                $saleItems = $saleItems->merge($cartItems);
            }
        }

        return view('seller.pos', compact('products', 'cart', 'customers', 'recentSales', 'runningSales', 'categories', 'diningTables', 'employees', 'sale', 'saleItems'));
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:carts,order_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
            'discount' => 'required|numeric',
            'unit_price' => 'required|numeric',
        ]);

        $product = Product::find($request->product_id);

        $cart = Cart::where('order_id', $request->order_id)->first();

        $currentStock = $product->availableStock;
        $newStock = $currentStock - $request->quantity;

        if ($newStock < 0) {
            return errorResponse('Insufficient stock!');
        }

        $cart_item = CartItem::create([
            'cart_id' => $cart->id,
            'item_id' => $product->id,
            'unit_price' => $request->unit_price,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
            'total_price' => ($request->quantity * $request->unit_price) - $request->discount,
        ]);

        $product->stock_out += $request->quantity;
        $product->save();

        $cart_items = CartItem::where('cart_id', $cart->id)->with('item')->get();
        $itemHtml = '';

        foreach ($cart_items as $cart_item) {
            $itemHtml .= View::make('components.pos.cart-item', ['item' => $cart_item])->render();
        }

        $response = [
            'item' => [
                'id' => $product->id,
                'stock' => $newStock
            ],
            'cart_item_html' => $itemHtml
        ];

        return apiResponse($response, 'Item addedd successfully');
    }

    public function removeItem(Request $request)
    {
        $cart_item = CartItem::find($request->cart_item_id);

        $item = $cart_item->item;
        $item->stock_out -= $cart_item->quantity;
        $item->save();

        $response = [
            'item' => [
                'id' => $item->id,
                'stock' => $item->availableStock
            ]
        ];

        $cart_item->delete();

        return apiResponse($response, 'Item removed successfully');
    }

    public function updateQuantity(Request $request)
    {
        $cart_item = CartItem::with('item')->find($request->cart_item_id);
        $item = $cart_item->item;
        $quantity = $request->quantity;

        if ($quantity > $cart_item->quantity) {
            $updatedQuantity = ($quantity - $cart_item->quantity);
            $item->stock_out += $updatedQuantity;
        }

        if ($quantity < $cart_item->quantity) {
            $updatedQuantity = ($cart_item->quantity - $quantity);
            $item->stock_out -= $updatedQuantity;
        }

        $cart_item->quantity = $quantity;
        $cart_item->total_price = ($cart_item->unit_price * $quantity);
        $cart_item->save();

        $item->save();

        $response = [
            'item' => ['id' => $item->id, 'stock' => $item->availableStock],
            'cart_item' => ['total_price' => $cart_item->total_price],
        ];

        return apiResponse($response, 'Cart updated successfully');
    }


    public function checkout(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:carts,order_id',
            'customer_id' => 'nullable|numeric',
            'table_id' => 'nullable|numeric',
            'employee_id' => 'nullable|numeric',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'discount_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ], [
            'customer_id.required' => 'Please select a customer',
        ]);

        $customer_id = null;

        if ($request->customer_id != '') {
            $customer_id = $request->customer_id;
        }

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

        $cart = Cart::where('order_id', $request->order_id)->with('items.item.unit')->first();

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

        $discount = $request->discount_amount ?? 0;
        $paid = $request->paid_amount ?? 0;
        $payable = ($subTotal - $discount);

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
            'note' => $request->note,
        ];

        if ($request->table_id) {
            $saleData['dining_table_id'] = $request->table_id;
        }
        if ($request->employee_id) {
            $saleData['seller_employee_id'] = $request->employee_id;
        }

        $sale = Sale::create($saleData);

        foreach ($saleItems as $saleItem) {
            $sale->items()->create($saleItem);
        }

        $cart->items()->delete();
        $cart->delete();

        if ($request->table_id) {
            $table = DiningTable::self()
                ->where('id', $request->table_id)
                ->first();

            if ($table) {
                $table->update(['status' => DiningTable::OCCUPIED]);
            }
        }

        return successResponse('Sale complete');
    }

    public function holdOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:carts,order_id',
            'customer_id' => 'nullable|numeric',
            'table_id' => 'nullable|numeric',
            'employee_id' => 'nullable|numeric',
            'customer_name' => 'nullable|string',
            'customer_phone' => 'nullable|string',
            'discount_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ], [
            'customer_id.required' => 'Please select a customer',
        ]);

        $customer_id = null;

        if ($request->customer_id != '') {
            $customer_id = $request->customer_id;
        }

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

        $cart = Cart::where('order_id', $request->order_id)->with('items.item.unit')->first();

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

        $discount = $request->discount_amount ?? 0;
        $paid = $request->paid_amount ?? 0;
        $payable = ($subTotal - $discount);

        $saleData = [
            'seller_id' => $cart->seller_id,
            'is_hold' => 1,
            'customer_id' => $customer_id,
            'order_id' => $cart->order_id,
            'sale_date' => date('Y-m-d'),
            'subtotal' => $subTotal,
            'discount' => 0,
            'payable' => $payable,
            'paid' => 0,
            'due' => $payable,
            'note' => $request->note,
        ];

        if ($request->table_id) {
            $saleData['dining_table_id'] = $request->table_id;
        }
        if ($request->employee_id) {
            $saleData['seller_employee_id'] = $request->employee_id;
        }

        $sale = Sale::create($saleData);

        foreach ($saleItems as $saleItem) {
            $sale->items()->create($saleItem);
        }

        $cart->items()->delete();
        $cart->delete();

        if ($request->table_id) {
            $table = DiningTable::self()
                ->where('id', $request->table_id)
                ->first();

            if ($table) {
                $table->update(['status' => DiningTable::OCCUPIED]);
            }
        }

        return successResponse('Sale complete');
    }

    public function oldPos()
    {
        $products = Product::query();
        $customers = Customer::get();

        if (request()->has('product_name')) {
            $products->where('name', 'LIKE', request()->get('product_name') . '%');
        }

        $products = $products->where('is_active', 1)->get();

        $sale = Sale::whereNull('paid_amount')->with('items.product')->first();

        return view('seller.old-pos', compact('products', 'sale', 'customers'));
    }
}
