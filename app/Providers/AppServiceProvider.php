<?php

namespace App\Providers;

use App\AdTag;
use App\Observers\AdTagObserver;
use App\BlockedEmail;
use App\Page;
use App\StopWord;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
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

        // Правила для форм оголошень і реєстрації (раніше оголошувались
        // заново всередині кожного методу контролера).
        Validator::extend('not_from_block_list', function ($attribute, $value, $parameters) {
            $mailbox = stristr($value, '@');
            foreach (BlockedEmail::all() as $email) {
                if ('@' . $email->mailbox === $mailbox) {
                    return false;
                }
            }
            return true;
        }, "Почтовые адреса этого сервиса не поддерживается нашим сайтом. Пожалуйста, воспользуйтесь другим почтовым сервисом.");

        Validator::extend('not_stop_word', function ($attribute, $value, $parameters) {
            return StopWord::findMatchIn($value) === null;
        }, "Текст содержит запрещенное слово и не может быть опубликован.");
    }
}
