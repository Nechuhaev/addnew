<?php

namespace App\Console\Commands;

use App\DailyReport;
use App\Services\Seo\BingClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * php artisan seo:bing-stats
 *
 * Забирає з Bing Webmaster Tools покази й кліки (GetRankAndTrafficStats) і кількість сторінок в індексі
 * та сканування (GetCrawlStats) по днях → таблиця bing_daily_stats. Підсумок (7 днів проти попередніх 7,
 * сторінок в індексі) — у щоденний звіт і над таблицями індексації в адмінці.
 */
class BingStats extends Command
{
    protected $signature = 'seo:bing-stats';

    protected $description = 'Статистика Bing Webmaster Tools: покази, кліки, сторінок в індексі';

    public function handle()
    {
        $bing = new BingClient();
        if ($error = $bing->configError()) {
            $this->warn($error);
            return 0;
        }

        try {
            $traffic = $bing->trafficByDay();
            $crawl = $bing->crawlByDay();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return 1;
        }

        $dates = array_unique(array_merge(array_keys($traffic), array_keys($crawl)));
        foreach ($dates as $date) {
            $values = array_merge($traffic[$date] ?? [], $crawl[$date] ?? []) + ['updated_at' => now()];
            $affected = DB::table('bing_daily_stats')->where('date', $date)->update($values);
            if (!$affected && !DB::table('bing_daily_stats')->where('date', $date)->exists()) {
                DB::table('bing_daily_stats')->insert($values + ['date' => $date, 'created_at' => now()]);
            }
        }
        Cache::forget('bing_summary');

        $summary = self::summaryLine();
        $this->info('Bing (' . $bing->siteUrl() . "): днів у відповіді — трафік " . count($traffic) . ', сканування ' . count($crawl));
        $this->info($summary ?: 'Даних ще немає (Bing накопичує статистику кілька днів після додавання сайту).');
        if ($summary) {
            $report = DailyReport::firstOrCreate(['date' => now()->toDateString()]);
            $report->bing_summary = $summary;
            $report->save();
        }

        return 0;
    }

    /** Підсумок: покази/кліки за 7 днів (проти попередніх 7) і останнє відоме «в індексі» */
    public static function summary(): ?array
    {
        return Cache::remember('bing_summary', 3600, function () {
            if (!\Illuminate\Support\Facades\Schema::hasTable('bing_daily_stats')) {
                return null;
            }
            $last = DB::table('bing_daily_stats')->max('date');
            if (!$last) {
                return null;
            }
            $sum = function ($from, $to) {
                return DB::table('bing_daily_stats')->whereBetween('date', [$from, $to])
                    ->selectRaw('COALESCE(SUM(clicks),0) c, COALESCE(SUM(impressions),0) i')->first();
            };
            $end = \Carbon\Carbon::parse($last);
            $cur = $sum($end->copy()->subDays(6)->toDateString(), $end->toDateString());
            $prev = $sum($end->copy()->subDays(13)->toDateString(), $end->copy()->subDays(7)->toDateString());
            $index = DB::table('bing_daily_stats')->whereNotNull('in_index')->where('in_index', '>', 0)->orderByDesc('date')->first();

            return [
                'last_date' => $last,
                'clicks' => (int) $cur->c, 'impressions' => (int) $cur->i,
                'prev_clicks' => (int) $prev->c, 'prev_impressions' => (int) $prev->i,
                'in_index' => $index ? (int) $index->in_index : null,
                'in_index_date' => $index ? $index->date : null,
            ];
        });
    }

    public static function summaryLine(): ?string
    {
        $s = self::summary();
        if (!$s) {
            return null;
        }

        return 'Bing за 7 днів до ' . date('d.m', strtotime($s['last_date'])) . ': покази ' . $s['impressions']
            . ' (було ' . $s['prev_impressions'] . '), кліки ' . $s['clicks'] . ' (було ' . $s['prev_clicks'] . ')'
            . ($s['in_index'] !== null ? '; в індексі ' . $s['in_index'] . ' стор. (' . date('d.m', strtotime($s['in_index_date'])) . ')' : '');
    }
}
