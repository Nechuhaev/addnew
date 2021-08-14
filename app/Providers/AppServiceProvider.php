<?php

namespace App\Providers;

use App\Page;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('front.layout', function ($view) {
            $pages = Page::orderBy('sort_order', 'asc')->get();
            $view->with('pages', $pages);

        });
        Schema::defaultStringLength(191);
    }
}
