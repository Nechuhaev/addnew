<?php

namespace App\Services;

use App\AdCity;

use App\AdCategory;
use App\Localization\Localization;
use App\User;

/**
 * Спільні блоки головної та сторінок регіону/міста: категорії по колонках,
 * «популярні магазини» і «останні оголошення».
 */
class GeoPageBlocks
{
    /**
     * Батьківські категорії, згруповані в колонки за sort_order
     * (0–99 → 0, 100–199 → 1, 200–299 → 2, 300–399 → 3, інше → 0).
     * Порядок колонок — у порядку першої появи, як і раніше.
     *
     * @return array<int, AdCategory[]>
     */
    public static function parentCategoriesByColumn(): array
    {
        $columns = [];
        foreach (AdCategory::where('parent_id', 0)->orderBy('sort_order', 'ASC')->get() as $parent) {
            $columns[self::columnFor($parent['sort_order'])][] = $parent;
        }

        return $columns;
    }

    /**
     * Плоский список батьківських категорій з посиланнями, відфільтрованими
     * за регіоном/містом ($slug) — для сторінок регіону й міста.
     */
    /**
     * Кількість активних оголошень у містах області — по розділах (батьківська
     * категорія разом із підкатегоріями) і по містах. Один запит, кеш 1 год.
     *
     * @return array ['categories' => [category_id => n], 'cities' => [city_id => n], 'total' => n]
     */
    public static function regionCounts(int $regionId): array
    {
        return \Illuminate\Support\Facades\Cache::remember('region_counts_' . $regionId, 3600, function () use ($regionId) {
            return self::countActiveAds(function ($q) use ($regionId) {
                $q->where('ad_cities.region_id', $regionId);
            });
        });
    }

    /**
     * Те саме для сторінки міста: розділи — по самому місту, а «cities» —
     * по містах області (для списку сусідніх міст). Кеш 1 год.
     */
    public static function cityCounts(AdCity $city): array
    {
        return \Illuminate\Support\Facades\Cache::remember('city_counts_' . $city->id, 3600, function () use ($city) {
            $region = self::regionCounts((int) $city->region_id);
            $own = self::countActiveAds(function ($q) use ($city) {
                $q->where('ads.city_id', $city->id);
            });
            return ['categories' => $own['categories'], 'cities' => $region['cities'], 'total' => $own['total']];
        });
    }

    protected static function countActiveAds(\Closure $scope): array
    {
        $query = \Illuminate\Support\Facades\DB::table('ads')
            ->join('ad_cities', 'ad_cities.id', '=', 'ads.city_id')
            ->join('ad_categories', 'ad_categories.id', '=', 'ads.category_id')
            ->where('ads.status', 1)
            ->selectRaw('ads.city_id, IF(ad_categories.parent_id = 0, ad_categories.id, ad_categories.parent_id) AS root_id, COUNT(*) AS n')
            ->groupBy('ads.city_id', 'root_id');
        $scope($query);

        $out = ['categories' => [], 'cities' => [], 'total' => 0];
        foreach ($query->get() as $r) {
            $out['categories'][$r->root_id] = ($out['categories'][$r->root_id] ?? 0) + $r->n;
            $out['cities'][$r->city_id] = ($out['cities'][$r->city_id] ?? 0) + $r->n;
            $out['total'] += $r->n;
        }
        return $out;
    }

    public static function filteredCategories(string $slug): array
    {
        $categories = [];
        foreach (self::parentCategoriesByColumn() as $parents) {
            foreach ($parents as $parent) {
                $categories[] = [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'url' => $parent->getFilteredUrl($slug),
                    'image' => $parent->image,
                ];
            }
        }

        return $categories;
    }

    /**
     * Останні магазини з товарами в межах поточної країни
     */
    public static function latestShops(Localization $localization)
    {
        return User::withCount('ads')
            ->whereHas('ads', function ($query) use ($localization) {
                $query->where('is_product', 1)->whereIn('city_id', $localization->citiesIds());
            })
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();
    }

    /**
     * Останні оголошення (по одному на автора), розбиті на групи по 5
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public static function latestAdsGroups($query): array
    {
        $groups = [];
        foreach ($query->where('is_sensitive', 0)->orderBy('created_at', 'desc')->groupBy('user_id')->take(20)->get()->chunk(5) as $key => $group) {
            foreach ($group as $ad) {
                $groups[$key][] = [
                    'name' => $ad->name,
                    'url' => $ad->url,
                    'price' => $ad->formatted_price,
                    'image' => $ad->image
                ];
            }
        }

        return $groups;
    }

    private static function columnFor($sortOrder): int
    {
        foreach ([1 => [100, 199], 2 => [200, 299], 3 => [300, 399]] as $column => [$from, $to]) {
            if (in_array($sortOrder, range($from, $to))) {
                return $column;
            }
        }

        return 0;
    }
}
