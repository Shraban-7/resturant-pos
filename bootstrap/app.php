<?php

use App\Http\Middleware\Seller;
use App\Http\Middleware\Supplier;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/supplier.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'seller' => Seller::class,
            'supplier' => Supplier::class,
        ]);

        // Background Sync cannot read the page's CSRF token. The endpoint still
        // requires the authenticated seller session and validates every payload.
        $middleware->validateCsrfTokens(except: [
            'api/seller/pos/offline-sync',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
