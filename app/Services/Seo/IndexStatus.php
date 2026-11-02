<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Статус індексації Google (таблиця seo_index_status, наповнює seo:index-status)
 * для адмінки: зведення над таблицями й значок у рядку.
 */
class IndexStatus
{
    const INDEXED = 'indexed';
    const NOT_INDEXED = 'not_indexed';
    const EXCLUDED = 'excluded';
    const UNKNOWN = 'unknown';

    /** Стан за результатом URL Inspection */
    public static function stateFromInspection(array $status): string
    {
        if (($status['verdict'] ?? '') === 'PASS') {
            return self::INDEXED;
        }
        $coverage = strtolower((string) ($status['coverageState'] ?? ''));
        if ($coverage === '') {
            return self::UNKNOWN;
        }
        // Свідомо не в індексі: noindex, robots.txt, канонічна інша сторінка, редирект, 404
        foreach (['noindex', 'robots', 'canonical', 'duplicate', 'redirect', 'not found', '404', 'soft 404'] as $needle) {
            if (strpos($coverage, $needle) !== false) {
                return self::EXCLUDED;
            }
        }

        return self::NOT_INDEXED; // «Crawled/Discovered - currently not indexed», «URL is unknown to Google»
    }

    /** Чи сутність зараз в індексі Google (за останньою перевіркою) */
    public static function isIndexed(string $type, int $id): bool
    {
        return in_array($id, self::indexedIds($type), true);
    }

    /** id сутностей у стані «в індексі»; кеш 1 год (дані оновлюються раз на добу) */
    public static function indexedIds(string $type): array
    {
        if (!self::tableExists()) {
            return [];
        }

        return Cache::remember("seo_indexed_ids:{$type}", 3600, function () use ($type) {
            return DB::table('seo_index_status')->where('entity_type', $type)->where('state', self::INDEXED)
                ->pluck('entity_id')->map(function ($id) {
                    return (int) $id;
                })->all();
        });
    }

    /** [entity_id => row] для рядків на поточній сторінці адмінки */
    public static function forIds(string $type, array $ids): array
    {
        if (!$ids || !self::tableExists()) {
            return [];
        }

        return DB::table('seo_index_status')->where('entity_type', $type)->whereIn('entity_id', $ids)
            ->get()->keyBy('entity_id')->all();
    }

    /**
     * Зведення: total — скільки сутностей має бути в індексі; checked/indexed/not_indexed/excluded.
     * Кеш 10 хв, щоб адмінка не рахувала таблицю на кожному відкритті.
     */
    public static function summary(string $type): array
    {
        return Cache::remember("seo_index_summary:{$type}", 600, function () use ($type) {
            $counts = self::tableExists()
                ? DB::table('seo_index_status')->where('entity_type', $type)
                    ->selectRaw('state, COUNT(*) AS n')->groupBy('state')->pluck('n', 'state')->all()
                : [];
            $last = self::tableExists()
                ? DB::table('seo_index_status')->where('entity_type', $type)->max('checked_at')
                : null;
            $total = self::scopeTotal($type);
            $checked = array_sum($counts);

            return [
                'total' => $total,
                'checked' => $checked,
                'indexed' => (int) ($counts[self::INDEXED] ?? 0),
                'not_indexed' => (int) ($counts[self::NOT_INDEXED] ?? 0),
                'excluded' => (int) ($counts[self::EXCLUDED] ?? 0),
                'unknown' => (int) ($counts[self::UNKNOWN] ?? 0),
                'unchecked' => max(0, $total - $checked),
                'last_checked' => $last,
            ];
        });
    }

    /** Скільки сутностей у «зоні індексації»: активні оголошення, усі категорії, теги з активними оголошеннями */
    public static function scopeTotal(string $type): int
    {
        switch ($type) {
            case 'ad':
                return (int) DB::table('ads')->where('status', 1)->where('is_sensitive', 0)->count();
            case 'category':
                return (int) DB::table('ad_categories')->count();
            case 'tag':
                return (int) DB::table('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')
                    ->where('ads.status', 1)->distinct()->count('ad_tag.tag_id');
        }

        return 0;
    }

    public static function label(?object $row): array
    {
        if (!$row) {
            return ['Не проверено', 'secondary', 'Ещё не проверялось в Google Search Console'];
        }
        $when = $row->checked_at ? ' · проверено ' . date('d.m.Y', strtotime($row->checked_at)) : '';
        switch ($row->state) {
            case self::INDEXED:
                $hint = $row->source === 'analytics'
                    ? 'Есть показы в поиске за 90 дней: ' . (int) $row->impressions
                    : 'Google: страница в индексе';

                return ['В индексе', 'success', $hint . $when];
            case self::EXCLUDED:
                return ['Исключено', 'warning', (string) $row->coverage_state . $when];
            case self::NOT_INDEXED:
                return ['Не в индексе', 'danger', (string) $row->coverage_state . $when];
        }

        return ['Неизвестно', 'secondary', (string) $row->coverage_state . $when];
    }

    public static function tableExists(): bool
    {
        static $exists = null;

        return $exists ?? ($exists = Schema::hasTable('seo_index_status'));
    }
}
