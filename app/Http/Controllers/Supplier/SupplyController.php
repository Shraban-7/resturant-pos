<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\SupplierCart;
use App\Models\SupplierCartItem;
use App\Models\SupplierProduct;
use App\Models\SupplierProductCategory;
use App\Models\SupplierSale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class SupplyController extends Controller
{
    public function index()
    {
        $products = SupplierProduct::self()->get();

        $sellers = User::seller()->get();

        $recentSales = SupplierSale::self()->latest('id')->limit(5)->get();

        $cart = SupplierCart::query()->firstOrCreate(
            ['supplier_id' => auth()->id()],
            ['order_id' => generateOrderId('SU')]
        );

        $categories = SupplierProductCategory::withCount('products')->get();

        return view('supplier.supply', compact('products', 'cart', 'sellers', 'recentSales', 'categories'));
    }

    public function addItem(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:supplier_carts,order_id',
            'product_id' => 'required|exists:supplier_products,id',
            'quantity' => 'required|numeric',
            'discount' => 'required|numeric',
            'unit_price' => 'required|numeric',
            'note' => 'nullable|string',
        ]);

        $product = SupplierProduct::find($request->product_id);

        $cart = SupplierCart::where('order_id', $request->order_id)->first();

        $currentStock = $product->availableStock;
        $newStock = $currentStock - $request->quantity;

        if ($newStock < 0) {
            return errorResponse('Insufficient stock!');
        }

        $cart_item = SupplierCartItem::create([
            'cart_id' => $cart->id,
            'item_id' => $product->id,
            'unit_price' => $request->unit_price,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
            'total_price' => ($request->quantity * $request->unit_price) - $request->discount,
            'note' => $request->note,
        ]);

        $product->stock_out +=  $request->quantity;
        $product->save();

        $cart_items = SupplierCartItem::where('cart_id', $cart->id)->with('item')->get();
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
        $cart_item = SupplierCartItem::find($request->cart_item_id);

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
        $cart_item = SupplierCartItem::with('item')->find($request->cart_item_id);
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
            'order_id' => 'required|string|exists:supplier_carts,order_id',
            'customer' => 'required|numeric',
            'discount_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);

        $customer_id = $request->customer;

        $cart = SupplierCart::where('order_id', $request->order_id)->with('items.item.unit')->first();

        if (!$cart || count($cart->items) == 0) {
            return errorResponse('No items added!');
        }

        $subTotal = 0;
        $saleItems = [];

        foreach ($cart->items as $item) {

            $subTotal += $item->total_price;

            $saleItems[] = [
                'supplier_id' => $cart->supplier_id,
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
            'supplier_id' => $cart->supplier_id,
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

        $sale = SupplierSale::create($saleData);

        foreach ($saleItems as $saleItem) {
            $sale->items()->create($saleItem);
        }

        $cart->items()->delete();
        $cart->delete();

        return successResponse('Supply complete');
    }

    public function invoice(SupplierSale $sale)
    {
        $sale->load('items', 'customer');

        $settings = BusinessSetting::where('user_id', $sale->supplier_id)->first();

        return view('supplier.invoice', compact('sale', 'settings'));
    }

    public function invoices(Request $request)
    {
        $sales = SupplierSale::self()->with('customer')->latest('id')->paginate(20)->withQueryString();
        $totalSales = SupplierSale::self()->sum('payable');

        return view('supplier.invoices.index', compact('sales', 'totalSales'));
    }

    public function markPaid(SupplierSale $sale)
    {
        $sale->paid = $sale->payable;
        $sale->due = 0;
        $sale->save();

        return redirect()->back()->with('success', 'Sale Due Paid Successfully');
    }
}
