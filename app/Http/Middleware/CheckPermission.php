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
                return errorResponse('You are not authorized to access this resource.', 403);
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
                            ->with('error', 'Dashboard is not part of your role.');
                    }
                }

                abort(403, 'Dashboard is not part of your role.');
            }

            return redirect()->route('admin.dashboard')->with('error', 'You do not have permission for this section.');
        }

        return $next($request);
    }
}
