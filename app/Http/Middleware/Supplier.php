<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Supplier
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!is_supplier()) {
            return redirect()->route('login')->with('error', 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
