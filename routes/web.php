<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiningTableController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\FloorController;
use App\Http\Controllers\Admin\GiftCardController;
use App\Http\Controllers\Admin\KdsController;
use App\Http\Controllers\Admin\LoyaltyController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\OfflineSyncController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductModifierController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->user()) {
        return redirect()->route('admin.dashboard');
    }

    return app(App\Http\Controllers\StorefrontController::class)->index(request());
})->name('home');

// Language switcher (English / Bangla)
Route::get('/lang/{locale}', function (string $locale) {
    if (in_array($locale, App\Http\Middleware\SetLocale::ALLOWED, true)) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('lang.switch');

// Public storefront (products + table reservation)
Route::get('/store', [App\Http\Controllers\StorefrontController::class, 'index'])->name('storefront.index');
Route::post('/reserve', [App\Http\Controllers\StorefrontController::class, 'reserve'])->name('storefront.reserve');

// Digital QR Code Menu & Public Tracking
Route::get('/menu/tracker/{token}', [MenuController::class, 'tracker'])->name('menu.tracker');
Route::get('/menu/{table}', [MenuController::class, 'index'])->name('menu.index');
Route::post('/menu/{table}/order', [MenuController::class, 'placeOrder'])->name('menu.placeOrder');
Route::get('/order-status/{order}', [OrderStatusController::class, 'show'])->name('order-status.show');

// Guest authentication routes...
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin panel routes (single panel: admin + employees via RBAC)...
Route::middleware(['auth', 'admin'])->prefix('admin')->as('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('permission:dashboard');

    Route::prefix('notifications')->as('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/latest', [NotificationController::class, 'latest'])->name('latest');
        Route::post('/read-all', [NotificationController::class, 'readAll'])->name('readAll');
    });

    Route::prefix('pos')->as('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/add-item', [PosController::class, 'addItem'])->name('addItem');
        Route::post('/remove-item', [PosController::class, 'removeItem'])->name('removeItem');
        Route::post('/update-quantity', [PosController::class, 'updateQuantity'])->name('updateQuantity');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/hold-order', [PosController::class, 'holdOrder'])->name('hold');
        Route::post('/update-sale', [SaleController::class, 'saleUpdate'])->name('updateSale');

        Route::prefix('sale-items')->as('saleItem.')->group(function () {
            Route::post('/add', [SaleController::class, 'addItemToSale'])->name('add');
            Route::post('/remove', [SaleController::class, 'removeSaleItem'])->name('remove');
            Route::post('/update-quantity', [SaleController::class, 'updateSaleItemQuantity'])->name('updateQuantity');
        });
    });

    Route::get('/kds', [KdsController::class, 'index'])->name('kds.index');
    Route::post('/kds/tickets/{ticket}/status', [KdsController::class, 'updateStatus'])->name('kds.updateStatus');

    Route::prefix('sales')->as('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/invoice/{sale:order_id}', [SaleController::class, 'invoice'])->name('invoice');
        Route::get('/{sale}/mark-paid', [SaleController::class, 'markPaid'])->name('mark-paid');
    });

    Route::prefix('suppliers')->as('suppliers.')->middleware('permission:products')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('index');
        Route::post('/', [SupplierController::class, 'store'])->name('store');
        Route::put('/{supplier}', [SupplierController::class, 'update'])->name('update');
        Route::delete('/{supplier}', [SupplierController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('purchases')->as('purchases.')->middleware('permission:products')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::post('/', [PurchaseController::class, 'store'])->name('store');
    });

    Route::prefix('products')->as('products.')->middleware('permission:products')->group(function () {
        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/store', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::post('/{product}/update', [ProductController::class, 'update'])->name('update');
        Route::get('/{product}/delete', [ProductController::class, 'delete'])->name('delete');

        Route::prefix('{product}/modifiers')->as('modifiers.')->group(function () {
            Route::get('/', [ProductModifierController::class, 'index'])->name('index');
            Route::post('/', [ProductModifierController::class, 'store'])->name('store');
            Route::put('/{productModifier}', [ProductModifierController::class, 'update'])->name('update');
            Route::delete('/{productModifier}', [ProductModifierController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('{product}/recipe')->as('recipe.')->group(function () {
            Route::get('/', [RecipeController::class, 'edit'])->name('edit');
            Route::put('/', [RecipeController::class, 'update'])->name('update');
            Route::delete('/', [RecipeController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('floors')->as('floors.')->group(function () {
        Route::get('/', [FloorController::class, 'index'])->name('index');
        Route::post('/', [FloorController::class, 'store'])->name('store');
        Route::put('/{floor}', [FloorController::class, 'update'])->name('update');
        Route::delete('/{floor}', [FloorController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('branches')->as('branches.')->group(function () {
        Route::get('/', [BranchController::class, 'index'])->name('index');
        Route::post('/', [BranchController::class, 'store'])->name('store');
        Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
        Route::delete('/{branch}', [BranchController::class, 'destroy'])->name('destroy');
        Route::post('/switch', [BranchController::class, 'switch'])->name('switch');
    });

    Route::prefix('reservations')->as('reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
        Route::put('/{reservation}', [ReservationController::class, 'update'])->name('update');
        Route::delete('/{reservation}', [ReservationController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('loyalty')->as('loyalty.')->group(function () {
        Route::get('/', [LoyaltyController::class, 'index'])->name('index');
        Route::post('/adjust', [LoyaltyController::class, 'adjust'])->name('adjust');
    });

    Route::prefix('gift-cards')->as('gift-cards.')->group(function () {
        Route::get('/', [GiftCardController::class, 'index'])->name('index');
        Route::post('/', [GiftCardController::class, 'store'])->name('store');
        Route::post('/verify', [GiftCardController::class, 'verify'])->name('verify');
    });

    Route::prefix('stocks')->as('stocks.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/update', [StockController::class, 'update'])->name('update');
    });

    Route::get('/report', [ReportController::class, 'index'])->name('report.index')->middleware('permission:reports');

    Route::prefix('customers')->as('customers.')->middleware('permission:customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/store', [CustomerController::class, 'store'])->name('store');
    });

    Route::prefix('settings')->as('settings.')->middleware('permission:settings')->group(function () {
        Route::match(['get', 'post'], 'business', [SettingController::class, 'index'])->name('index');
    });

    Route::prefix('dining-tables')->as('diningTables.')->middleware('permission:floors')->group(function () {
        Route::get('/', [DiningTableController::class, 'index'])->name('index');
        Route::get('/floor-map', [DiningTableController::class, 'floorMap'])->name('floorMap');
        Route::post('/positions', [DiningTableController::class, 'savePositions'])->name('savePositions');
        Route::post('/store', [DiningTableController::class, 'store'])->name('store');
        Route::post('/{table}/update', [DiningTableController::class, 'update'])->name('update');
        Route::delete('/{table}/destroy', [DiningTableController::class, 'destroy'])->name('destroy');
        Route::get('/{table}/qr-card', [DiningTableController::class, 'qrCard'])->name('qrCard');
        Route::get('/{table}/qr.svg', [DiningTableController::class, 'qrSvg'])->name('qrSvg');
    });

    Route::prefix('employees')->as('employees.')->middleware('permission:employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::post('/store', [EmployeeController::class, 'store'])->name('store');
        Route::post('/{employee}/update', [EmployeeController::class, 'update'])->name('update');
    });

});

// Legacy seller URLs redirect to admin panel (any method: GET/POST/PUT/DELETE).
Route::prefix('seller')->group(function () {
    Route::any('/{any?}', function () {
        $path = str_replace('/seller', '/admin', request()->path());
        $query = request()->getQueryString() ? '?'.request()->getQueryString() : '';

        return redirect('/'.$path.$query, 301);
    })->where('any', '.*');
});


