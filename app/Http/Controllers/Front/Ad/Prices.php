<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\Http\Controllers\Controller;
use App\Services\PriceStats;

/**
 * Сторінки «Ціни в розділі …» (/ceny, /ceny/{slug}): статистика цін з реальних
 * оголошень для пошуку й AI-відповідей на запити «скільки коштує …».
 */
class Prices extends Controller
{
    const MONTHS_UK = ['січня', 'лютого', 'березня', 'квітня', 'травня', 'червня', 'липня', 'серпня', 'вересня', 'жовтня', 'листопада', 'грудня'];
    const MONTHS_RU = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    const MONTHS_UK_NOM = ['січень', 'лютий', 'березень', 'квітень', 'травень', 'червень', 'липень', 'серпень', 'вересень', 'жовтень', 'листопад', 'грудень'];
    const MONTHS_RU_NOM = ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];

    public function index()
    {
        $ru = $this->ru();
        $available = PriceStats::available();
        $categories = AdCategory::whereIn('id', array_keys($available))->get()->keyBy('id');

        $groups = [];
        foreach ($available as $id => $stats) {
            $category = $categories[$id] ?? null;
            if (!$category) {
                continue;
            }
            $rootId = $category->parent_id ?: $category->id;
            $row = ['name' => $category->name, 'url' => self::url($category), 'stats' => $stats];
            if ($category->parent_id) {
                $groups[$rootId]['children'][] = $row;
            } else {
                $groups[$rootId]['root'] = $row;
            }
        }
        // Підрозділи без сторінки батьківського розділу — окремою групою з назвою батька
        foreach ($groups as $rootId => &$g) {
            if (empty($g['root'])) {
                $parent = AdCategory::find($rootId);
                $g['root'] = ['name' => $parent ? $parent->name : '', 'url' => null, 'stats' => null];
            }
        }
        unset($g);
        uasort($groups, function ($a, $b) {
            return strcmp($a['root']['name'], $b['root']['name']);
        });

        $month = $this->monthYear();
        $meta = [
            'meta_title' => $ru ? "Цены в Украине по разделам объявлений, {$month} | Addnew.biz" : "Ціни в Україні за розділами оголошень, {$month} | Addnew.biz",
            'meta_description' => $ru
                ? 'Сколько стоят товары и услуги в Украине: медианные цены и типичный диапазон по разделам доски объявлений Addnew.biz. Статистика по реальным объявлениям, обновляется ежедневно.'
                : 'Скільки коштують товари й послуги в Україні: медіанні ціни та типовий діапазон за розділами дошки оголошень Addnew.biz. Статистика за реальними оголошеннями, оновлюється щодня.',
        ];

        return view('front.ad.prices-index')->with([
            'groups' => $groups,
            'meta' => $meta,
            'h1' => $ru ? 'Цены в Украине по разделам' : 'Ціни в Україні за розділами',
            'breadcrumbs' => 'prices.index',
            'date' => $this->date(now()->toDateString()),
        ]);
    }

    public function page($slug)
    {
        $ru = $this->ru();
        $category = AdCategory::where('slug', $slug)->first();
        if (!$category) {
            abort(404);
        }
        $stats = PriceStats::forCategory($category);
        if (!$stats) {
            abort(404);
        }

        $name = $category->name;
        $date = $this->date($stats['updated_at']);
        $uah = function ($v) {
            return number_format((float) $v, 0, ',', ' ') . ' грн';
        };

        // Міста
        $cityModels = AdCity::whereIn('id', array_keys($stats['cities']))->get()->keyBy('id');
        $cities = [];
        foreach ($stats['cities'] as $id => $c) {
            if (isset($cityModels[$id])) {
                $cities[] = ['name' => $cityModels[$id]->name, 'url' => $category->getFilteredUrl($cityModels[$id]->slug)] + $c;
            }
        }

        // Підрозділи (лише для батьківського розділу)
        $available = PriceStats::available();
        $children = [];
        if ($stats['children']) {
            $childModels = AdCategory::whereIn('id', array_keys($stats['children']))->get()->keyBy('id');
            foreach ($stats['children'] as $id => $c) {
                if (isset($childModels[$id])) {
                    $children[] = [
                        'name' => $childModels[$id]->name,
                        'url' => isset($available[$id]) ? self::url($childModels[$id]) : $childModels[$id]->url,
                    ] + $c;
                }
            }
        }

        $parent = $category->parent;
        $parentLink = ($parent && isset($available[$parent->id])) ? ['name' => $parent->name, 'url' => self::url($parent)] : null;

        // Висновки текстом (короткі відповіді для пошуку й AI)
        $summary = $ru
            ? "По состоянию на {$date} медианная цена в разделе «{$name}» на Addnew.biz — {$uah($stats['median'])}. Половина объявлений стоит от {$uah($stats['p25'])} до {$uah($stats['p75'])}, 90% — от {$uah($stats['p5'])} до {$uah($stats['p95'])}. Статистика построена по " . $this->num($stats['count']) . ' ' . $this->plural($stats['count'], ['актуальному объявлению', 'актуальным объявлениям', 'актуальным объявлениям']) . ' с ценой в гривнах по Украине.'
            : "Станом на {$date} медіанна ціна в розділі «{$name}» на Addnew.biz — {$uah($stats['median'])}. Половина оголошень коштує від {$uah($stats['p25'])} до {$uah($stats['p75'])}, 90% — від {$uah($stats['p5'])} до {$uah($stats['p95'])}. Статистику побудовано за " . $this->num($stats['count']) . ' ' . $this->plural($stats['count'], ['актуальним оголошенням', 'актуальними оголошеннями', 'актуальними оголошеннями']) . ' з ціною в гривнях по Україні.';

        $conditionText = null;
        if (isset($stats['condition']['new'], $stats['condition']['used'])) {
            $new = $stats['condition']['new']['median'];
            $used = $stats['condition']['used']['median'];
            $diff = $new > 0 ? round((1 - $used / $new) * 100) : 0;
            $conditionText = $ru
                ? "Новые — медиана {$uah($new)} (" . $this->num($stats['condition']['new']['count']) . " объявл.), б/у — {$uah($used)} (" . $this->num($stats['condition']['used']['count']) . ' объявл.).' . ($diff > 0 ? " Б/у в среднем дешевле на {$diff}%." : '')
                : "Нові — медіана {$uah($new)} (" . $this->num($stats['condition']['new']['count']) . " огол.), б/в — {$uah($used)} (" . $this->num($stats['condition']['used']['count']) . ' огол.).' . ($diff > 0 ? " Б/в у середньому дешевше на {$diff}%." : '');
        }

        $sellerText = null;
        if (isset($stats['seller']['shop'], $stats['seller']['private'])) {
            $sellerText = $ru
                ? "У интернет-магазинов медиана — {$uah($stats['seller']['shop']['median'])} (" . $this->num($stats['seller']['shop']['count']) . " объявл.), у частных продавцов — {$uah($stats['seller']['private']['median'])} (" . $this->num($stats['seller']['private']['count']) . ' объявл.).'
                : "В інтернет-магазинів медіана — {$uah($stats['seller']['shop']['median'])} (" . $this->num($stats['seller']['shop']['count']) . " огол.), у приватних продавців — {$uah($stats['seller']['private']['median'])} (" . $this->num($stats['seller']['private']['count']) . ' огол.).';
        }

        // FAQ
        $faq = [[
            $ru ? "Сколько стоят товары и услуги в разделе «{$name}» в Украине?" : "Скільки коштують товари й послуги в розділі «{$name}» в Україні?",
            $ru
                ? "Медианная цена — {$uah($stats['median'])}, половина предложений — от {$uah($stats['p25'])} до {$uah($stats['p75'])} (данные Addnew.biz на {$date})."
                : "Медіанна ціна — {$uah($stats['median'])}, половина пропозицій — від {$uah($stats['p25'])} до {$uah($stats['p75'])} (дані Addnew.biz на {$date}).",
        ]];
        $bigCities = array_values(array_filter($cities, function ($c) {
            return $c['count'] >= PriceStats::MIN_CHILD;
        }));
        if (count($bigCities) >= 2) {
            usort($bigCities, function ($a, $b) {
                return $a['median'] <=> $b['median'];
            });
            $cheap = $bigCities[0];
            $faq[] = [
                $ru ? "В каком городе дешевле всего «{$name}»?" : "У якому місті найдешевше «{$name}»?",
                $ru
                    ? "Среди городов, где не меньше " . PriceStats::MIN_CHILD . " объявлений, самая низкая медианная цена — в городе {$cheap['name']}: {$uah($cheap['median'])}."
                    : "Серед міст, де щонайменше " . PriceStats::MIN_CHILD . " оголошень, найнижча медіанна ціна — у місті {$cheap['name']}: {$uah($cheap['median'])}.",
            ];
        }
        if ($conditionText) {
            $faq[] = [$ru ? 'Насколько б/у дешевле нового?' : 'Наскільки б/в дешевше за нове?', $conditionText];
        }
        $faq[] = [
            $ru ? 'Как считаются эти цены?' : 'Як рахуються ці ціни?',
            $ru
                ? 'Учитываются только активные объявления в Украине с ценой в гривнах больше нуля. Мы показываем медиану, а не среднее: так единичные очень дешёвые или очень дорогие объявления не искажают картину. Данные обновляются ежедневно.'
                : 'Враховуються лише активні оголошення в Україні з ціною в гривнях понад нуль. Ми показуємо медіану, а не середнє: так поодинокі дуже дешеві чи дуже дорогі оголошення не спотворюють картину. Дані оновлюються щодня.',
        ];

        // Свіжі оголошення розділу з ціною
        $ids = array_merge([$category->id], $category->parent_id ? [] : $category->children()->pluck('id')->all());
        $ads = Ad::getLoopArray(Ad::getAds()->whereIn('ads.category_id', $ids)->where('ads.price', '>', 0)->where('ads.is_sensitive', 0)->limit(6)->get());

        $month = $this->monthYear();
        $meta = [
            'meta_title' => $ru ? "{$name}: цены в Украине, {$month} | Addnew.biz" : "{$name}: ціни в Україні, {$month} | Addnew.biz",
            'meta_description' => $ru
                ? "Сколько стоит «{$name}» в Украине: медианная цена {$uah($stats['median'])}, половина предложений от {$uah($stats['p25'])} до {$uah($stats['p75'])}. Статистика по " . $this->num($stats['count']) . ' объявлениям, обновляется ежедневно.'
                : "Скільки коштує «{$name}» в Україні: медіанна ціна {$uah($stats['median'])}, половина пропозицій від {$uah($stats['p25'])} до {$uah($stats['p75'])}. Статистика за " . $this->num($stats['count']) . ' оголошеннями, оновлюється щодня.',
        ];

        $dataset = [
            '@context' => 'https://schema.org',
            '@type' => 'Dataset',
            'name' => $meta['meta_title'],
            'description' => $summary,
            'url' => url()->current(),
            'inLanguage' => $ru ? 'ru' : 'uk',
            'isAccessibleForFree' => true,
            'dateModified' => $stats['updated_at'],
            'spatialCoverage' => ['@type' => 'Place', 'name' => $ru ? 'Украина' : 'Україна'],
            'variableMeasured' => $ru ? 'Цена в объявлениях, грн' : 'Ціна в оголошеннях, грн',
            'creator' => ['@type' => 'Organization', 'name' => 'Addnew.biz', 'url' => url('/')],
        ];

        return view('front.ad.prices')->with([
            'entity' => $category,
            'stats' => $stats,
            'name' => $name,
            'h1' => $ru ? "{$name}: цены в Украине" : "{$name}: ціни в Україні",
            'summary' => $summary,
            'conditionText' => $conditionText,
            'sellerText' => $sellerText,
            'cities' => $cities,
            'children' => $children,
            'parentLink' => $parentLink,
            'faq' => $faq,
            'ads' => $ads,
            'date' => $date,
            'meta' => $meta,
            'dataset' => $dataset,
            'breadcrumbs' => 'prices.page',
            'indexUrl' => self::indexUrl(),
        ]);
    }

    public static function url(AdCategory $category): string
    {
        return url((app()->getLocale() === 'ru' ? '/ru' : '') . '/ceny/' . $category->slug);
    }

    public static function indexUrl(): string
    {
        return url((app()->getLocale() === 'ru' ? '/ru' : '') . '/ceny');
    }

    protected function ru(): bool
    {
        return app()->getLocale() === 'ru';
    }

    protected function date(string $ymd): string
    {
        $t = strtotime($ymd);
        $months = $this->ru() ? self::MONTHS_RU : self::MONTHS_UK;

        return (int) date('j', $t) . ' ' . $months[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
    }

    protected function monthYear(): string
    {
        $months = $this->ru() ? self::MONTHS_RU_NOM : self::MONTHS_UK_NOM;

        return $months[(int) date('n') - 1] . ' ' . date('Y');
    }

    protected function num(int $n): string
    {
        return number_format($n, 0, ',', ' ');
    }

    protected function plural(int $n, array $forms): string
    {
        $m10 = $n % 10;
        $m100 = $n % 100;
        if ($m10 === 1 && $m100 !== 11) {
            return $forms[0];
        }
        if ($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) {
            return $forms[1];
        }

        return $forms[2];
    }
}
