<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class DemoMode
{
    /**
     * Route names that stay writable while demo mode is on.
     * Auth/session + read-only lookups + the POS ordering flow
     * (so visitors can still take orders in the demo).
     */
    protected array $allowed = [
        'login',
        'login.post',
        'logout',
        'lang.switch',
        'admin.notifications.index',
        'admin.notifications.latest',
        'admin.notifications.readAll',
        'admin.branches.switch',
        'admin.gift-cards.verify',
        'admin.kds.updateStatus',
        'api.admin.pos.offline-sync',
        'api.seller.pos.offline-sync',
    ];

    protected array $allowedPrefixes = [
        'admin.pos.',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (! is_demo()) {
            return $next($request);
        }

        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && $this->isAllowed($routeName)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return errorResponse('This action is disabled in demo mode.', 403);
        }

        return redirect()->back()->with('error', 'This action is disabled in demo mode.');
    }

    protected function isAllowed(string $routeName): bool
    {
        if (in_array($routeName, $this->allowed, true)) {
            return true;
        }

        foreach ($this->allowedPrefixes as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
