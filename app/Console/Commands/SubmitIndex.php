<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\UpdatesDailyReport;
use App\Services\Seo\GscClient;
use App\Services\Seo\IndexStatus;
use App\Services\Seo\IndexSubmissions;
use App\Services\SeoRules;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * php artisan seo:submit-index [--indexnow-limit=5000] [--google-limit=] [--only=article|category|tag|ad] [--dry-run]
 *
 * Відправляє на індексацію відкриті для індексації сторінки (статті, категорії, теги, оголошення й товари магазинів):
 *  - IndexNow (Bing, на ньому пошук ChatGPT і Copilot; Yandex, Seznam): ще не надіслані або надіслані понад
 *    30 днів тому й досі не в індексі; uk і ru-версії. Потрібен INDEXNOW_KEY.
 *  - Google Indexing API: до GOOGLE_INDEXING_PER_DAY (100) адрес на добу — лише ті, яких немає в індексі за
 *    seo:index-status (спершу статті й категорії). Офіційно API для вакансій і трансляцій — Google може ігнорувати
 *    інші сторінки. 0 — вимкнено. Потрібні Indexing API в Google Cloud і сервісний акаунт-власник у Search Console.
 * Сторінки, закриті noindex нами, не надсилаються. Звіт — у щоденному звіті й над таблицями адмінки.
 */
class SubmitIndex extends Command
{
    use UpdatesDailyReport;

    protected $signature = 'seo:submit-index {--indexnow-limit=5000} {--google-limit=} {--only=} {--resubmit-days=30} {--dry-run}';

    protected $description = 'Відправка сторінок на індексацію: IndexNow + Google Indexing API, зі звітом';

    const ORDER = ['article', 'category', 'tag', 'ad'];

    public function handle()
    {
        $dry = (bool) $this->option('dry-run');
        $sentIndexNow = $this->indexNow((int) $this->option('indexnow-limit'), $dry);
        $googleLimit = $this->option('google-limit') !== null ? (int) $this->option('google-limit') : (int) env('GOOGLE_INDEXING_PER_DAY', 100);
        $sentGoogle = $this->google($googleLimit, $dry);

        if (!$dry && ($sentIndexNow || $sentGoogle)) {
            $r = IndexSubmissions::report();
            $line = 'IndexNow ' . $sentIndexNow . ', Google ' . $sentGoogle
                . ' · з надісланих коли-небудь в індексі: IndexNow ' . ($r['indexnow']['indexed'] ?? 0) . '/' . ($r['indexnow']['total'] ?? 0)
                . ', Google ' . ($r['google']['indexed'] ?? 0) . '/' . ($r['google']['total'] ?? 0);
            $this->appendDailyReportStat('index_submitted_indexnow', $sentIndexNow);
            $this->appendDailyReportStat('index_submitted_google', $sentGoogle, 'index_submitted_summary', $line);
            $this->info($line);
        }

        return 0;
    }

    /** @return int скільки сторінок (сутностей) надіслано */
    protected function indexNow(int $limit, bool $dry): int
    {
        $key = (string) config('seo.indexnow_key', '');
        if ($limit <= 0 || !preg_match('/^[a-zA-Z0-9\-]{8,128}$/', $key)) {
            $this->warn('IndexNow: пропущено (INDEXNOW_KEY не задано або ліміт 0).');
            return 0;
        }
        $items = $this->candidates('indexnow', $limit);
        $urls = [];
        foreach ($items as [, , $url]) {
            $urls[] = $url;
            $urls[] = IndexSubmissions::ruUrl($url);
        }
        $this->info('IndexNow: сторінок ' . count($items) . ', адрес (uk+ru) ' . count($urls) . ' ' . $this->byType($items));
        if ($dry || !$urls) {
            return $dry ? 0 : 0;
        }

        $client = new Client(['timeout' => 30]);
        $host = parse_url(url('/'), PHP_URL_HOST);
        $sent = [];
        foreach (array_chunk($items, 4000) as $chunk) {
            $batch = [];
            foreach ($chunk as [, , $url]) {
                $batch[] = $url;
                $batch[] = IndexSubmissions::ruUrl($url);
            }
            try {
                $res = $client->post(\App\Console\Commands\IndexNowPing::ENDPOINT, [
                    'json' => ['host' => $host, 'key' => $key, 'keyLocation' => url('/indexnow-key.txt'), 'urlList' => $batch],
                    'http_errors' => false,
                ]);
            } catch (\Throwable $e) {
                $this->error('IndexNow: ' . $e->getMessage());
                break;
            }
            $code = $res->getStatusCode();
            if (!in_array($code, [200, 202], true)) {
                $this->error("IndexNow відхилив запит (HTTP {$code}): " . mb_substr((string) $res->getBody(), 0, 300));
                break;
            }
            IndexSubmissions::record('indexnow', $chunk);
            $sent = array_merge($sent, $chunk);
        }

        return count($sent);
    }

