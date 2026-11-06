<?php

namespace App\Services\Seo;

use App\AdCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Журнал відправок на індексацію (таблиця seo_index_submissions) і звіт «надіслано / з надісланих в індексі».
 */
class IndexSubmissions
{
    const TYPES = ['article' => 'Статьи', 'category' => 'Категории', 'tag' => 'Теги', 'ad' => 'Объявления'];

    /** Адреса сутності (українська версія) */
    public static function url(string $type, int $id, ?string $slug = null): ?string
    {
        switch ($type) {
            case 'ad':
                return $slug ? url('/ads/' . $slug) : null;
            case 'tag':
                return $slug ? route('tag', ['slug' => $slug]) : null;
            case 'article':
                return $slug ? route('blog.article', $slug) : null;
            case 'category':
                $category = AdCategory::find($id);

                return $category ? $category->url : null;
        }

        return null;
    }

    /** Російська версія адреси: префікс /ru після домену */
    public static function ruUrl(string $url): string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '/';
        if (preg_match('~^/ru(/|$)~', $path)) {
            return $url;
        }

        return $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '')
            . '/ru' . ($path === '/' ? '' : $path) . (isset($parts['query']) ? '?' . $parts['query'] : '');
    }

    /** Записати відправку: [[type, id, url], ...] */
    public static function record(string $channel, array $items): void
    {
        if (!$items || !self::tableExists()) {
            return;
        }
        $now = now();
        foreach ($items as [$type, $id, $url]) {
            $affected = DB::table('seo_index_submissions')
                ->where('entity_type', $type)->where('entity_id', $id)->where('channel', $channel)
                ->update(['url' => mb_substr($url, 0, 500), 'submitted_at' => $now, 'times' => DB::raw('times + 1')]);
            if (!$affected) {
                DB::table('seo_index_submissions')->insertOrIgnore([
                    'entity_type' => $type, 'entity_id' => $id, 'channel' => $channel,
                    'url' => mb_substr($url, 0, 500), 'submitted_at' => $now,
                ]);
            }
        }
        Cache::forget('seo_index_submissions_report');
    }

    /**
     * Звіт: за 1 і 7 днів скільки надіслано по каналах; з усіх коли-небудь надісланих — скільки зараз в індексі
     * (за seo_index_status / article_index_status). Кеш 10 хв.
     */
    public static function report(): array
    {
        if (!self::tableExists()) {
            return [];
        }

        return Cache::remember('seo_index_submissions_report', 600, function () {
            $out = [];
            foreach (['indexnow', 'google'] as $channel) {
                $base = DB::table('seo_index_submissions')->where('channel', $channel);
                $out[$channel] = [
                    'day' => (clone $base)->where('submitted_at', '>=', now()->subDay())->count(),
                    'week' => (clone $base)->where('submitted_at', '>=', now()->subDays(7))->count(),
                    'total' => (clone $base)->count(),
                    'indexed' => self::indexedAmongSubmitted($channel),
                ];
            }

            return $out;
        });
    }

    protected static function indexedAmongSubmitted(string $channel): int
    {
        $n = DB::table('seo_index_submissions as s')
            ->join('seo_index_status as i', function ($j) {
                $j->on('i.entity_id', '=', 's.entity_id')->on('i.entity_type', '=', 's.entity_type');
            })
            ->where('s.channel', $channel)->where('i.state', IndexStatus::INDEXED)->count();
        if (Schema::hasTable('article_index_status')) {
            $n += DB::table('seo_index_submissions as s')
                ->join('article_index_status as a', 'a.article_id', '=', 's.entity_id')
                ->where('s.channel', $channel)->where('s.entity_type', 'article')->where('a.verdict', 'PASS')->count();
        }

        return $n;
    }

    public static function tableExists(): bool
    {
        static $exists = null;

        return $exists ?? ($exists = Schema::hasTable('seo_index_submissions'));
    }
}
