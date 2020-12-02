<?php

namespace App\Providers;

use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class BladeDirectivesServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerShopUserDirective();
    }

    /**
     * Добавляет директиву @is_shop_owner
     */
    private function registerShopUserDirective() {
        Blade::if('is_shop_owner', function (User $user = null) {
            $_user = $user ?? Auth::user();
            return (bool)$_user->is_shop_owner;
        });
    }
}
