<?php

namespace App\Console\Commands;

use App\DailyReport;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GenerateDailyReport extends Command
{
    /**
     * php artisan report:daily
     *
     * Рахує за вказану (або вчорашню за замовчуванням) дату:
     *  - скільки оголошень розміщено (усі, не тільки товари магазинів),
     *  - скільки нових користувачів зареєстровано,
     *  - скільки нових МАГАЗИНІВ з'явилось — тобто користувачів, у яких
     *    САМЕ ЦЬОГО дня була створена їхня ПЕРША товарна позиція
     *    (is_product=1); просто нові товари вже наявних магазинів
     *    у цю цифру не потрапляють.
     *
     * Дані про семантику/контент-план (кластери, теми) сюди НЕ пише —
     * їх заповнює сама content:build-plan одразу після свого запуску.
     */
    protected $signature = 'report:daily {--date=}';

    protected $description = 'Формує щоденний звіт: оголошення, нові користувачі, нові магазини за вказану дату';

    public function handle()
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->toDateString()
            : now()->subDay()->toDateString();

        $adsCount = DB::table('ads')->whereDate('created_at', $date)->count();

        $newUsersCount = DB::table('users')->whereDate('created_at', $date)->count();

        // Новий магазин = користувач, чия НАЙРАНІША товарна позиція
        // (is_product=1) припадає саме на цю дату.
        $newShopsCount = DB::table('ads')
            ->where('is_product', 1)
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('DATE(MIN(created_at)) = ?', [$date])
            ->get()
            ->count();

        DailyReport::updateOrCreate(
            ['date' => $date],
            [
                'ads_count' => $adsCount,
                'new_users_count' => $newUsersCount,
                'new_shops_count' => $newShopsCount,
            ]
        );

        $this->info("Звіт за {$date}: оголошень={$adsCount}, нових користувачів={$newUsersCount}, нових магазинів={$newShopsCount}");

        return 0;
    }
}
