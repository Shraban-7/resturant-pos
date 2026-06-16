<?php

use App\Http\Controllers\Supplier\CustomerController;
use App\Http\Controllers\Supplier\DashboardController;
use App\Http\Controllers\Supplier\ProductController;
use App\Http\Controllers\Supplier\ReportController;
use App\Http\Controllers\Supplier\SettingController;
use App\Http\Controllers\Supplier\StockController;
use App\Http\Controllers\Supplier\SupplyController;
use Illuminate\Support\Facades\Route;

Route::middleware('supplier')->prefix('supplier')->as('supplier.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('products')->as('products.')->group(function () {

        Route::get('/', [ProductController::class, 'index'])->name('index');
        Route::get('/create', [ProductController::class, 'create'])->name('create');
        Route::post('/store', [ProductController::class, 'store'])->name('store');
        Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
        Route::post('/{product}/update', [ProductController::class, 'update'])->name('update');
        Route::get('/{product}/delete', [ProductController::class, 'delete'])->name('delete');
    });

    Route::prefix('stocks')->as('stocks.')->group(function () {

        Route::get('/', [StockController::class, 'index'])->name('index');
        Route::get('/create', [StockController::class, 'create'])->name('create');
        Route::post('/update', [StockController::class, 'update'])->name('update');
    });

    Route::prefix('supply')->as('supply.')->group(function () {

        Route::get('/', [SupplyController::class, 'index'])->name('index');
        Route::post('/item/add', [SupplyController::class, 'addItem'])->name('addItem');
        Route::post('/item/remove', [SupplyController::class, 'removeItem'])->name('removeItem');
        Route::post('/item/update-qty', [SupplyController::class, 'updateQuantity'])->name('updateQuantity');
        Route::post('/checkout', [SupplyController::class, 'checkout'])->name('checkout');
    });

    Route::get('/invoices', [SupplyController::class, 'invoices'])->name('invoices');
    Route::get('/report', [ReportController::class, 'index'])->name('report.index');

    Route::prefix('sales')->as('sales.')->group(function () {
        Route::get('{sale:order_id}/invoice', [SupplyController::class, 'invoice'])->name('invoice');
        Route::get('{sale}/mark-paid', [SupplyController::class, 'markPaid'])->name('mark-paid');
    });


    Route::prefix('customers')->as('customers.')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('index');
    });

    Route::prefix('settings')->as('settings.')->group(function () {
        Route::match(['get', 'post'], 'business', [SettingController::class, 'index'])->name('index');
    });
});
