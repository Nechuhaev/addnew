<?php

namespace App\Http\Middleware;

use App\AdCountry;
use App\Localization\Localization;
use Closure;

class SetupLocalization
{
    public $localization;

    public function __construct(Localization $localization)
    {
        $this->localization = $localization;
    }

    public function handle($request, Closure $next)
    {
        // По умолчанию у нас Украина
        $default_country_id = 62;
        $country = AdCountry::find($default_country_id);
        if ($country) {
            $this->localization->setCountry($country);
        }
        return $next($request);
    }
}
