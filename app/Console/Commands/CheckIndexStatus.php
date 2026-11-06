<?php

namespace App\Console\Commands;

use App\AdCategory;
use App\Services\Seo\GscClient;
use App\Services\Seo\IndexStatus;
use App\Services\SeoRules;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * php artisan seo:index-status [--limit=1500] [--only=ad|category|tag] [--skip-analytics] [--skip-inspection]
 *
 * Статус індексації Google для оголошень, категорій і тегів (адмінка: стовпчик «Индекс»):
 *  1) Search Analytics: сторінки з показами за 90 днів — точно в індексі (одним запитом тисячі адрес);
 *  2) URL Inspection: решта порціями (ліміт Google 2000/добу на властивість, частину бере
 *     content:check-index для статей) — спершу ще не перевірені, далі найдавніше перевірені;
 *     порядок: категорії → теги (з активними оголошеннями) → активні оголошення.
 */
class CheckIndexStatus extends Command
{
    protected $signature = 'seo:index-status {--limit=} {--only=} {--skip-analytics} {--skip-inspection} {--recheck-days=30}';

    protected $description = 'Статус індексації Google для оголошень, категорій і тегів';

    /** @var GscClient */
    protected $gsc;

    public function handle()
    {
        $this->gsc = new GscClient();
        if ($error = $this->gsc->configError()) {
            $this->error($error);
            return 1;
        }

        $only = $this->option('only');
        if (!$this->option('skip-analytics')) {
            $this->fromAnalytics($only);
        }
        if (!$this->option('skip-inspection')) {
            $this->fromInspection($only, (int) ($this->option('limit') ?: env('SEO_INDEX_CHECK_PER_DAY', 1500)));
        }
        foreach (['ad', 'category', 'tag'] as $type) {
            Cache::forget("seo_index_summary_v2:{$type}");
            Cache::forget("seo_our_noindex_ids:{$type}");
            Cache::forget("seo_indexed_ids:{$type}");
        }

        return 0;
    }

    /** Сторінки з показами → «В индексе» (uk і ru-версії однієї сутності сумуються) */
    protected function fromAnalytics(?string $only): void
    {
        try {
            $pages = $this->gsc->pagesWithImpressions(now()->subDays(93)->toDateString(), now()->subDays(3)->toDateString());
        } catch (\Throwable $e) {
            $this->error('Search Analytics: ' . $e->getMessage());
            return;
        }

        $byType = ['ad' => [], 'category' => [], 'tag' => []];
        $categoryPaths = $this->categoryPaths();
        foreach ($pages as $url => $impressions) {
            $path = rtrim(preg_replace('~^/ru(?=/|$)~', '', (string) parse_url($url, PHP_URL_PATH)), '/');
            if (preg_match('~^/ads/([^/]+)$~', $path, $m)) {
                $byType['ad'][$m[1]] = ($byType['ad'][$m[1]] ?? 0) + $impressions;
            } elseif (preg_match('~^/ad-tag/([^/]+)$~', $path, $m)) {
                $byType['tag'][$m[1]] = ($byType['tag'][$m[1]] ?? 0) + $impressions;
            } elseif (isset($categoryPaths[$path])) {
                $id = $categoryPaths[$path];
                $byType['category'][$id] = ($byType['category'][$id] ?? 0) + $impressions;
            }
        }

        $saved = 0;
        foreach (['ad' => 'ads', 'tag' => 'ad_tags'] as $type => $table) {
            if ($only && $only !== $type) {
                continue;
            }
            foreach (array_chunk(array_keys($byType[$type]), 500) as $slugs) {
                $ids = DB::table($table)->whereIn('slug', $slugs)->pluck('id', 'slug')->all();
                foreach ($ids as $slug => $id) {
                    $this->store($type, (int) $id, $this->entityUrl($type, (int) $id, $slug), IndexStatus::INDEXED, [
                        'impressions' => $byType[$type][$slug], 'source' => 'analytics',
                    ]);
                    $saved++;
                }
            }
        }
        if (!$only || $only === 'category') {
            foreach ($byType['category'] as $id => $impressions) {
                $this->store('category', (int) $id, $this->entityUrl('category', (int) $id), IndexStatus::INDEXED, [
                    'impressions' => $impressions, 'source' => 'analytics',
                ]);
                $saved++;
            }
        }
        $this->info('Search Analytics: сторінок з показами ' . count($pages) . ", записано як «в індексі» {$saved}");
    }

