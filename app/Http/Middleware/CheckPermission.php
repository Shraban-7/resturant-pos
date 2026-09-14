<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return errorResponse('Access denied.', 403);
            }

            // Avoid redirect loop when the denied page IS the dashboard:
            // send the user to their first permitted section instead.
            if ($permission === 'dashboard') {
                foreach (['pos', 'kds', 'sales'] as $fallback) {
                    if ($user && $user->hasPermission($fallback)) {
                        $map = [
                            'pos' => 'admin.pos.index',
                            'kds' => 'admin.kds.index',
                            'sales' => 'admin.sales.index',
                        ];

                        return redirect()->route($map[$fallback])
                            ->with('error', 'Access denied.');
                    }
                }

                abort(403, 'Access denied.');
            }

            return redirect()->route('admin.dashboard')->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
