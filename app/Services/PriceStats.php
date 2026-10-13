<?php

namespace App\Services;

use App\AdCategory;
use App\AdCountry;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Статистика цін розділу для сторінок /ceny/{категорія}: медіана, квартилі,
 * нове/б/в, магазини/приватні, міста, підрозділи. Рахується з активних
 * оголошень в Україні з ціною в гривнях (без чутливих). Кеш — до наступного
 * прогріву (prices:warm щодня), тож на сторінці немає важких запитів.
 */
class PriceStats
{
    const MIN_ADS = 30;          // менше — сторінки цін немає (ненадійна статистика)
    const MIN_GROUP = 5;         // мінімум оголошень для медіани міста / стану / продавця
    const MIN_CHILD = 10;        // мінімум для рядка підрозділу
    const TTL = 93600;           // 26 год
    const VERSION = 'v1';

    /**
     * @return array|null null — сторінки цін для розділу немає
     */
    public static function forCategory(AdCategory $category, bool $refresh = false): ?array
    {
        if (SeoRules::isSensitiveCategory($category)) {
            return null;
        }
        $key = self::key($category->id);
        if ($refresh) {
            Cache::forget($key);
        }
        $stats = Cache::remember($key, self::TTL, function () use ($category) {
            return self::compute($category) ?: [];
        });

        return $stats ?: null;
    }

    public static function key(int $categoryId): string
    {
        return 'price_stats_' . self::VERSION . '_' . $categoryId;
    }

    /**
     * Розділи, для яких є сторінка цін: [category_id => stats], батьківські першими.
     */
    public static function available(): array
    {
        return Cache::remember('price_stats_' . self::VERSION . '_index', self::TTL, function () {
            $out = [];
            foreach (self::candidateCategories() as $category) {
                $stats = self::forCategory($category);
                if ($stats) {
                    $out[$category->id] = $stats;
                }
            }
            return $out;
        });
    }

    /** Перерахувати все (команда prices:warm). */
    public static function warm(): int
    {
        Cache::forget('price_stats_' . self::VERSION . '_index');
        $n = 0;
        foreach (self::candidateCategories() as $category) {
            if (self::forCategory($category, true)) {
                $n++;
            }
        }
        self::available();

        return $n;
    }

    /**
     * Категорії, де взагалі може набратися MIN_ADS оголошень з ціною (дешевий груповий запит).
     *
     * @return AdCategory[]
     */
    protected static function candidateCategories(): array
    {
        $counts = self::baseQuery()
            ->selectRaw('ads.category_id, COUNT(*) AS n')
            ->groupBy('ads.category_id')
            ->pluck('n', 'ads.category_id')
            ->all();

        $categories = AdCategory::orderBy('parent_id')->orderBy('id')->get();
        $out = [];
        foreach ($categories as $category) {
            $n = (int) ($counts[$category->id] ?? 0);
            if (!$category->parent_id) {
                foreach ($categories as $child) {
                    if ((int) $child->parent_id === (int) $category->id) {
                        $n += (int) ($counts[$child->id] ?? 0);
                    }
                }
            }
            if ($n >= self::MIN_ADS && !SeoRules::isSensitiveCategory($category)) {
                $out[] = $category;
            }
        }

        return $out;
    }

    protected static function baseQuery()
    {
        $countryId = optional(AdCountry::getCurrentCountry())->id ?? 62;

        return DB::table('ads')
            ->join('ad_currencies', 'ad_currencies.id', '=', 'ads.currency_id')
            ->join('ad_cities', 'ad_cities.id', '=', 'ads.city_id')
            ->join('ad_regions', 'ad_regions.id', '=', 'ad_cities.region_id')
            ->where('ad_regions.country_id', $countryId)
            ->where('ads.status', 1)
            ->where('ads.is_sensitive', 0)
            ->where('ads.price', '>', 0)
            ->where('ad_currencies.code', 'UAH');
    }

    protected static function compute(AdCategory $category): ?array
    {
        $childIds = $category->parent_id ? [] : $category->children()->pluck('id')->all();
        $ids = array_merge([$category->id], $childIds);

        $rows = self::baseQuery()
            ->whereIn('ads.category_id', $ids)
            ->select(['ads.price', 'ads.category_id', 'ads.city_id', 'ads.condition', 'ads.is_product'])
            ->get();

        if ($rows->count() < self::MIN_ADS) {
            return null;
        }

        $all = [];
        $byCondition = ['new' => [], 'used' => []];
        $bySeller = ['shop' => [], 'private' => []];
        $byCity = [];
        $byChild = [];
        foreach ($rows as $r) {
            $p = (float) $r->price;
            $all[] = $p;
            if (isset($byCondition[$r->condition])) {
                $byCondition[$r->condition][] = $p;
            }
            $bySeller[$r->is_product ? 'shop' : 'private'][] = $p;
            $byCity[$r->city_id][] = $p;
            if ((int) $r->category_id !== (int) $category->id) {
                $byChild[$r->category_id][] = $p;
            }
        }

        $groups = function (array $groups, int $min) {
            $out = [];
            foreach ($groups as $k => $prices) {
                if (count($prices) >= $min) {
                    $out[$k] = ['count' => count($prices), 'median' => self::percentile($prices, 50)];
                }
            }
            return $out;
        };

        $cities = $groups($byCity, self::MIN_GROUP);
        uasort($cities, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $children = $groups($byChild, self::MIN_CHILD);
        uasort($children, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return [
            'category_id' => $category->id,
            'count' => count($all),
            'p5' => self::percentile($all, 5),
            'p25' => self::percentile($all, 25),
            'median' => self::percentile($all, 50),
            'p75' => self::percentile($all, 75),
            'p95' => self::percentile($all, 95),
            'condition' => $groups($byCondition, self::MIN_GROUP),
            'seller' => $groups($bySeller, self::MIN_GROUP),
            'cities' => array_slice($cities, 0, 10, true),
            'children' => $children,
            'updated_at' => now()->toDateString(),
        ];
    }

    /** Перцентиль з лінійною інтерполяцією, округлений до гривні. */
    public static function percentile(array $values, float $p): float
    {
        sort($values, SORT_NUMERIC);
        $n = count($values);
        if ($n === 0) {
            return 0;
        }
        $pos = ($n - 1) * $p / 100;
        $lo = (int) floor($pos);
        $hi = (int) ceil($pos);
        $v = $values[$lo] + ($values[$hi] - $values[$lo]) * ($pos - $lo);

        return round($v);
    }
}
