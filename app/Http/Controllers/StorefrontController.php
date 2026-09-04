<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;

use App\Enums\TableStatus;

use App\Enums\ProductType;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StorefrontController extends Controller
{
    /**
     * Resolve the storefront owner (single-tenant: first admin).
     * Returns null when no admin exists yet (fresh install).
     */
    public static function owner(): ?User
    {
        return User::storeOwner();
    }

    public function index(Request $request)
    {
        $owner = static::owner();

        if (! $owner) {
            return view('storefront.empty');
        }

        $business = BusinessSetting::where('user_id', $owner->id)->first();

        // Dynamic menu: auto-create a starter menu on first visit when none exists.
        static::ensureDemoMenu($owner);

        $categories = ProductCategory::query()
            ->where('admin_id', $owner->id)
            ->with(['products' => function ($q) use ($owner) {
                $q->where('admin_id', $owner->id)
                    ->sellable()
                    ->where('is_active', 1)
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->products->isNotEmpty());

        $popular = Product::query()
            ->where('admin_id', $owner->id)
            ->sellable()
            ->where('is_active', 1)
            ->orderByDesc('stock_out')
            ->take(8)
            ->get();

        $branches = Branch::query()
            ->where('admin_id', $owner->id)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $tables = DiningTable::query()
            ->where('admin_id', $owner->id)
            ->where('status', TableStatus::FREE)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        $mealSlots = Product::mealSlotHours();
        $currentSlot = Product::currentMealSlot();

        return view('storefront.index', compact('owner', 'business', 'categories', 'popular', 'branches', 'tables', 'mealSlots', 'currentSlot'));
    }

    /**
     * Seed the menu from database/seeders/data/products.json when the owner
     * has no categories/products yet. Idempotent: single source of truth
     * shared with ProductSeeder. Images are always NULL.
     */
    protected static function ensureDemoMenu(User $owner): void
    {
        if (ProductCategory::where('admin_id', $owner->id)->exists()
            || Product::where('admin_id', $owner->id)->exists()) {
            return;
        }

        $items = json_decode(
            file_get_contents(database_path(\Database\Seeders\ProductSeeder::JSON_PATH)),
            true
        );

        if (! is_array($items)) {
            return;
        }

        $branchIds = \App\Models\Branch::where('admin_id', $owner->id)->orderBy('id')->pluck('id')->all();

        foreach (array_values($items) as $index => $item) {
            $category = ProductCategory::firstOrCreate(
                ['admin_id' => $owner->id, 'name' => $item['category']],
                ['admin_id' => $owner->id, 'name' => $item['category']]
            );

            $unit = \App\Models\ProductUnit::firstOrCreate(
                ['name' => $item['unit']],
                ['short_name' => strtolower(substr($item['unit'], 0, 3))]
            );

            $branchId = ($branchIds && $index % 6 === 5)
                ? $branchIds[(int) ($index / 6) % count($branchIds)]
                : null;

            $mealTimes = ($item['meal'] ?? 'all') === 'all' ? null : array_values($item['meal']);

            $product = Product::firstOrCreate(
                ['admin_id' => $owner->id, 'name' => $item['name']],
                [
                    'admin_id' => $owner->id,
                    'branch_id' => $branchId,
                    'type' => $item['type'] ?? ProductType::DISH,
                    'meal_times' => $mealTimes,
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'name' => $item['name'],
                    'name_bn' => $item['name_bn'] ?? null,
                    'buying_price' => $item['buying_price'],
                    'selling_price' => $item['selling_price'],
                    'stock_in' => $item['stock_in'],
                    'stock_out' => 0,
                    'image' => null,
                    'is_active' => 1,
                ]
            );

            \App\Models\ProductStock::firstOrCreate(
                ['product_id' => $product->id, 'admin_id' => $owner->id, 'type' => 'increment'],
                [
                    'product_id' => $product->id,
                    'admin_id' => $owner->id,
                    'type' => 'increment',
                    'quantity' => $product->stock_in,
                    'old_stock' => 0,
                    'new_stock' => $product->stock_in,
                    'buying_price' => $product->buying_price,
                    'selling_price' => $product->selling_price,
                ]
            );
        }
    }

    /**
     * Guest table reservation (always created as pending for staff confirmation).
     */
    public function reserve(Request $request)
    {
        $owner = static::owner();
        abort_unless($owner, 503, 'Store is not set up yet.');

        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'guest_count' => 'required|integer|min:1|max:100',
            'reservation_time' => 'required|date|after:now',
            'branch_id' => [
                'nullable',
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('admin_id', $owner->id)),
            ],
            'table_id' => [
                'required',
                Rule::exists('dining_tables', 'id')->where(fn ($q) => $q->where('admin_id', $owner->id)),
            ],
            'notes' => 'nullable|string|max:1000',
        ]);

        $table = DiningTable::query()
            ->where('admin_id', $owner->id)
            ->whereKey($data['table_id'])
            ->firstOrFail();

        if ($conflict = Reservation::conflictingBooking((int) $table->id, $data['reservation_time'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'reservation_time' => Reservation::conflictMessage($conflict),
            ]);
        }

        $reservation = Reservation::create([
            'admin_id' => $owner->id,
            'branch_id' => $data['branch_id'] ?? $table->branch_id,
            'table_id' => $table->id,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'guest_count' => $data['guest_count'],
            'reservation_time' => $data['reservation_time'],
            'notes' => $data['notes'] ?? null,
            'status' => ReservationStatus::PENDING,
        ]);

        // Persist for the bell dropdown + live ping to every staff screen.
        \App\Models\StaffNotification::notify(
            $owner->id,
            \App\Models\StaffNotification::TYPE_RESERVATION,
            "New reservation: {$reservation->customer_name}",
            "{$reservation->guest_count} guests · " . ($table->name ?? '') . ' · ' . \Carbon\Carbon::parse($reservation->reservation_time)->format('d M, h:i A'),
            ['reservation_id' => $reservation->id]
        );

        event(new \App\Events\ReservationPlaced($reservation));

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'status' => true,
                'message' => 'Table reservation requested. We will confirm shortly.',
                'reservation_id' => $reservation->id,
            ]);
        }

        return redirect()->route('storefront.index', ['reserved' => 1, '#reservation' => ''])
            ->with('success', 'Table reservation requested. We will confirm shortly.');
    }
}








