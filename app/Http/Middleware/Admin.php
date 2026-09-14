<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Single panel: admin + employee both allowed; fine-grained checks via `permission` middleware.
        if (! is_admin() && ! is_employee()) {
            // Offline sync / fetch clients must receive JSON, not a login redirect.
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = Auth::check() ? 403 : 401;

                return errorResponse(
                    Auth::check()
                        ? 'Access denied.'
                        : 'Login required.',
                    $status
                );
            }

            return redirect()->route('login')->with('error', 'Access denied.');
        }

        return $next($request);
    }
}

