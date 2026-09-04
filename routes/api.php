<?php

use App\Http\Controllers\Admin\OfflineSyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['web', 'auth', 'admin'])->prefix('admin')->group(function () {
    Route::post('/pos/offline-sync', [OfflineSyncController::class, 'store'])
        ->name('api.admin.pos.offline-sync');
});

// Legacy seller API path.
Route::middleware(['web', 'auth', 'admin'])->prefix('seller')->group(function () {
    Route::post('/pos/offline-sync', [OfflineSyncController::class, 'store'])
        ->name('api.seller.pos.offline-sync');
});


