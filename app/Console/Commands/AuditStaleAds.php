<?php

namespace App\Console\Commands;

use App\Services\Seo\GscClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan ads:stale-audit [--years=3] [--apply] [--unmark-all]
 *
 * Старі оголошення, яких Google не показав жодного разу за 16 місяців (максимум, що зберігає Search Console):
 * лишаються на сайті й у пошуку по сайту, але отримують noindex і виходять із sitemap (ads.is_stale = 1).
 * «Старе» — створене й не піднімане (date_active) довше, ніж --years років. Імпортовані товари (is_product) не чіпаються:
 * у них оновлюються ціни й наявність. Позначка знімається сама, коли автор редагує чи піднімає оголошення,
 * а при кожному запуску — з тих, що отримали покази. Без --apply — лише перегляд.
 */
class AuditStaleAds extends Command
{
    protected $signature = 'ads:stale-audit {--years=3} {--apply} {--unmark-all}';

    protected $description = 'Noindex для старих оголошень без показів у Google за 16 місяців';

    const SHORT_TEXT = 200;

    public function handle()
    {
        if (!Schema::hasColumn('ads', 'is_stale')) {
            $this->error('Немає колонки ads.is_stale — спершу php artisan migrate');
            return 1;
        }
        if ($this->option('unmark-all')) {
            $n = DB::table('ads')->where('is_stale', 1)->update(['is_stale' => 0]);
            $this->forgetCaches();
            $this->info("Знято позначку з оголошень: {$n}. Після цього: php artisan sitemap:rebuild");
            return 0;
        }

        // Без даних Search Console нічого не позначаємо: інакше під noindex піде все старе підряд
        $gsc = app(GscClient::class);
        if ($error = $gsc->configError()) {
            $this->error('Search Console не налаштовано: ' . $error);
            return 1;
        }
        try {
            $pages = $gsc->pagesWithImpressions(now()->subMonths(16)->toDateString(), now()->subDays(3)->toDateString());
        } catch (\Throwable $e) {
            $this->error('Search Analytics: ' . $e->getMessage());
            return 1;
        }
        $shown = [];
        foreach ($pages as $url => $impressions) {
            $path = rtrim(preg_replace('~^/ru(?=/|$)~', '', (string) parse_url($url, PHP_URL_PATH)), '/');
            if ($impressions > 0 && preg_match('~^/ads/([^/]+)$~', $path, $m)) {
                $shown[rawurldecode($m[1])] = true;
            }
        }
        if (!$shown) {
            $this->error('Search Console не повернув жодного оголошення з показами — зупиняюсь, нічого не змінено');
            return 1;
        }
        $this->line('Сторінок оголошень з показами за 16 місяців: ' . count($shown));

        $cutoff = now()->subYears(max(1, (int) $this->option('years')))->toDateTimeString();
        $stats = ['active' => 0, 'old' => 0, 'shown' => 0, 'mark' => 0, 'short' => 0, 'nophoto' => 0];
        $mark = $unmark = [];
        $examples = [];
        DB::table('ads')->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('is_product')->orWhere('is_product', 0);
            })
            ->select('id', 'slug', 'name', 'content', 'image', 'created_at', 'date_active', 'is_stale')
            ->orderBy('id')
            ->chunk(2000, function ($ads) use ($cutoff, $shown, &$stats, &$mark, &$unmark, &$examples) {
                foreach ($ads as $ad) {
                    $stats['active']++;
                    $last = max((string) $ad->created_at, (string) $ad->date_active);
                    $isOld = $last !== '' && $last < $cutoff;
                    $hasImpressions = isset($shown[(string) $ad->slug]);
                    $stale = $isOld && !$hasImpressions;
                    if ($isOld) {
                        $stats['old']++;
                        $stats['shown'] += $hasImpressions ? 1 : 0;
                    }
                    if ($stale && !$ad->is_stale) {
                        $mark[] = (int) $ad->id;
                    } elseif (!$stale && $ad->is_stale) {
                        $unmark[] = (int) $ad->id;
                    }
                    if ($stale) {
                        $stats['mark']++;
                        $text = trim(strip_tags(html_entity_decode((string) $ad->content, ENT_QUOTES, 'UTF-8')));
                        $stats['short'] += mb_strlen($text) < self::SHORT_TEXT ? 1 : 0;
                        $image = trim((string) $ad->image);
                        $stats['nophoto'] += ($image === '' || strpos($image, 'placeholder') !== false) ? 1 : 0;
                        if (count($examples) < 15) {
                            $examples[] = sprintf('#%d %s | %s', $ad->id, substr($last, 0, 10), mb_substr((string) $ad->name, 0, 70));
                        }
                    }
                }
            });

        $this->info(sprintf('Активних оголошень (без імпортованих товарів): %d', $stats['active']));
        $this->line(sprintf('  старших за %s: %d, з них мали покази: %d', substr($cutoff, 0, 10), $stats['old'], $stats['shown']));
        $this->info(sprintf('Під noindex (старі без жодного показу): %d — з них текст коротший за %d символів: %d, без фото: %d',
            $stats['mark'], self::SHORT_TEXT, $stats['short'], $stats['nophoto']));
        $this->line(sprintf('  нових позначок: %d; зняти позначку (з’явилися покази чи оновлено): %d', count($mark), count($unmark)));
        if ($examples) {
            $this->line('Приклади:');
            foreach ($examples as $line) {
                $this->line('  ' . $line);
            }
        }

        if (!$this->option('apply')) {
            $this->warn('Це попередній перегляд. Щоб позначити — з --apply. Скасувати все: --unmark-all');
            return 0;
        }
        $backup = storage_path('app/stale-ads-' . date('Ymd-His') . '.json');
        file_put_contents($backup, json_encode(['marked' => $mark, 'unmarked' => $unmark]));
        foreach (array_chunk($mark, 1000) as $chunk) {
            DB::table('ads')->whereIn('id', $chunk)->update(['is_stale' => 1]);
        }
        foreach (array_chunk($unmark, 1000) as $chunk) {
            DB::table('ads')->whereIn('id', $chunk)->update(['is_stale' => 0]);
        }
        $this->forgetCaches();
        $this->info('Позначено: ' . count($mark) . ', знято: ' . count($unmark) . ". Список: {$backup}. Після цього: php artisan sitemap:rebuild");

        return 0;
    }

    protected function forgetCaches(): void
    {
        foreach (['seo_index_summary_v2:ad', 'seo_our_noindex_ids:ad'] as $key) {
            Cache::forget($key);
        }
    }
}
