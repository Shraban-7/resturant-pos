<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TableStatus;

use App\Actions\CreateKitchenTicketAction;
use App\Actions\DeductRecipeStockAction;
use App\Actions\ResolveProductModifiersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfflineSyncRequest;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

class OfflineSyncController extends Controller
{
    public function __construct(
        private DeductRecipeStockAction $deductRecipeStock,
        private CreateKitchenTicketAction $createKitchenTicket,
        private ResolveProductModifiersAction $resolveModifiers,
    ) {}

    public function store(OfflineSyncRequest $request)
    {
        $results = [];

        foreach ($request->validated('orders') as $order) {
            try {
                $results[] = $this->reconcile($order);
            } catch (ValidationException $e) {
                $results[] = $this->conflict($order, 'validation_failed', $e->getMessage(), $e->errors());
            } catch (InvalidArgumentException|RuntimeException $e) {
                $results[] = $this->conflict($order, 'inventory_conflict', $e->getMessage());
            } catch (Throwable $e) {
                report($e);

                $results[] = [
                    'client_order_id' => $order['client_order_id'],
                    'status' => 'retry',
                    'code' => 'server_error',
                    'message' => 'The order could not be synchronized yet.',
                ];
            }
        }

        return response()->json([
            'status' => true,
            'results' => $results,
        ]);
    }

    private function reconcile(array $order): array
    {
        $ownerId = (int) panel_owner_id();

        $existing = Sale::query()
            ->where('admin_id', $ownerId)
            ->where('client_order_id', $order['client_order_id'])
            ->first();

        if ($existing) {
            return $this->ack($existing, $order['client_order_id'], true);
        }

        $sale = DB::transaction(function () use ($order, $ownerId) {
            // The unique index is the final protection against concurrent replay.
            $existing = Sale::query()
                ->where('admin_id', $ownerId)
                ->where('client_order_id', $order['client_order_id'])
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            $tableId = $order['dining_table_id'] ?? null;
            if ($tableId) {
                DiningTable::query()
                    ->where('admin_id', $ownerId)
                    ->whereKey($tableId)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $cart = null;
            if (! empty($order['source_order_id'])) {
                $cart = Cart::query()
                    ->where('admin_id', $ownerId)
                    ->where('order_id', $order['source_order_id'])
                    ->with(['items.item.unit', 'items.item.recipe.ingredients.ingredientProduct'])
                    ->lockForUpdate()
                    ->first();
            }

            $customerId = $order['customer_id'] ?? null;
            if ($customerId && ! Customer::query()->where('admin_id', $ownerId)->whereKey($customerId)->exists()) {
                throw ValidationException::withMessages([
                    'customer_id' => 'The selected customer does not belong to the store.',
                ]);
            }

            if (! $customerId && ! empty($order['customer_name']) && ! empty($order['customer_phone'])) {
                $customerId = Customer::create([
                    'admin_id' => $ownerId,
                    'name' => $order['customer_name'],
                    'phone' => $order['customer_phone'],
                ])->id;
            }

            $employeeId = $order['employee_id'] ?? null;
            if ($employeeId && ! Employee::query()->where('admin_id', $ownerId)->whereKey($employeeId)->exists()) {
                throw ValidationException::withMessages([
                    'employee_id' => 'The selected employee does not belong to the store.',
                ]);
            }

            $saleItems = [];
            $subtotal = 0.0;

            foreach ($order['items'] as $line) {
                $product = Product::query()
                    ->where('admin_id', $ownerId)
                    ->whereKey($line['product_id'])
                    ->with(['unit', 'modifiers', 'recipe.ingredients.ingredientProduct'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $quantity = (float) $line['quantity'];
                $cartItem = $cart?->items->firstWhere('item_id', $product->id);
                $alreadyDeducted = (float) ($cartItem?->quantity ?? 0);

                if ($quantity > $alreadyDeducted) {
                    $this->deductRecipeStock->execute($product, $quantity - $alreadyDeducted);
                } elseif ($quantity < $alreadyDeducted) {
                    $this->deductRecipeStock->restore($product, $alreadyDeducted - $quantity);
                }

                [$modifiers, $unitPrice] = $this->resolveModifiers->execute(
                    $product,
                    $line['modifiers'] ?? []
                );

                $discount = (float) ($line['discount'] ?? 0);
                $total = max(0, ($unitPrice * $quantity) - $discount);
                $subtotal += $total;

                $saleItems[] = [
                    'admin_id' => $ownerId,
                    'item_id' => $product->id,
                    'item_name' => $product->name,
                    'buying_price' => $product->buying_price,
                    'unit_price' => $unitPrice,
                    'unit' => $product->unit?->short_name ?? 'pcs',
                    'quantity' => $quantity,
                    'total_price' => $total,
                    'note' => $line['notes'] ?? null,
                    'modifiers_json' => $modifiers ?: null,
                ];
            }

            // Items removed from a previously server-backed cart while offline
            // must have their provisional stock reservation restored.
            if ($cart) {
                $submittedProductIds = collect($order['items'])
                    ->pluck('product_id')
                    ->map(fn ($id) => (int) $id);

                foreach ($cart->items->whereNotIn('item_id', $submittedProductIds) as $removedItem) {
                    $this->deductRecipeStock->restore($removedItem->item, (float) $removedItem->quantity);
                }
            }

            $discount = (float) ($order['amounts']['discount'] ?? 0);
            $payable = max(0, $subtotal - $discount);
            $paid = min((float) $order['amounts']['paid'], $payable);

            $branchId = null;
            if ($tableId) {
                $branchId = DiningTable::query()
                    ->where('admin_id', $ownerId)
                    ->whereKey($tableId)
                    ->value('branch_id');
            }

            $sale = Sale::create([
                'admin_id' => $ownerId,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'dining_table_id' => $tableId,
                'employee_id' => $employeeId,
                'order_id' => generateOrderId(),
                'client_order_id' => $order['client_order_id'],
                'device_id' => $order['device_id'],
                'created_at_client' => ! empty($order['created_at_client'])
                    ? Carbon::parse($order['created_at_client'])
                    : null,
                'synced_at' => now(),
                'sale_date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'payable' => $payable,
                'paid' => $paid,
                'due' => $payable - $paid,
                'payment_option' => $order['amounts']['payment_type'],
                'note' => $order['note'] ?? null,
            ]);

            foreach ($saleItems as $saleItem) {
                $sale->items()->create($saleItem);
            }

            $cart?->items()->delete();
            $cart?->delete();

            if ($tableId) {
                DiningTable::query()
                    ->where('admin_id', $ownerId)
                    ->whereKey($tableId)
                    ->update(['status' => TableStatus::OCCUPIED]);
            }

            $sale->load(['items', 'table']);
            $this->createKitchenTicket->execute($sale);

            return $sale;
        }, 3);

        return $this->ack($sale, $order['client_order_id']);
    }

    private function ack(Sale $sale, string $clientOrderId, bool $duplicate = false): array
    {
        return [
            'client_order_id' => $clientOrderId,
            'status' => 'synced',
            'duplicate' => $duplicate,
            'server_sale_id' => $sale->id,
            'order_id' => $sale->order_id,
        ];
    }

    private function conflict(array $order, string $code, string $message, array $context = []): array
    {
        return [
            'client_order_id' => $order['client_order_id'],
            'status' => 'conflict',
            'code' => $code,
            'message' => $message,
            'context' => $context,
        ];
    }
}







