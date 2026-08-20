<?php

namespace App\Providers;

use App\Services\Translation\DatabaseTranslationLoader;
use Illuminate\Support\ServiceProvider;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Реєструється ПІСЛЯ стандартного Illuminate\Translation\TranslationServiceProvider
     * (порядок у config/app.php має значення — цей провайдер має йти нижче
     * за системний), тож ми просто перезаписуємо вже зареєстрований
     * 'translation.loader' на власну обгортку навколо файлового.
     */
    public function register()
    {
        $this->app->extend('translation.loader', function ($fileLoader) {
            return new DatabaseTranslationLoader($fileLoader);
        });
    }
}