<?php

namespace App\Services\Seo;

use App\AdCategory;
use App\AdCity;
use App\AdCountry;
use App\Services\PriceStats;
use App\Services\SeoRules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Блок «Розділ у цифрах» для сторінки категорії (без фільтра міста, 1-ша сторінка):
 * скільки оголошень, нових/б/в, від магазинів/приватних, ціни (PriceStats), міста й
 * підрозділи з посиланнями, дата останнього оголошення. Унікальність сторінкам дають
 * реальні дані розділу, а не однаковий для всіх шаблонний текст.
 *
 * Без LLM і зовнішніх викликів. Кеш: доба на категорію/мову.
 */
class CategoryDataText
{
    const VERSION = 'v1';
    const MIN_ADS = 3;       // менше — блоку немає (сторінка й так noindex)
    const TOP_CITIES = 6;
    const TOP_CHILDREN = 8;

    public function build(AdCategory $category, array $categoryIds): string
    {
        if (SeoRules::isSensitiveCategory($category)) {
            return '';
        }
        $locale = app()->getLocale() === 'ru' ? 'ru' : 'uk';

        return Cache::remember('category_data_text:' . self::VERSION . ":{$category->id}:{$locale}", now()->addDay(), function () use ($category, $categoryIds, $locale) {
            return $this->generate($category, $categoryIds, $locale);
        });
    }

    /** Некешований варіант (консоль, тести) */
    public function generate(AdCategory $category, array $categoryIds, string $locale): string
    {
        $ru = $locale === 'ru';
        $name = e(trim((string) $category->name));
        $ids = array_values(array_unique(array_map('intval', $categoryIds ?: [$category->id])));

        $base = $this->baseQuery($ids);
        $total = (clone $base)->count();
        if ($total < self::MIN_ADS) {
            return '';
        }

        $byCondition = (clone $base)->selectRaw('ads.`condition` AS k, COUNT(*) AS n')->groupBy('ads.condition')->pluck('n', 'k')->all();
        $bySeller = (clone $base)->selectRaw('ads.is_product AS k, COUNT(*) AS n')->groupBy('ads.is_product')->pluck('n', 'k')->all();
        $latest = (clone $base)->max('ads.date_active');
        $cityCounts = (clone $base)->selectRaw('ads.city_id AS k, COUNT(*) AS n')->groupBy('ads.city_id')
            ->orderByDesc('n')->limit(self::TOP_CITIES)->pluck('n', 'k')->all();
        $childCounts = [];
        if (!$category->parent_id && count($ids) > 1) {
            $childCounts = (clone $base)->where('ads.category_id', '<>', $category->id)
                ->selectRaw('ads.category_id AS k, COUNT(*) AS n')->groupBy('ads.category_id')
                ->orderByDesc('n')->limit(self::TOP_CHILDREN)->pluck('n', 'k')->all();
        }

        $p = [];
        // 1. Обсяг і склад
        $line = $ru
            ? "Сейчас в разделе «{$name}» на Addnew.biz — <strong>" . $this->num($total) . '</strong> ' . $this->plural($total, ['актуальное объявление', 'актуальных объявления', 'актуальных объявлений']) . ' по Украине'
            : "Зараз у розділі «{$name}» на Addnew.biz — <strong>" . $this->num($total) . '</strong> ' . $this->plural($total, ['актуальне оголошення', 'актуальні оголошення', 'актуальних оголошень']) . ' по Україні';
        $parts = [];
        $new = (int) ($byCondition['new'] ?? 0);
        $used = (int) ($byCondition['used'] ?? 0);
        if ($new > 0 && $used > 0) {
            $parts[] = $ru ? 'новых — ' . $this->num($new) . ', б/у — ' . $this->num($used) : 'нових — ' . $this->num($new) . ', б/в — ' . $this->num($used);
        }
        $shops = (int) ($bySeller[1] ?? 0);
        $private = (int) ($bySeller[0] ?? 0);
        if ($shops > 0 && $private > 0) {
            $parts[] = $ru ? 'от магазинов — ' . $this->num($shops) . ', от частных продавцов — ' . $this->num($private)
                : 'від магазинів — ' . $this->num($shops) . ', від приватних продавців — ' . $this->num($private);
        } elseif ($shops > 0 && $private === 0) {
            $parts[] = $ru ? 'все — от интернет-магазинов' : 'усі — від інтернет-магазинів';
        }
        $p[] = $line . ($parts ? ': ' . implode('; ', $parts) : '') . '.'
            . ($latest ? ($ru ? ' Последнее добавлено ' : ' Останнє додано ') . date('d.m.Y', strtotime($latest)) . '.' : '');

        // 2. Ціни (лише якщо є надійна статистика ≥ PriceStats::MIN_ADS)
        $stats = PriceStats::forCategory($category);
        if ($stats) {
            $prices = $ru
                ? 'Типичная цена — <strong>' . $this->money($stats['median']) . ' грн</strong> (медиана по ' . $this->num($stats['count']) . ' объявлениям с ценой в гривнах); половина предложений — от ' . $this->money($stats['p25']) . ' до ' . $this->money($stats['p75']) . ' грн.'
                : 'Типова ціна — <strong>' . $this->money($stats['median']) . ' грн</strong> (медіана за ' . $this->num($stats['count']) . ' оголошеннями з ціною в гривнях); половина пропозицій — від ' . $this->money($stats['p25']) . ' до ' . $this->money($stats['p75']) . ' грн.';
            if (isset($stats['condition']['new'], $stats['condition']['used'])) {
                $prices .= $ru
                    ? ' Новое — около ' . $this->money($stats['condition']['new']['median']) . ' грн, б/у — около ' . $this->money($stats['condition']['used']['median']) . ' грн.'
                    : ' Нове — близько ' . $this->money($stats['condition']['new']['median']) . ' грн, б/в — близько ' . $this->money($stats['condition']['used']['median']) . ' грн.';
            }
            $prices .= ' <a href="' . e(\App\Http\Controllers\Front\Ad\Prices::url($category)) . '">' . ($ru ? 'Подробная статистика цен' : 'Детальна статистика цін') . '</a>.';
            $p[] = $prices;
        }

        $html = '<h2>' . ($ru ? "Раздел «{$name}» в цифрах" : "Розділ «{$name}» у цифрах") . '</h2>';
        foreach ($p as $para) {
            $html .= '<p>' . $para . '</p>';
        }

        // 3. Міста
        $cityLinks = $this->cityLinks($category, $cityCounts);
        if (count($cityLinks) >= 2) {
            $html .= '<p>' . ($ru ? 'Больше всего объявлений: ' : 'Найбільше оголошень: ') . implode(', ', $cityLinks) . '.</p>';
        }

        // 4. Підрозділи
        $childLinks = $this->childLinks($childCounts);
        if (count($childLinks) >= 2) {
            $html .= '<p>' . ($ru ? 'Популярные подразделы: ' : 'Популярні підрозділи: ') . implode(', ', $childLinks) . '.</p>';
        }

        return $html;
    }

