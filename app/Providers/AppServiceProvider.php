<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useTailwind();

        // Site name comes from Admin → Settings (Business Name), not .env.
        try {
            if ($name = store_business()?->name) {
                config(['app.name' => $name]);
            }
        } catch (\Throwable $e) {
            // Fresh install without tables yet — keep .env default.
        }

        Broadcast::routes(['middleware' => ['web', 'auth']]);

        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return true;
            }

            return null;
        });

        $permissions = array_keys(config('permissions', []));
        if (empty($permissions)) {
            // Fallback when config is cached without permissions.php.
            $permissions = ['dashboard','pos','products','stocks','sales','kds','floors','branches','reservations','loyalty','gift-cards','customers','employees','reports','settings'];
        }

        foreach ($permissions as $permission) {
            Gate::define($permission, fn ($user) => $user && $user->hasPermission($permission));
        }
    }
}
