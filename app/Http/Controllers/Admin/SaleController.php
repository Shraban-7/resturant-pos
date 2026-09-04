<?php

namespace App\Http\Controllers\Admin;

use App\Actions\CreateKitchenTicketAction;
use App\Actions\DeductRecipeStockAction;
use App\Actions\ResolveProductModifiersAction;
use App\Events\TableStatusChangedEvent;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;
use RuntimeException;

class SaleController extends Controller
{
    public function __construct(
        protected StockService $stockService,
        protected DeductRecipeStockAction $deductRecipeStock,
        protected CreateKitchenTicketAction $createKitchenTicket,
        protected ResolveProductModifiersAction $resolveModifiers,
    ) {
    }

    public function index(Request $request)
    {
        $sales = Sale::self()->with(['customer', 'items.product', 'table', 'waiter'])->latest('id')->paginate(20)->withQueryString();
        $totalSales = Sale::self()->sum('payable');

        return view('admin.sales.index', compact('sales', 'totalSales'));
    }

    public function invoice(Sale $sale)
    {
        abort_unless((int) $sale->seller_id === (int) panel_owner_id(), 403);

        $sale->load('items', 'customer');

        $settings = BusinessSetting::where('user_id', $sale->seller_id)->first();

        return view('admin.sales.pos_receipt', compact('sale', 'settings'));
    }

    public function markPaid(Sale $sale)
    {
        abort_unless((int) $sale->seller_id === (int) panel_owner_id(), 403);

        DB::transaction(function () use ($sale) {
            $sale->paid = $sale->payable;
            $sale->due = 0;
            $sale->save();

            if ($sale->dining_table_id) {
                $table = DiningTable::self()
                    ->whereKey($sale->dining_table_id)
                    ->lockForUpdate()
                    ->first();

                if ($table) {
                    $table->update(['status' => DiningTable::FREE]);
                    event(new TableStatusChangedEvent($table->fresh()));
                }
            }
        });

        return redirect()->back()->with('success', 'Sale Due Paid Successfully');
    }

