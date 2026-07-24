<?php

namespace App\Http\Controllers\Seller;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Customer;
use App\Models\SaleItem;
use App\Models\DiningTable;
use Illuminate\Http\Request;
use App\Models\BusinessSetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $sales = Sale::self()->with(['customer', 'items.product', 'table', 'waiter'])->latest('id')->paginate(20)->withQueryString();
        $totalSales = Sale::self()->sum('payable');

        return view('seller.sales.index', compact('sales', 'totalSales'));
    }

    public function invoice(Sale $sale)
    {
        $sale->load('items', 'customer');

        $settings = BusinessSetting::where('user_id', $sale->seller_id)->first();

        return view('seller.sales.pos_receipt', compact('sale', 'settings'));
    }

    public function markPaid(Sale $sale)
    {
        $sale->paid = $sale->payable;
        $sale->due = 0;

        if ($sale->dining_table_id != '') {
            $sale->table->status = DiningTable::FREE;
        }
        $sale->save();

        return redirect()->back()->with('success', 'Sale Due Paid Successfully');
    }

    public function addToCart($product_id)
    {
        $product = Product::find($product_id);

        if ($product->stock_in - $product->stock_out == 0) {
            return redirect()->back()->with('error', 'Stock not available!');
        }

        $sale = Sale::whereNull('paid_amount')->first();

        $product->increment('stock_out');
        $product->save();

        if ($sale) {

            $productCheck = SaleItem::where('product_id', $product_id)->first();

            if ($productCheck) {
                $productCheck->quantity = $productCheck->quantity + 1;
                $productCheck->price = $productCheck->price;
                $productCheck->total_price += $productCheck->price;
                $productCheck->save();
            } else {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product_id,
                    'price' => $product->price,
                    'quantity' => 1,
                    'total_price' => $product->price,
                ]);
            }

            $sale->subtotal += $product->price;
            $sale->save();

            return redirect()->back()->with('success', 'Added to cart successfully');
        }

        $sale = Sale::create([
            'seller_id' => auth()->id(),
            'subtotal' => $product->price,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product_id,
            'price' => $product->price,
            'quantity' => 1,
            'total_price' => $product->price,
        ]);

        return redirect()->back()->with('success', 'Added to cart successfully');
    }

    public function deleteFromCart($sale_item_id)
    {
        $saleItem = SaleItem::with('sale', 'product')->find($sale_item_id);

        $product = $saleItem->product;
        $sale = $saleItem->sale;

        $sale->subtotal -= $product->price;
        $sale->save();

        $saleItem->delete();

        $product->decrement('stock_out');
        $product->save();

        return redirect()->back()->with('success', 'Removed from cart successfully');
    }

    public function updateCart($sale_item_id, Request $request)
    {
        $type = $request->type;

        $saleItem = SaleItem::with('sale', 'product')->find($sale_item_id);

        $sale = $saleItem->sale;

        if ($type == 'increment') {

            if ($saleItem->product->stock_in - $saleItem->product->stock_out == 0) {
                return redirect()->back()->with('error', 'Stock not available!');
            }

            $saleItem->quantity = $saleItem->quantity + 1;
            $saleItem->total_price = $saleItem->total_price + $saleItem->price;
            $saleItem->save();

            $sale->subtotal += $saleItem->price;
            $sale->save();

            $saleItem->product->increment('stock_out');
            $saleItem->product->save();

            return redirect()->back()->with('success', 'Increment successful');
        }

        if ($type == 'decrement') {

            if ($saleItem->quantity == 1) {
                $saleItem->delete();
            }

            if ($saleItem->quantity > 1) {
                $saleItem->quantity = $saleItem->quantity - 1;
                $saleItem->total_price = $saleItem->total_price - $saleItem->price;
                $saleItem->save();
            }

            $sale->subtotal -= $saleItem->price;

            if ($sale->subtotal == 0) {
                $sale->delete();
            } else {
                $sale->save();
            }

            $saleItem->product->decrement('stock_out');
            $saleItem->product->save();
        }

        return redirect()->back()->with('success', 'Removed from cart successfully');
    }

    public function checkout($sale_id, Request $request)
    {
        $sale = Sale::find($sale_id);

        $paid = $request->paid_amount;
        $due = $sale->subtotal - $paid;

        $sale->paid_amount = $paid;
        $sale->due_amount = $due;
        $sale->customer_id = $request->customer_id;
        $sale->save();

        return redirect()->route('seller.sales.invoice', $sale->id);
    }

    public function addItemToSale(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:sales,order_id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
            'discount' => 'required|numeric',
            'unit_price' => 'required|numeric',
        ]);

        $product = Product::find($request->product_id);

        $sale = Sale::where('order_id', $request->order_id)->first();

        $currentStock = $product->availableStock;
        $newStock = $currentStock - $request->quantity;

        if ($newStock < 0) {
            return errorResponse('Insufficient stock!');
        }

        $sale_item = SaleItem::create([
            'sale_id' => $sale->id,
            'seller_id' => $sale->seller_id,
            'item_id' => $product->id,
            'item_name' => $product->name,
            'buying_price' => $product->buying_price,
            'unit_price' => $request->unit_price,
            'unit' => $product->unit,
            'discount' => $request->discount,
            'quantity' => $request->quantity,
            'total_price' => ($request->quantity * $request->unit_price) - $request->discount,
        ]);

        $product->stock_out += $request->quantity;
        $product->save();

        $sale->due += $sale_item->total_price;
        $sale->subtotal += $sale_item->total_price;
        $sale->payable += $sale_item->total_price;
        $sale->save();

        $sale_items = SaleItem::where('sale_id', $sale->id)->with('product')->get();
        $itemHtml = '';

        foreach ($sale_items as $sale_item) {
            $itemHtml .= View::make('components.pos.sale-item', ['item' => $sale_item])->render();
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

    public function removeSaleItem(Request $request)
    {
        $sale_item = SaleItem::find($request->sale_item_id);

        $item = $sale_item->product;

        $item->stock_out -= $sale_item->quantity;
        $item->save();

        $response = [
            'item' => [
                'id' => $item->id,
                'stock' => $item->availableStock
            ]
        ];

        $sale = Sale::where('id', $sale_item->sale_id)->first();

        $sale->due -= $sale_item->total_price;
        $sale->subtotal -= $sale_item->total_price;
        $sale->payable -= $sale_item->total_price;
        $sale->save();

        $sale_item->delete();

        return apiResponse($response, 'Item removed successfully');
    }

    public function updateSaleItemQuantity(Request $request)
    {
        $sale_item = SaleItem::with('product')->find($request->sale_item_id);
        $sale = Sale::where('id', $sale_item->sale_id)->first();
        $item = $sale_item->product;
        $quantity = $request->quantity;

        if ($quantity > $sale_item->quantity) {
            $updatedQuantity = ($quantity - $sale_item->quantity);
            $item->stock_out += $updatedQuantity;
            $totalPriceQuantity = $sale_item->unit_price * $updatedQuantity;
            $sale->due += $totalPriceQuantity;
            $sale->subtotal += $totalPriceQuantity;
            $sale->payable += $totalPriceQuantity;
            $sale->save();
        }

        if ($quantity < $sale_item->quantity) {
            $updatedQuantity = ($sale_item->quantity - $quantity);
            $item->stock_out -= $updatedQuantity;
            $totalPriceQuantity = $sale_item->unit_price * $updatedQuantity;
            $sale->due -= $totalPriceQuantity;
            $sale->subtotal -= $totalPriceQuantity;
            $sale->payable -= $totalPriceQuantity;
            $sale->save();
        }

        $sale_item->quantity = $quantity;
        $sale_item->total_price = ($sale_item->unit_price * $quantity);
        $sale_item->save();

        $item->save();

        $response = [
            'item' => ['id' => $item->id, 'stock' => $item->availableStock],
            'sale_item' => ['total_price' => $sale_item->total_price],
        ];

        return apiResponse($response, 'Sale updated successfully');
    }

    public function saleUpdate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|string|exists:sales,order_id',
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

        $sale = Sale::where('order_id', $request->order_id)->with('items.product.unit')->first();

        if (!$sale || count($sale->items) == 0) {
            return errorResponse('No items added!');
        }

        $discount = $request->discount_amount ?? 0;
        $paid = $request->paid_amount ?? 0;
        $payable = ($sale->subtotal - $discount);

        $saleData = [
            'seller_id' => $sale->seller_id,
            'is_hold' => 0,
            'customer_id' => $customer_id,
            'order_id' => $sale->order_id,
            'sale_date' => date('Y-m-d'),
            'subtotal' => $sale->subtotal,
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

        $sale->update($saleData);

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
}
