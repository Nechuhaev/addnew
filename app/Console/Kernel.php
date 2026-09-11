<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            foreach (Storage::directories('ads') as $directory) {
                if(time() - (int)basename($directory) > 604800) {
                    Storage::deleteDirectory($directory);
                }
            }
        })->mondays()->at('17:00');

        $schedule->command('sitemap:update')->fridays()->at('17:00')->runInBackground();
    // Раз на тиждень (понеділок, 05:00) — поповнює семантику й контент-план,
    // якщо запланованих тем лишилось мало (MIN_PLANNED_ARTICLES).
    $schedule->command('content:build-plan')
        ->weeklyOn(1, '05:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/content-build-plan.log'));

    // Щодня о 09:00 — публікує 1 статтю (або POSTS_PER_RUN, якщо змінили в .env)
    $schedule->command('content:publish')
        ->dailyAt('09:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/content-publish.log'));

    // Раз на тиждень (четвер, 06:00) — оновлює старі малопереглядові статті.
    $schedule->command('content:refresh')
        ->weeklyOn(4, '06:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/content-refresh.log'));

    // Раз на день — перевіряє статус індексації статей у Google Search Console.
    $schedule->command('content:check-index')
        ->dailyAt('10:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/content-check-index.log'));

    // Safety-net: sitemap і так перегенеровується автоматично всередині
    // content:publish/content:refresh, цей запис — просто підстраховка.
    $schedule->command('content:sitemap')
        ->dailyAt('08:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/content-sitemap.log'));
        
        // Щогодини — перевіряє ціну/наявність товарів у джерелах (competitor_url/url).
    // Ліміт підвищений порівняно з дефолтом, бо товарів багато тисяч —
    // інакше повний цикл перевірки займе непристойно довго.
    $schedule->command('products:monitor-prices --limit=150')
        ->hourly()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/products-monitor-prices.log'));

    // Раз на день — SEO-оптимізація карток товарів через Claude API.
    $schedule->command('products:seo-optimize')
        ->weeklyOn(2, '11:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/products-seo-optimize.log'));
        
        // Щогодини — перевіряє доступність зображень оголошень, замінює
    // биті (403/404 на S3) на локальний placeholder. mod_pagespeed
    // сам підміняє <img> ще до браузера, тому фікс має бути в БД.
    $schedule->command('ads:check-images --limit=100')
        ->hourly()
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/ads-check-images.log'));
        
        // Раз на день — SEO-оптимізація назви й опису звичайних оголошень.
    $schedule->command('ads:seo-optimize')
        ->weeklyOn(3, '12:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/ads-seo-optimize.log'));
        
            // Раз на день — звіряє email магазинів з тим, що реально вказано
    // на їхніх сайтах, автоматично оновлює при розбіжності.
    $schedule->command('shops:check-email --limit=50')
        ->weeklyOn(5, '13:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/shops-check-email.log'));

            // Раз на день — SEO-генерація тегів оголошень (10 за раз, найпопулярніші спершу).
    $schedule->command('tags:seo-optimize --limit=10')
        ->dailyAt('14:00')
        ->withoutOverlapping()
        ->appendOutputTo(storage_path('logs/tags-seo-optimize.log'));

    }
    

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