    public function addItemToSale(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'order_id' => 'required|string|exists:sales,order_id',
                    'product_id' => 'required|exists:products,id',
                    'quantity' => 'required|numeric|min:0.01',
                    'discount' => 'required|numeric|min:0',
                    'note' => 'nullable|string|max:500',
                    'modifiers' => 'nullable|array',
                    'modifiers.*.id' => 'required_with:modifiers|integer',
                ]);

                $sale = Sale::self()
                    ->where('order_id', $request->order_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $product = Product::self()
                    ->with(['unit', 'modifiers', 'recipe.ingredients.ingredientProduct'])
                    ->whereKey($request->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $qty = (float) $request->quantity;
                $discount = (float) $request->discount;

                if (! $this->deductRecipeStock->usesRecipe($product)
                    && ! $this->stockService->hasAvailableStock($product, $qty)) {
                    throw new RuntimeException('Insufficient stock!');
                }

                [$modifiers, $lineUnit] = $this->resolveModifiers->execute(
                    $product,
                    $request->input('modifiers', [])
                );

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'seller_id' => $sale->seller_id,
                    'item_id' => $product->id,
                    'item_name' => $product->name,
                    'buying_price' => $product->buying_price,
                    'unit_price' => $lineUnit,
                    'unit' => $product->unit?->short_name ?? 'pcs',
                    'quantity' => $qty,
                    'total_price' => ($qty * $lineUnit) - $discount,
                    'note' => $request->input('note'),
                    'modifiers_json' => $modifiers ?: null,
                ]);

                $this->deductRecipeStock->execute($product, $qty);

                $sale->due += $saleItem->total_price;
                $sale->subtotal += $saleItem->total_price;
                $sale->payable += $saleItem->total_price;
                $sale->save();

                $this->createKitchenTicket->fireAdditionalItems($sale, [$saleItem]);

                $saleItems = SaleItem::where('sale_id', $sale->id)->with('product')->get();
                $itemHtml = '';
                foreach ($saleItems as $line) {
                    $itemHtml .= View::make('components.pos.sale-item', ['item' => $line])->render();
                }

                return apiResponse([
                    'item' => [
                        'id' => $product->id,
                        'stock' => $this->stockService->availableQuantity($product->fresh()),
                    ],
                    'cart_item_html' => $itemHtml,
                ], 'Item added successfully');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
    }

    public function removeSaleItem(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $saleItem = SaleItem::query()
                ->whereHas('sale', fn ($q) => $q->where('seller_id', panel_owner_id()))
                ->with(['product.recipe.ingredients.ingredientProduct'])
                ->findOrFail($request->sale_item_id);

            $item = $saleItem->product;
            $this->deductRecipeStock->restore($item, (float) $saleItem->quantity);

            $response = [
                'item' => [
                    'id' => $item->id,
                    'stock' => $this->stockService->availableQuantity($item->fresh()),
                ],
            ];

            $sale = Sale::self()->whereKey($saleItem->sale_id)->lockForUpdate()->firstOrFail();
            $sale->due -= $saleItem->total_price;
            $sale->subtotal -= $saleItem->total_price;
            $sale->payable -= $saleItem->total_price;
            $sale->save();

            $saleItem->delete();

            return apiResponse($response, 'Item removed successfully');
        });
    }

    public function updateSaleItemQuantity(Request $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $saleItem = SaleItem::query()
                    ->whereHas('sale', fn ($q) => $q->where('seller_id', panel_owner_id()))
                    ->with(['product.recipe.ingredients.ingredientProduct'])
                    ->findOrFail($request->sale_item_id);

                $sale = Sale::self()->whereKey($saleItem->sale_id)->lockForUpdate()->firstOrFail();
                $item = $saleItem->product;
                $quantity = (float) $request->quantity;

                if ($quantity > $saleItem->quantity) {
                    $diff = $quantity - $saleItem->quantity;
                    $this->deductRecipeStock->execute($item, $diff);
                    $deltaPrice = $saleItem->unit_price * $diff;
                    $sale->due += $deltaPrice;
                    $sale->subtotal += $deltaPrice;
                    $sale->payable += $deltaPrice;
                    $sale->save();
                } elseif ($quantity < $saleItem->quantity) {
                    $diff = $saleItem->quantity - $quantity;
                    $this->deductRecipeStock->restore($item, $diff);
                    $deltaPrice = $saleItem->unit_price * $diff;
                    $sale->due -= $deltaPrice;
                    $sale->subtotal -= $deltaPrice;
                    $sale->payable -= $deltaPrice;
                    $sale->save();
                }

                $saleItem->quantity = $quantity;
                $saleItem->total_price = $saleItem->unit_price * $quantity;
                $saleItem->save();

                return apiResponse([
                    'item' => ['id' => $item->id, 'stock' => $this->stockService->availableQuantity($item->fresh())],
                    'sale_item' => ['total_price' => $saleItem->total_price],
                ], 'Sale updated successfully');
            });
        } catch (RuntimeException|InvalidArgumentException $e) {
            return errorResponse($e->getMessage());
        }
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

        $customer_id = $request->customer_id ?: null;
        $customer_name = $request->customer_name ?? '';
        $customer_phone = $request->customer_phone ?? '';

        if ($customer_name != '' && $customer_phone != '') {
            $newCustomer = Customer::create([
                'seller_id' => panel_owner_id(),
                'name' => $customer_name,
                'phone' => $customer_phone,
            ]);
            $customer_id = $newCustomer->id;
        }

        return DB::transaction(function () use ($request, $customer_id) {
            $sale = Sale::self()
                ->where('order_id', $request->order_id)
                ->with('items.product.unit')
                ->lockForUpdate()
                ->first();

            if (! $sale || count($sale->items) == 0) {
                return errorResponse('No items added!');
            }

            $discount = $request->discount_amount ?? 0;
            $paid = $request->paid_amount ?? 0;
            $payable = ($sale->subtotal - $discount);

            $saleData = [
                'is_hold' => 0,
                'customer_id' => $customer_id,
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
                    ->lockForUpdate()
                    ->first();

                if ($table) {
                    $table->update(['status' => DiningTable::OCCUPIED]);
                    event(new TableStatusChangedEvent($table->fresh(), $sale->id));
                }
            }

            return successResponse('Sale complete');
        });
    }
}



