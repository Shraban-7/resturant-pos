<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\Seller\CustomerController;
use App\Http\Controllers\Seller\DashboardController;
use App\Http\Controllers\Seller\DiningTableController;
use App\Http\Controllers\Seller\EmployeeController;
use App\Http\Controllers\Seller\FloorController;
use App\Http\Controllers\Seller\KdsController;
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

Route::get('/', function () {
    if ($user = auth()->user()) {
        return redirect()->route("{$user->role}.dashboard");
    }

    return redirect()->route('login');
})->name('home');

Route::prefix('menu')->as('menu.')->group(function () {
    Route::get('/tracker/{token}', [MenuController::class, 'tracker'])->name('tracker');
    Route::get('/{table}', [MenuController::class, 'index'])->name('index');
    Route::post('/{table}/order', [MenuController::class, 'placeOrder'])->name('placeOrder');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'show']);
    Route::post('login', [LoginController::class, 'login'])->name('login');
    // Route::get('register', [RegisterController::class, 'show']);
    // Route::post('register', [RegisterController::class, 'register']);
});


Route::get('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('seller')->post(
    '/api/seller/pos/offline-sync',
    [OfflineSyncController::class, 'store']
)->name('seller.pos.offlineSync');

Route::middleware('seller')->prefix('seller')->as('seller.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

    Route::prefix('pos')->as('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::post('/item/add', [PosController::class, 'addItem'])->name('addItem');
        Route::post('/item/remove', [PosController::class, 'removeItem'])->name('removeItem');
        Route::post('/item/update-qty', [PosController::class, 'updateQuantity'])->name('updateQuantity');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
        Route::post('/hold', [PosController::class, 'holdOrder'])->name('hold');
        Route::post('/sale-update', [SaleController::class, 'saleUpdate'])->name('updateSale');
        Route::prefix('sale-item')->as('saleItem.')->group(function () {
            Route::post('/add', [SaleController::class, 'addItemToSale'])->name('add');
            Route::post('/remove', [SaleController::class, 'removeSaleItem'])->name('remove');
            Route::post('/update-qty', [SaleController::class, 'updateSaleItemQuantity'])->name('updateQuantity');
        });
    });

    Route::prefix('kds')->as('kds.')->group(function () {
        Route::get('/', [KdsController::class, 'index'])->name('index');
        Route::post('/{ticket}/status', [KdsController::class, 'updateStatus'])->name('updateStatus');
    });

    Route::prefix('sales')->as('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::get('{sale:order_id}/invoice', [SaleController::class, 'invoice'])->name('invoice');
        Route::get('{sale}/mark-paid', [SaleController::class, 'markPaid'])->name('mark-paid');
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