    protected function google(int $limit, bool $dry): int
    {
        if ($limit <= 0) {
            $this->line('Google Indexing API: вимкнено (GOOGLE_INDEXING_PER_DAY=0).');
            return 0;
        }
        $gsc = new GscClient();
        if ($error = $gsc->configError()) {
            $this->warn('Google Indexing API: ' . $error);
            return 0;
        }
        $items = $this->candidates('google', $limit);
        $this->info('Google Indexing API: кандидатів ' . count($items) . ' ' . $this->byType($items));
        if ($dry) {
            foreach (array_slice($items, 0, 20) as [$type, , $url]) {
                $this->line("  [{$type}] {$url}");
            }
            return 0;
        }

        $sent = [];
        foreach ($items as $item) {
            try {
                $gsc->publishUrl($item[2]);
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (strpos($msg, '403') !== false) {
                    $this->error('Google Indexing API: немає доступу (403). Увімкніть Indexing API в Google Cloud і додайте сервісний акаунт власником (Owner) у Search Console. ' . mb_substr($msg, 0, 200));
                } elseif (strpos($msg, '429') !== false || stripos($msg, 'quota') !== false) {
                    $this->warn('Google Indexing API: добовий ліміт вичерпано.');
                } else {
                    $this->error("{$item[2]}: " . mb_substr($msg, 0, 200));
                    continue;
                }
                break;
            }
            $sent[] = $item;
            usleep(200000);
        }
        IndexSubmissions::record('google', $sent);

        return count($sent);
    }

    /**
     * [[type, id, url], ...] у порядку ORDER, без закритих нами noindex і без надісланих цим каналом
     * за останні --resubmit-days днів (і вже проіндексованих — для повторних відправок).
     * Для Google — лише те, чого немає в індексі (seo:index-status / content:check-index).
     */
    protected function candidates(string $channel, int $limit): array
    {
        $since = now()->subDays(max(1, (int) $this->option('resubmit-days')));
        $only = $this->option('only');
        $out = [];
        foreach (self::ORDER as $type) {
            if (($only && $only !== $type) || count($out) >= $limit) {
                continue;
            }
            $q = $this->baseQuery($type);
            if (!$q) {
                continue;
            }
            if (IndexSubmissions::tableExists()) {
                $q->whereNotIn('e.id', function ($s) use ($type, $channel, $since) {
                    $s->select('entity_id')->from('seo_index_submissions')
                        ->where('entity_type', $type)->where('channel', $channel)->where('submitted_at', '>=', $since);
                });
            }
            // Уже в індексі — не надсилаємо
            if ($type === 'article') {
                if (Schema::hasTable('article_index_status')) {
                    $q->whereNotIn('e.id', function ($s) {
                        $s->select('article_id')->from('article_index_status')->where('verdict', 'PASS');
                    });
                }
            } elseif (IndexStatus::tableExists()) {
                $q->whereNotIn('e.id', function ($s) use ($type) {
                    $s->select('entity_id')->from('seo_index_status')->where('entity_type', $type)->where('state', IndexStatus::INDEXED);
                });
                if ($channel === 'google') {
                    // спершу ті, що Google уже бачив і не взяв, далі ще не перевірені
                    $q->leftJoin('seo_index_status as st', function ($j) use ($type) {
                        $j->on('st.entity_id', '=', 'e.id')->where('st.entity_type', '=', $type);
                    })->orderByRaw('st.id IS NULL');
                }
            }
            $ours = array_flip(IndexStatus::ourNoindexIds($type));
            foreach ($q->orderByDesc('e.id')->limit(($limit - count($out)) + count($ours))->get() as $row) {
                if (count($out) >= $limit) {
                    break;
                }
                if (isset($ours[$row->id])) {
                    continue;
                }
                $url = IndexSubmissions::url($type, (int) $row->id, $row->slug ?? null);
                if ($url) {
                    $out[] = [$type, (int) $row->id, $url];
                }
            }
        }

        return $out;
    }

    /** Відкриті для індексації сутності типу (як у sitemap і зоні індексації адмінки) */
    protected function baseQuery(string $type)
    {
        switch ($type) {
            case 'article':
                return DB::table('articles as e')->whereNotNull('e.slug')->where('e.slug', '<>', '')->select('e.id', 'e.slug');
            case 'category':
                $q = DB::table('ad_categories as e')->select('e.id', 'e.slug');
                if ($sensitive = SeoRules::sensitiveCategoryIds()) {
                    $q->whereNotIn('e.id', $sensitive);
                }
                return $q;
            case 'tag':
                return DB::table('ad_tags as e')->whereNotNull('e.slug')->where('e.slug', '<>', '')
                    ->whereExists(function ($x) {
                        $x->select(DB::raw(1))->from('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')
                            ->whereColumn('ad_tag.tag_id', 'e.id')->where('ads.status', 1)->where('ads.is_sensitive', 0);
                    })->select('e.id', 'e.slug');
            case 'ad':
                return DB::table('ads as e')->where('e.status', 1)->where('e.is_sensitive', 0)
                    ->whereNotNull('e.slug')->where('e.slug', '<>', '')->select('e.id', 'e.slug');
        }

        return null;
    }

    protected function byType(array $items): string
    {
        $n = [];
        foreach ($items as [$type]) {
            $n[$type] = ($n[$type] ?? 0) + 1;
        }

        return $n ? json_encode($n) : '';
    }
}
