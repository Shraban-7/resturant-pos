<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;

use App\Actions\CreateKitchenTicketAction;
use App\Actions\DeductRecipeStockAction;
use App\Actions\ResolveProductAdditionsAction;
use App\Actions\ResolveProductAddonsAction;
use App\Actions\ResolveProductModifiersAction;
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
        protected ResolveProductAdditionsAction $resolveAdditions,
        protected ResolveProductAddonsAction $resolveProductAddons,
    ) {
    }

    /**
     * @return \Illuminate\Support\Collection<int, Product>
     */
    private function lockedAddonProducts(array $addons): \Illuminate\Support\Collection
    {
        $ids = collect($addons)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return collect();
        }

        return Product::query()
            ->where('admin_id', panel_owner_id())
            ->whereKey($ids)
            ->with('recipe.ingredients.ingredientProduct')
            ->lockForUpdate()
            ->get();
    }

    public function index(Request $request)
    {
        $sales = Sale::self()->with(['customer', 'items.product', 'table', 'waiter'])->latest('id')->paginate(20)->withQueryString();
        $totalSales = Sale::self()->sum('payable');

        return view('admin.sales.index', compact('sales', 'totalSales'));
    }

    public function invoice(Sale $sale)
    {
        abort_unless((int) $sale->admin_id === (int) panel_owner_id(), 403);

        $sale->load('items', 'customer');

        $settings = BusinessSetting::where('user_id', $sale->admin_id)->first();

        return view('admin.sales.pos_receipt', compact('sale', 'settings'));
    }

    public function markPaid(Sale $sale)
    {
        abort_unless((int) $sale->admin_id === (int) panel_owner_id(), 403);

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
                    $table->update(['status' => TableStatus::FREE]);
                }
            }
        });

        return redirect()->back()->with('success', 'Due paid.');
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
                    'modifiers' => 'nullable|array',
                    'modifiers.*.id' => 'required_with:modifiers|integer',
                    'additions' => 'nullable|array',
                    'additions.*.id' => 'required_with:additions|integer',
                    'addons' => 'nullable|array',
                    'addons.*.id' => 'required_with:addons|integer',
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

                if ($product->isIngredient()) {
                    throw new RuntimeException('Ingredient not for sale.');
                }

                // Pure menu: recipe-less dishes are always available;
                // ingredient shortages surface from execute() below.

                [$modifiers, $lineUnit] = $this->resolveModifiers->execute(
                    $product,
                    $request->input('modifiers', [])
                );

                [$additions, $additionsTotal] = $this->resolveAdditions->execute(
                    $product,
                    $request->input('additions', [])
                );
                $lineUnit += $additionsTotal;

                [$productAddons, $productAddonsTotal] = $this->resolveProductAddons->execute(
                    $product,
                    $request->input('addons', [])
                );
                $lineUnit += $productAddonsTotal;

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'admin_id' => $sale->admin_id,
                    'item_id' => $product->id,
                    'item_name' => $product->name,
                    'buying_price' => $product->buying_price,
                    'unit_price' => $lineUnit,
                    'unit' => $product->unit?->short_name ?? 'pcs',
                    'quantity' => $qty,
                    'total_price' => ($qty * $lineUnit) - $discount,
                    'modifiers_json' => $modifiers ?: null,
                    'additions_json' => $additions ?: null,
                    'addons_json' => $productAddons ?: null,
                ]);

                $this->deductRecipeStock->execute($product, $qty);
                foreach ($this->lockedAddonProducts($productAddons) as $addonProduct) {
                    $this->deductRecipeStock->execute($addonProduct, $qty);
                }

                $sale->subtotal += $saleItem->total_price;
                \App\Support\VatCalculator::refreshSale($sale);
                $sale->save();

                $this->createKitchenTicket->fireAdditionalItems($sale, [$saleItem]);

                $saleItems = SaleItem::where('sale_id', $sale->id)->with('product')->get();
                $itemHtml = '';
                foreach ($saleItems as $line) {
                    $itemHtml .= View::make('components.pos.sale-item', ['item' => $line])->render();
                }

                $product->loadMissing(['recipe.ingredients.ingredientProduct']);
                $servings = $this->deductRecipeStock->availableServings($product->fresh());

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

    public function removeSaleItem(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $saleItem = SaleItem::query()
                ->whereHas('sale', fn ($q) => $q->where('admin_id', panel_owner_id()))
                ->with(['product.recipe.ingredients.ingredientProduct'])
                ->findOrFail($request->sale_item_id);

            $item = $saleItem->product;
            $this->deductRecipeStock->restore($item, (float) $saleItem->quantity);
            foreach ($this->lockedAddonProducts($saleItem->addons_json ?? []) as $addonProduct) {
                $this->deductRecipeStock->restore($addonProduct, (float) $saleItem->quantity);
            }
            $item->loadMissing(['recipe.ingredients.ingredientProduct']);
            $servings = $this->deductRecipeStock->availableServings($item->fresh());

            $response = [
                'item' => [
                    'id' => $item->id,
                    'stock' => $servings ?? 0,
                    'unlimited' => $servings === null,
                ],
            ];

            $sale = Sale::self()->whereKey($saleItem->sale_id)->lockForUpdate()->firstOrFail();
            $sale->subtotal -= $saleItem->total_price;
            \App\Support\VatCalculator::refreshSale($sale);
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
                    ->whereHas('sale', fn ($q) => $q->where('admin_id', panel_owner_id()))
                    ->with(['product.recipe.ingredients.ingredientProduct'])
                    ->findOrFail($request->sale_item_id);

                $sale = Sale::self()->whereKey($saleItem->sale_id)->lockForUpdate()->firstOrFail();
                $item = $saleItem->product;
                $quantity = (float) $request->quantity;

                $addonProducts = $this->lockedAddonProducts($saleItem->addons_json ?? []);

                if ($quantity > $saleItem->quantity) {
                    $diff = $quantity - $saleItem->quantity;
                    $this->deductRecipeStock->execute($item, $diff);
                    foreach ($addonProducts as $addonProduct) {
                        $this->deductRecipeStock->execute($addonProduct, $diff);
                    }
                    $sale->subtotal += $saleItem->unit_price * $diff;
                } elseif ($quantity < $saleItem->quantity) {
                    $diff = $saleItem->quantity - $quantity;
                    $this->deductRecipeStock->restore($item, $diff);
                    foreach ($addonProducts as $addonProduct) {
                        $this->deductRecipeStock->restore($addonProduct, $diff);
                    }
                    $sale->subtotal -= $saleItem->unit_price * $diff;
                }
                \App\Support\VatCalculator::refreshSale($sale);
                $sale->save();

                $saleItem->quantity = $quantity;
                $saleItem->total_price = $saleItem->unit_price * $quantity;
                $saleItem->save();

                $item->loadMissing(['recipe.ingredients.ingredientProduct']);
                $servings = $this->deductRecipeStock->availableServings($item->fresh());

                return apiResponse([
                    'item' => ['id' => $item->id, 'stock' => $servings ?? 0, 'unlimited' => $servings === null],
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
                'admin_id' => panel_owner_id(),
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
                return errorResponse('Cart is empty.');
            }

            $settings = \App\Support\VatCalculator::settingsFor((int) $sale->admin_id);
            $discount = \App\Support\GlobalDiscount::totalDiscount((float) $sale->subtotal, (float) ($request->discount_amount ?? 0), $settings);
            $paid = $request->paid_amount ?? 0;
            $vat = \App\Support\VatCalculator::calculate((float) $sale->subtotal, (float) $discount, $settings);
            $payable = $vat['payable'];

            $saleData = [
                'is_hold' => 0,
                'customer_id' => $customer_id,
                'sale_date' => date('Y-m-d'),
                'subtotal' => $sale->subtotal,
                'discount' => $discount,
                'vat_mode' => $vat['mode'],
                'vat_rate' => $vat['rate'],
                'vat_amount' => $vat['vat'],
                'payable' => $payable,
                'paid' => $paid,
                'due' => ($payable - $paid),
                'note' => $request->note,
            ];

            if ($request->table_id) {
                $saleData['dining_table_id'] = $request->table_id;
            }
            if ($request->employee_id) {
                $saleData['employee_id'] = $request->employee_id;
            }

            $sale->update($saleData);

            if ($request->table_id) {
                $table = DiningTable::self()
                    ->where('id', $request->table_id)
                    ->lockForUpdate()
                    ->first();

                if ($table) {
                    $table->update(['status' => TableStatus::OCCUPIED]);
                }
            }

            return successResponse('Sale completed.');
        });
    }
}







