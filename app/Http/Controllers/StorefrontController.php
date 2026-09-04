<?php

namespace App\Http\Controllers;

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
        return User::admin()->orderBy('id')->first();
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
            ->where('seller_id', $owner->id)
            ->with(['products' => function ($q) use ($owner) {
                $q->where('seller_id', $owner->id)
                    ->where('is_active', 1)
                    ->orderBy('name');
            }])
            ->orderBy('name')
            ->get()
            ->filter(fn ($c) => $c->products->isNotEmpty());

        $popular = Product::query()
            ->where('seller_id', $owner->id)
            ->where('is_active', 1)
            ->orderByDesc('stock_out')
            ->take(8)
            ->get();

        $branches = Branch::query()
            ->where('seller_id', $owner->id)
            ->where('is_active', true)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();

        $tables = DiningTable::query()
            ->where('seller_id', $owner->id)
            ->where('status', DiningTable::FREE)
            ->orderBy('name')
            ->get(['id', 'name', 'branch_id']);

        return view('storefront.index', compact('owner', 'business', 'categories', 'popular', 'branches', 'tables'));
    }

    /**
     * Seed a starter menu when the owner has no categories/products yet.
     * Idempotent: runs only when both tables are empty for this seller.
     */
    protected static function ensureDemoMenu(User $owner): void
    {
        $hasCategories = ProductCategory::where('seller_id', $owner->id)->exists();
        $hasProducts = Product::where('seller_id', $owner->id)->exists();

        if ($hasCategories || $hasProducts) {
            return;
        }

        $unit = \App\Models\ProductUnit::firstOrCreate(
            ['name' => 'PIECES'],
            ['short_name' => 'pcs']
        );

        $menu = [
            'Starters' => [
                ['Chicken Wings', 180, 250], ['Spring Rolls', 120, 180],
                ['French Fries', 100, 150], ['Chicken Soup', 140, 200],
            ],
            'Main Course' => [
                ['Chicken Biryani', 180, 260], ['Beef Tehari', 200, 280],
                ['Fried Rice & Chicken', 220, 320], ['Pasta Alfredo', 250, 350],
            ],
            'Burgers & Fast Food' => [
                ['Chicken Burger', 150, 220], ['Beef Burger', 200, 290],
                ['Club Sandwich', 160, 230], ['Shawarma', 130, 190],
            ],
            'Drinks' => [
                ['Fresh Juice', 80, 120], ['Cold Coffee', 120, 170],
                ['Soft Drink', 40, 60], ['Mineral Water', 20, 30],
            ],
            'Desserts' => [
                ['Gulab Jamun', 60, 100], ['Ice Cream', 80, 120],
                ['Firni', 70, 110], ['Brownie', 120, 170],
            ],
        ];

        foreach ($menu as $categoryName => $items) {
            $category = ProductCategory::create([
                'seller_id' => $owner->id,
                'name' => $categoryName,
            ]);

            foreach ($items as [$name, $buying, $selling]) {
                $product = Product::create([
                    'seller_id' => $owner->id,
                    'category_id' => $category->id,
                    'unit_id' => $unit->id,
                    'name' => $name,
                    'buying_price' => $buying,
                    'selling_price' => $selling,
                    'stock_in' => 100,
                    'stock_out' => 0,
                    'image' => 'images/products/' . str_slug($categoryName) . '.jpg',
                    'is_active' => 1,
                ]);

                \App\Models\ProductStock::create([
                    'product_id' => $product->id,
                    'seller_id' => $owner->id,
                    'type' => 'increment',
                    'quantity' => 100,
                    'old_stock' => 0,
                    'new_stock' => 100,
                    'buying_price' => $buying,
                    'selling_price' => $selling,
                ]);
            }
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
                Rule::exists('branches', 'id')->where(fn ($q) => $q->where('seller_id', $owner->id)),
            ],
            'table_id' => [
                'required',
                Rule::exists('dining_tables', 'id')->where(fn ($q) => $q->where('seller_id', $owner->id)),
            ],
            'notes' => 'nullable|string|max:1000',
        ]);

        $table = DiningTable::query()
            ->where('seller_id', $owner->id)
            ->whereKey($data['table_id'])
            ->firstOrFail();

        $reservation = Reservation::create([
            'seller_id' => $owner->id,
            'branch_id' => $data['branch_id'] ?? $table->branch_id,
            'table_id' => $table->id,
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'guest_count' => $data['guest_count'],
            'reservation_time' => $data['reservation_time'],
            'notes' => $data['notes'] ?? null,
            'status' => Reservation::PENDING,
        ]);

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
