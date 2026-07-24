<?php

use App\Http\Controllers\Seller\OfflineSyncController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['web', 'auth', 'seller'])->prefix('seller')->group(function () {
    Route::post('/pos/offline-sync', [OfflineSyncController::class, 'store'])
        ->name('api.seller.pos.offline-sync');
});
