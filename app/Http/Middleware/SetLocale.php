<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public const ALLOWED = ['en', 'bn'];

    public function handle(Request $request, Closure $next)
    {
        $locale = $request->session()->get('locale');

        if (! in_array($locale, self::ALLOWED, true)) {
            $locale = config('app.locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
