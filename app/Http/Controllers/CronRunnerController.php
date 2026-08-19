<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;

class CronRunnerController extends Controller
{
    /**
     * Зовнішній тригер Laravel Scheduler — обхід зламаного системного
     * cron (CloudLinux CageFS) на хостингу. Викликається щохвилини
     * зовнішнім сервісом (cron-job.org чи подібним) замість системного
     * crontab. Захищено секретним токеном з .env — без нього 403.
     *
     * ТИМЧАСОВЕ рішення, поки хостинг-підтримка не полагодить cron.
     */
    public function run($token)
    {
        $expected = env('CRON_SECRET_TOKEN');

        if (empty($expected) || !hash_equals($expected, (string) $token)) {
            abort(403);
        }

        // Примусово фіксуємо таймзону саме тут: цей роут викликається через
        // HTTP (веб-SAPI), а web/CLI можуть мати різні php.ini з різним
        // date.timezone на shared-хостингу. Без цього schedule:run міг би
        // порахувати "зараз" неправильно й запустити команди не в той час.
        date_default_timezone_set(config('app.timezone'));

        Artisan::call('schedule:run');
        $output = Artisan::output();

        $debug = 'PHP timezone: ' . date_default_timezone_get()
            . ' | Laravel now(): ' . now()->toDateTimeString()
            . ' | PHP date(): ' . date('Y-m-d H:i:s');

        return response("OK\n{$debug}\n\n" . $output)->header('Content-Type', 'text/plain');
    }
}