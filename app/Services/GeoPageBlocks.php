<?php

namespace App\Services;

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
    public static function filteredCategories(string $slug): array
    {
        $categories = [];
        foreach (self::parentCategoriesByColumn() as $parents) {
            foreach ($parents as $parent) {
                $categories[] = [
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
        foreach ($query->orderBy('created_at', 'desc')->groupBy('user_id')->take(20)->get()->chunk(5) as $key => $group) {
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
