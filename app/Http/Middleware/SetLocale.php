<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Встановлює локаль застосунку залежно від того, якою мовною групою
     * маршрутів (uk — за замовчуванням, ru — з префіксом /ru) обслуговується
     * поточний запит. Використовується як параметризований middleware:
     * ->middleware('setlocale:uk') / ->middleware('setlocale:ru')
     */
    public function handle($request, Closure $next, string $locale)
    {
        App::setLocale($locale);

        return $next($request);
    }
}