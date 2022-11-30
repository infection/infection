<?php

namespace _HumbugBoxb47773b41c19\App\Http\Middleware;

use Closure;
use _HumbugBoxb47773b41c19\Illuminate\Support\Facades\Auth;
class RedirectIfAuthenticated
{
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect('/home');
        }
        return $next($request);
    }
}
