<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ShopOwnerMiddleware
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_shop_owner) {
            return $next($request);
        }

        return redirect('/profile')->with('error', 'Доступ разрешен только владельцам магазинов.');
    }
}
