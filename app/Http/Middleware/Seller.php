<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Seller
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (! is_seller()) {
            // Offline sync / fetch clients must receive JSON, not a login redirect.
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = Auth::check() ? 403 : 401;

                return errorResponse(
                    Auth::check()
                        ? 'You are not authorized to access this resource.'
                        : 'Authentication required.',
                    $status
                );
            }

            return redirect()->route('login')->with('error', 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
