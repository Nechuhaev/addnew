<?php

namespace App\Providers;

use App\AdTag;
use App\Observers\AdTagObserver;
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

        // Одразу генерує унікальний SEO-текст для щойно створеного тега
        // (наприклад, коли відвідувач додає оголошення з новою міткою).
        AdTag::observe(AdTagObserver::class);
    }
}
