<?php

namespace App\Providers;

use App\Localization\Localization;
use Illuminate\Support\ServiceProvider;

class LocalizationServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(Localization::class, function() {
            return Localization::getInstance();
        });
    }

    public function boot()
    {

    }
}
