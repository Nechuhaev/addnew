<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;

class CronRunnerController extends Controller
{
    /** Скільки пропущених хвилин наздоганяти за один виклик */
    const MAX_CATCHUP_MINUTES = 60;
    const LAST_MINUTE_KEY = 'cron_runner_last_minute';

    /**
     * Зовнішній тригер Laravel Scheduler — обхід зламаного системного
     * cron (CloudLinux CageFS) на хостингу. Викликається зовнішнім сервісом
     * (GitHub Actions кожні 5 хв, cron-job.org тощо) замість системного crontab.
     * Захищено секретним токеном з .env — без нього 403.
     *
     * Зовнішні виклики приходять із затримками й не щохвилини, а schedule:run
     * запускає лише задачі, призначені на поточну хвилину. Тому тут виконуються
     * всі задачі, що мали спрацювати з попереднього виклику (до MAX_CATCHUP_MINUTES),
     * кожна — один раз.
     */
    public function run($token)
    {
        $expected = env('CRON_SECRET_TOKEN');

        if (empty($expected) || !hash_equals($expected, (string) $token)) {
            abort(403);
        }

        // Примусово фіксуємо таймзону: web і CLI на shared-хостингу можуть мати різний date.timezone.
        date_default_timezone_set(config('app.timezone'));
        @set_time_limit(280);
        ignore_user_abort(true);

        app(ConsoleKernel::class); // реєструє розклад (App\Console\Kernel::schedule)
        $schedule = app(Schedule::class);

        $now = Carbon::now()->startOfMinute();
        $last = Cache::get(self::LAST_MINUTE_KEY);
        $from = $last ? Carbon::parse($last)->addMinute() : $now->copy();
        if ($from->lt($now->copy()->subMinutes(self::MAX_CATCHUP_MINUTES - 1))) {
            $from = $now->copy()->subMinutes(self::MAX_CATCHUP_MINUTES - 1);
        }
        if ($from->gt($now)) {
            return $this->respond('Цю хвилину вже оброблено', []);
        }
        Cache::forever(self::LAST_MINUTE_KEY, $now->toDateTimeString());

        // Які задачі мали спрацювати в будь-яку з пропущених хвилин (кожна один раз)
        $due = [];
        for ($minute = $from->copy(); $minute->lte($now); $minute->addMinute()) {
            Carbon::setTestNow($minute->copy());
            foreach ($schedule->dueEvents(app()) as $event) {
                $due[$event->mutexName()] = $event;
            }
        }
        Carbon::setTestNow();

        $ran = [];
        foreach ($due as $event) {
            if (!$event->filtersPass(app())) {
                continue;
            }
            try {
                $event->run(app());
                $ran[] = $event->getSummaryForDisplay();
            } catch (\Throwable $e) {
                $ran[] = $event->getSummaryForDisplay() . ' — ПОМИЛКА: ' . $e->getMessage();
                report($e);
            }
        }

        return $this->respond('Хвилини ' . $from->format('H:i') . '–' . $now->format('H:i'), $ran);
    }

    protected function respond(string $window, array $ran)
    {
        $debug = 'PHP timezone: ' . date_default_timezone_get()
            . ' | Laravel now(): ' . now()->toDateTimeString();
        $body = "OK\n{$debug}\n{$window}; запущено задач: " . count($ran) . "\n" . implode("\n", $ran) . "\n";

        return response($body)->header('Content-Type', 'text/plain');
    }
}
