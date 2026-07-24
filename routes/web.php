<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\Seller\CustomerController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\DeliveryController;
use App\Http\Controllers\Seller\DiningTableController;
use App\Http\Controllers\Seller\EmployeeController;
use App\Http\Controllers\Seller\FloorController;
use App\Http\Controllers\Seller\GiftCardController;
use App\Http\Controllers\Seller\KdsController;
use App\Http\Controllers\Seller\LoyaltyController;
use App\Http\Controllers\Seller\OfflineSyncController;
use App\Http\Controllers\Seller\PosController;
use App\Http\Controllers\Seller\ProductController;
use App\Http\Controllers\Seller\ProductModifierController;
use App\Http\Controllers\Seller\RecipeController;
use App\Http\Controllers\Seller\ReportController;
use App\Http\Controllers\Seller\ReservationController;
use App\Http\Controllers\Seller\SaleController;
use App\Http\Controllers\Seller\SettingController;
use App\Http\Controllers\Seller\StockController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if ($user = auth()->user()) {
        return redirect()->route("{$user->role}.dashboard");
    }

    return redirect()->route('login');
})->name('home');

// Digital QR Code Menu & Public Tracking
Route::get('/menu/tracker/{token}', [MenuController::class, 'tracker'])->name('menu.tracker');
Route::get('/menu/{table}', [MenuController::class, 'index'])->name('menu.index');
Route::post('/menu/{table}/order', [MenuController::class, 'placeOrder'])->name('menu.placeOrder');
Route::get('/order-status/{order}', [OrderStatusController::class, 'show'])->name('order-status.show');

// Storefront Online Ordering
Route::get('/online-order', [OnlineOrderController::class, 'index'])->name('online.order.index');
Route::post('/online-order/checkout', [OnlineOrderController::class, 'checkout'])->name('online.order.checkout');

// Guest authentication routes...
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Seller panel routes...
Route::middleware(['auth', 'seller'])->prefix('seller')->as('seller.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('pos')->as('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/cds', [PosController::class, 'cds'])->name('cds');
        Route::post('/add-item', [PosController::class, 'addItem'])->name('add_item');
        Route::post('/remove-item', [PosController::class, 'removeItem'])->name('remove_item');
        Route::post('/update-quantity', [PosController::class, 'updateQuantity'])->name('update_quantity');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/hold-order', [PosController::class, 'holdOrder'])->name('hold_order');
    });

    Route::get('/kds', [KdsController::class, 'index'])->name('kds.index');
    Route::post('/kds/tickets/{ticket}/status', [KdsController::class, 'updateStatus'])->name('kds.updateStatus');

    Route::prefix('sales')->as('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('/invoice/{sale}', [SaleController::class, 'invoice'])->name('invoice');
    });

    Route::prefix('products')->as('products.')->group(function () {
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

    Route::prefix('deliveries')->as('deliveries.')->group(function () {
        Route::get('/', [DeliveryController::class, 'index'])->name('index');
        Route::post('/{delivery}/status', [DeliveryController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('stocks')->as('stocks.')->group(function () {
        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/update', [StockController::class, 'update'])->name('update');
    });

    Route::get('/report', [ReportController::class, 'index'])->name('report.index');

    Route::prefix('customers')->as('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
        Route::post('/store', [CustomerController::class, 'store'])->name('store');
    });

    Route::prefix('settings')->as('settings.')->group(function () {
        Route::match(['get', 'post'], 'business', [SettingController::class, 'index'])->name('index');
    });

    Route::prefix('dining-tables')->as('diningTables.')->group(function () {
        Route::get('/', [DiningTableController::class, 'index'])->name('index');
        Route::get('/floor-map', [DiningTableController::class, 'floorMap'])->name('floorMap');
        Route::post('/positions', [DiningTableController::class, 'savePositions'])->name('savePositions');
        Route::post('/store', [DiningTableController::class, 'store'])->name('store');
        Route::post('/{table}/update', [DiningTableController::class, 'update'])->name('update');
        Route::delete('/{table}/destroy', [DiningTableController::class, 'destroy'])->name('destroy');
        Route::get('/{table}/qr-card', [DiningTableController::class, 'qrCard'])->name('qrCard');
        Route::get('/{table}/qr.svg', [DiningTableController::class, 'qrSvg'])->name('qrSvg');
    });

    Route::prefix('employees')->as('employees.')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('index');
        Route::post('/store', [EmployeeController::class, 'store'])->name('store');
        Route::post('/{employee}/update', [EmployeeController::class, 'update'])->name('update');
    });

});