    protected function baseQuery(array $ids)
    {
        $countryId = optional(AdCountry::getCurrentCountry())->id ?? 62;

        return DB::table('ads')
            ->join('ad_cities', 'ad_cities.id', '=', 'ads.city_id')
            ->join('ad_regions', 'ad_regions.id', '=', 'ad_cities.region_id')
            ->where('ad_regions.country_id', $countryId)
            ->where('ads.status', 1)
            ->where('ads.is_sensitive', 0)
            ->whereIn('ads.category_id', $ids);
    }

    protected function cityLinks(AdCategory $category, array $counts): array
    {
        if (!$counts) {
            return [];
        }
        $cities = AdCity::whereIn('id', array_keys($counts))->get()->keyBy('id');
        $out = [];
        foreach ($counts as $id => $n) {
            $city = $cities[$id] ?? null;
            if (!$city || trim((string) $city->name) === '') {
                continue;
            }
            $out[] = '<a href="' . e($this->localize($category->getFilteredUrl($city->slug))) . '">' . e($city->name) . '</a> (' . $this->num((int) $n) . ')';
        }

        return $out;
    }

    protected function childLinks(array $counts): array
    {
        if (!$counts) {
            return [];
        }
        $children = AdCategory::whereIn('id', array_keys($counts))->get()->keyBy('id');
        $sensitive = SeoRules::sensitiveCategoryIds();
        $out = [];
        foreach ($counts as $id => $n) {
            $child = $children[$id] ?? null;
            if (!$child || in_array($id, $sensitive) || trim((string) $child->name) === '') {
                continue;
            }
            $out[] = '<a href="' . e($this->localize($child->url)) . '">' . e($child->name) . '</a> (' . $this->num((int) $n) . ')';
        }

        return $out;
    }

    /** Маршрути категорій генерують українську адресу — для ru-сторінки додаємо /ru */
    protected function localize(string $url): string
    {
        if (app()->getLocale() !== 'ru') {
            return $url;
        }
        $path = (string) parse_url($url, PHP_URL_PATH);

        return strpos($path, '/ru/') === 0 ? $url : url('/ru' . $path);
    }

    protected function num(int $n): string
    {
        return number_format($n, 0, ',', ' ');
    }

    protected function money(float $v): string
    {
        return number_format(round($v), 0, ',', ' ');
    }

    protected function plural(int $n, array $forms): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) {
            return $forms[2];
        }
        if ($n1 > 1 && $n1 < 5) {
            return $forms[1];
        }

        return $n1 === 1 ? $forms[0] : $forms[2];
    }
}
