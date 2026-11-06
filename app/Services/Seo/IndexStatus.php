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

    /** Фільтри адмінки (?index=...) => підпис */
    const FILTERS = [
        'indexed' => 'В индексе',
        'refused' => 'Google не взял',
        'ours' => 'Закрыто нами (noindex)',
        'excluded' => 'Исключено',
        'unchecked' => 'Не проверено',
    ];

    /**
     * Зведення: total — скільки сутностей у «зоні індексації»;
     * indexed / refused (Google не взяв сторінку, яку ми відкрили) / ours (сторінку закрито noindex нами —
     * що її немає в індексі, очікувано) / excluded / unchecked. Кеш 10 хв.
     */
    public static function summary(string $type): array
    {
        return Cache::remember("seo_index_summary_v2:{$type}", 600, function () use ($type) {
            $rows = self::tableExists()
                ? DB::table('seo_index_status')->where('entity_type', $type)->pluck('state', 'entity_id')->all()
                : [];
            $ours = array_flip(self::ourNoindexIds($type));
            $out = ['indexed' => 0, 'refused' => 0, 'ours' => 0, 'excluded' => 0];
            foreach ($rows as $id => $state) {
                if ($state === self::INDEXED) {
                    $out['indexed']++;
                } elseif (isset($ours[$id])) {
                    $out['ours']++;
                } elseif ($state === self::EXCLUDED) {
                    $out['excluded']++;
                } else {
                    $out['refused']++;
                }
            }
            $oursUnchecked = count(array_diff_key($ours, $rows));
            $total = self::scopeTotal($type);
            $checked = count($rows);

            return $out + [
                'total' => $total,
                'checked' => $checked,
                'open_checked' => $out['indexed'] + $out['refused'] + $out['excluded'],
                'ours_unchecked' => $oursUnchecked,
                'unchecked' => max(0, $total - $checked - $oursUnchecked),
                'last_checked' => self::tableExists()
                    ? DB::table('seo_index_status')->where('entity_type', $type)->max('checked_at')
                    : null,
            ];
        });
    }

    /**
     * id сутностей, які ми самі закрили noindex (узгоджено з фронтом), кеш 1 год:
     *  tag — менше 5 активних оголошень або стоп-слово в назві (Front/Ad/Tag.php);
     *  category — менше 3 активних оголошень з підрозділами або чутливий розділ (Front/Ad/Category.php);
     *  ad — у зоні індексації лише активні нечутливі, тож порожньо.
     */
    public static function ourNoindexIds(string $type): array
    {
        return Cache::remember("seo_our_noindex_ids:{$type}", 3600, function () use ($type) {
            if ($type === 'tag') {
                $thin = DB::table('ad_tag')->join('ads', 'ads.id', '=', 'ad_tag.ad_id')->where('ads.status', 1)
                    ->groupBy('ad_tag.tag_id')->havingRaw('SUM(ads.is_sensitive = 0) < 5')
                    ->pluck('ad_tag.tag_id')->map(function ($id) {
                        return (int) $id;
                    })->all();
                $sensitive = [];
                if (\App\StopWord::query()->exists()) {
                    \App\AdTag::query()->select('id', 'name', 'name_uk')->orderBy('id')->chunk(2000, function ($tags) use (&$sensitive) {
                        foreach ($tags as $t) {
                            if (\App\Services\SeoRules::isSensitiveTag($t)) {
                                $sensitive[] = (int) $t->id;
                            }
                        }
                    });
                }

                return array_values(array_unique(array_merge($thin, $sensitive)));
            }
            if ($type === 'category') {
                $counts = DB::table('ads')->where('status', 1)->where('is_sensitive', 0)
                    ->groupBy('category_id')->selectRaw('category_id, COUNT(*) n')->pluck('n', 'category_id')->all();
                $cats = DB::table('ad_categories')->get(['id', 'parent_id']);
                $total = [];
                foreach ($cats as $c) {
                    $total[$c->id] = ($total[$c->id] ?? 0) + (int) ($counts[$c->id] ?? 0);
                    if ($c->parent_id) {
                        $total[$c->parent_id] = ($total[$c->parent_id] ?? 0) + (int) ($counts[$c->id] ?? 0);
                    }
                }
                $out = \App\Services\SeoRules::sensitiveCategoryIds();
                foreach ($cats as $c) {
                    if (($total[$c->id] ?? 0) < \App\Http\Controllers\Front\Ad\Category::MIN_INDEX_ADS) {
                        $out[] = (int) $c->id;
                    }
                }

                return array_values(array_unique(array_map('intval', $out)));
            }

            return [];
        });
    }

    /**
     * Обмежити запит списку адмінки фільтром ?index=... ($idColumn — колонка id сутності в запиті)
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     */
    public static function applyFilter($query, string $type, ?string $filter, string $idColumn = 'id'): void
    {
        if (!$filter || !isset(self::FILTERS[$filter]) || !self::tableExists()) {
            return;
        }
        $rows = function ($states) use ($type) {
            return function ($q) use ($type, $states) {
                $q->select('entity_id')->from('seo_index_status')->where('entity_type', $type)->whereIn('state', (array) $states);
            };
        };
        $ours = self::ourNoindexIds($type);
        switch ($filter) {
            case 'indexed':
                $query->whereIn($idColumn, $rows(self::INDEXED));
                break;
            case 'refused':
                $query->whereIn($idColumn, $rows([self::NOT_INDEXED, self::UNKNOWN]));
                if ($ours) {
                    $query->whereNotIn($idColumn, $ours);
                }
                break;
            case 'excluded':
                $query->whereIn($idColumn, $rows(self::EXCLUDED));
                if ($ours) {
                    $query->whereNotIn($idColumn, $ours);
                }
                break;
            case 'ours':
                $query->whereIn($idColumn, $ours ?: [0])->whereNotIn($idColumn, $rows(self::INDEXED));
                break;
            case 'unchecked':
                $query->whereNotIn($idColumn, function ($q) use ($type) {
                    $q->select('entity_id')->from('seo_index_status')->where('entity_type', $type);
                });
                if ($ours) {
                    $query->whereNotIn($idColumn, $ours);
                }
                break;
        }
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

    /** Чи сутність закрита noindex нами (кеш у межах запиту) */
    public static function isOurs(string $type, int $id): bool
    {
        static $sets = [];
        if (!isset($sets[$type])) {
            $sets[$type] = array_flip(self::ourNoindexIds($type));
        }

        return isset($sets[$type][$id]);
    }

    public static function label(?object $row, bool $ours = false): array
    {
        if ($ours && (!$row || $row->state !== self::INDEXED)) {
            return ['Закрыто нами', 'light', 'noindex на нашей стороне (мало объявлений или стоп-слово) — отсутствие в индексе ожидаемо'
                . ($row && $row->checked_at ? ' · проверено ' . date('d.m.Y', strtotime($row->checked_at)) : '')];
        }
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
                return ['Google не взял', 'danger', (string) $row->coverage_state . $when];
        }

        return ['Неизвестно', 'secondary', (string) $row->coverage_state . $when];
    }

    public static function tableExists(): bool
    {
        static $exists = null;

        return $exists ?? ($exists = Schema::hasTable('seo_index_status'));
    }
}