    protected function fromInspection(?string $only, int $limit): void
    {
        if ($limit <= 0) {
            return;
        }
        $done = 0;
        $counts = [];
        foreach (['category', 'tag', 'ad'] as $type) {
            if (($only && $only !== $type) || $done >= $limit) {
                continue;
            }
            foreach ($this->candidates($type, $limit - $done) as $id => $slug) {
                $url = $this->entityUrl($type, (int) $id, $slug);
                try {
                    $status = $this->gsc->inspect($url);
                } catch (\Throwable $e) {
                    $msg = $e->getMessage();
                    $this->error("{$url}: " . mb_substr($msg, 0, 200));
                    if (strpos($msg, '429') !== false || stripos($msg, 'quota') !== false) {
                        $this->warn('Добовий ліміт URL Inspection вичерпано — продовжимо завтра.');
                        break 2;
                    }
                    continue;
                }
                $state = IndexStatus::stateFromInspection($status);
                $this->store($type, (int) $id, $url, $state, [
                    'verdict' => $status['verdict'] ?? null,
                    'coverage_state' => $status['coverageState'] ?? null,
                    'last_crawl_time' => !empty($status['lastCrawlTime']) ? date('Y-m-d H:i:s', strtotime($status['lastCrawlTime'])) : null,
                    'source' => 'inspection',
                ]);
                $counts[$state] = ($counts[$state] ?? 0) + 1;
                $done++;
                usleep(150000); // ліміт 600/хв
            }
        }
        $this->info("URL Inspection: перевірено {$done} — " . json_encode($counts, JSON_UNESCAPED_UNICODE));
    }

    /**
     * [id => slug|null]: спершу ще не перевірені, далі найдавніше перевірені (старші за --recheck-days);
     * сторінки з показами (analytics, свіжі) повторно не інспектуємо.
     */
    protected function candidates(string $type, int $limit): array
    {
        $recheckBefore = now()->subDays(max(1, (int) $this->option('recheck-days')));
        $table = ['ad' => 'ads', 'category' => 'ad_categories', 'tag' => 'ad_tags'][$type];
        $q = DB::table("{$table} as e")
            ->leftJoin('seo_index_status as s', function ($j) use ($type) {
                $j->on('s.entity_id', '=', 'e.id')->where('s.entity_type', '=', $type);
            })
            ->where(function ($w) use ($recheckBefore) {
                $w->whereNull('s.id')->orWhere('s.checked_at', '<', $recheckBefore);
            })
            ->orderByRaw('s.id IS NOT NULL, s.checked_at ASC')
            ->limit($limit);

        if ($type === 'ad') {
            $q->where('e.status', 1)->where('e.is_sensitive', 0)->whereNotNull('e.slug')->where('e.slug', '<>', '')
                ->orderByDesc('e.id')->select('e.id', 'e.slug');
        } elseif ($type === 'tag') {
            $q->whereNotNull('e.slug')->where('e.slug', '<>', '')
                ->whereExists(function ($x) {
                    $x->select(DB::raw(1))->from('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')
                        ->whereColumn('ad_tag.tag_id', 'e.id')->where('ads.status', 1)->where('ads.is_sensitive', 0);
                })->select('e.id', 'e.slug');
        } else {
            $sensitive = SeoRules::sensitiveCategoryIds();
            if ($sensitive) {
                $q->whereNotIn('e.id', $sensitive);
            }
            $q->select('e.id', 'e.slug');
        }

        return $q->pluck('e.slug', 'e.id')->all();
    }

    protected function entityUrl(string $type, int $id, ?string $slug = null): string
    {
        if ($type === 'ad') {
            return url('/ads/' . $slug);
        }
        if ($type === 'tag') {
            return route('tag', ['slug' => $slug]);
        }
        $category = AdCategory::find($id);

        return $category ? $category->url : url('/');
    }

    /** [шлях без домену => id категорії] */
    protected function categoryPaths(): array
    {
        $out = [];
        foreach (AdCategory::all() as $c) {
            $out[rtrim((string) parse_url($c->url, PHP_URL_PATH), '/')] = $c->id;
        }

        return $out;
    }

    protected function store(string $type, int $id, string $url, string $state, array $extra): void
    {
        $values = array_merge([
            'url' => mb_substr($url, 0, 500),
            'state' => $state,
            'verdict' => $state === IndexStatus::INDEXED ? 'PASS' : null,
            'coverage_state' => null,
            'impressions' => null,
            'last_crawl_time' => null,
            'checked_at' => now(),
            'updated_at' => now(),
        ], $extra);

        $affected = DB::table('seo_index_status')->where('entity_type', $type)->where('entity_id', $id)->update($values);
        if (!$affected && !DB::table('seo_index_status')->where('entity_type', $type)->where('entity_id', $id)->exists()) {
            DB::table('seo_index_status')->insert($values + ['entity_type' => $type, 'entity_id' => $id, 'created_at' => now()]);
        }
    }
}
