<?php

namespace App\Services;

use App\AdCategory;
use App\AdCity;
use App\AdRegion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Блок «Часті запитання» для сторінок категорій, областей і міст (+ FAQPage JSON-LD).
 * Відповіді збираються лише з даних сайту (кількість оголошень, ціни в гривнях,
 * популярні розділи/міста) і з того, як справді працює сайт (безкоштовна подача,
 * без обов'язкової реєстрації, чат, рейтинг магазинів). Питання без даних не виводяться.
 */
class GeoFaq
{
    const TTL = 21600; // 6 год

    /**
     * @param AdCategory              $category
     * @param AdRegion|AdCity|null    $place     фільтр /r/{place}/{category}
     * @param int                     $total     кількість оголошень у списку
     * @param array|null              $filters   міста/області з кількістю (getFilters категорії)
     * @param int[]                   $categoryIds категорія + підкатегорії
     */
    public static function forCategory(AdCategory $category, $place, int $total, ?array $filters, array $categoryIds): array
    {
        $ru = self::ru();
        $name = $category->name;
        $where = $place ? self::placePhrase($place) : ($ru ? 'в Украине' : 'в Україні');
        $faq = [];

        if ($total > 0) {
            $faq[] = [
                $ru ? "Сколько объявлений в разделе «{$name}» {$where}?" : "Скільки оголошень у розділі «{$name}» {$where}?",
                $ru
                    ? "Сейчас на Addnew.biz " . self::num($total) . ' ' . self::plural($total, ['актуальное объявление', 'актуальных объявления', 'актуальных объявлений']) . " в разделе «{$name}» {$where}."
                    : "Зараз на Addnew.biz " . self::num($total) . ' ' . self::plural($total, ['актуальне оголошення', 'актуальні оголошення', 'актуальних оголошень']) . " у розділі «{$name}» {$where}.",
            ];
        }

        $prices = self::uahPriceRange($categoryIds, $place);
        if ($prices) {
            $faq[] = [
                $ru ? "Сколько стоят товары и услуги в разделе «{$name}»?" : "Скільки коштують товари й послуги в розділі «{$name}»?",
                $ru
                    ? "Цены в объявлениях в гривнях — от " . self::money($prices[0]) . " до " . self::money($prices[1]) . " грн, медианная цена — " . self::money($prices[2]) . " грн. Цену и условия уточняйте у продавца: часть объявлений допускает торг."
                    : "Ціни в оголошеннях у гривнях — від " . self::money($prices[0]) . " до " . self::money($prices[1]) . " грн, медіанна ціна — " . self::money($prices[2]) . " грн. Ціну й умови уточнюйте в продавця: частина оголошень допускає торг.",
            ];
        }

        $top = array_slice(array_filter((array) $filters, function ($f) {
            return ($f['ads_count'] ?? 0) > 0;
        }), 0, 3);
        if (count($top) >= 2) {
            $list = implode(', ', array_map(function ($f) {
                return $f['name'] . ' (' . self::num((int) $f['ads_count']) . ')';
            }, $top));
            $faq[] = [
                $place
                    ? ($ru ? "В каких городах больше всего объявлений «{$name}»?" : "У яких містах найбільше оголошень «{$name}»?")
                    : ($ru ? "В каких областях больше всего объявлений «{$name}»?" : "В яких областях найбільше оголошень «{$name}»?"),
                ($ru ? 'Больше всего объявлений: ' : 'Найбільше оголошень: ') . $list . '.',
            ];
        }

        $faq[] = [
            $ru ? "Как подать объявление в раздел «{$name}»?" : "Як подати оголошення в розділ «{$name}»?",
            $ru
                ? "Бесплатно: нажмите «Подать объявление», выберите раздел «{$name}», город, добавьте описание, фото и контакты. Регистрация не обязательна."
                : "Безкоштовно: натисніть «Подати оголошення», оберіть розділ «{$name}», місто, додайте опис, фото й контакти. Реєстрація не обов'язкова.",
        ];
        $faq[] = self::safetyItem();

        return $faq;
    }

    /**
     * @param AdRegion|AdCity $place
     * @param array $counts ['categories' => [root_id => n], 'cities' => [city_id => n], 'total' => n]
     */
    public static function forPlace($place, array $counts): array
    {
        $ru = self::ru();
        $where = self::placePhrase($place);
        $total = (int) ($counts['total'] ?? 0);
        $faq = [];

        if ($total > 0) {
            $faq[] = [
                $ru ? "Сколько объявлений {$where}?" : "Скільки оголошень {$where}?",
                $ru
                    ? "На Addnew.biz сейчас " . self::num($total) . ' ' . self::plural($total, ['актуальное объявление', 'актуальных объявления', 'актуальных объявлений']) . " {$where}: частные объявления и товары интернет-магазинов."
                    : "На Addnew.biz зараз " . self::num($total) . ' ' . self::plural($total, ['актуальне оголошення', 'актуальні оголошення', 'актуальних оголошень']) . " {$where}: приватні оголошення й товари інтернет-магазинів.",
            ];
        }

        $cats = array_filter((array) ($counts['categories'] ?? []));
        arsort($cats);
        $cats = array_slice($cats, 0, 3, true);
        if (count($cats) >= 2) {
            $names = AdCategory::whereIn('id', array_keys($cats))->get()->keyBy('id');
            $list = [];
            foreach ($cats as $id => $n) {
                if (isset($names[$id])) {
                    $list[] = $names[$id]->name . ' (' . self::num((int) $n) . ')';
                }
            }
            if (count($list) >= 2) {
                $faq[] = [
                    $ru ? "Какие разделы самые популярные {$where}?" : "Які розділи найпопулярніші {$where}?",
                    ($ru ? 'Больше всего объявлений в разделах: ' : 'Найбільше оголошень у розділах: ') . implode(', ', $list) . '.',
                ];
            }
        }

        if ($place instanceof AdRegion) {
            $cities = array_filter((array) ($counts['cities'] ?? []));
            arsort($cities);
            $cities = array_slice($cities, 0, 3, true);
            if (count($cities) >= 2) {
                $names = AdCity::whereIn('id', array_keys($cities))->get()->keyBy('id');
                $list = [];
                foreach ($cities as $id => $n) {
                    if (isset($names[$id])) {
                        $list[] = $names[$id]->name . ' (' . self::num((int) $n) . ')';
                    }
                }
                if (count($list) >= 2) {
                    $faq[] = [
                        $ru ? "В каких городах региона «{$place->name}» больше всего объявлений?" : "У яких містах регіону «{$place->name}» найбільше оголошень?",
                        ($ru ? 'Лидируют: ' : 'Лідирують: ') . implode(', ', $list) . '.',
                    ];
                }
            }
        }

        $isCity = $place instanceof AdCity;
        $choose = $isCity
            ? ($ru ? "город — {$place->name}" : "місто — {$place->name}")
            : ($ru ? "город в регионе «{$place->name}»" : "місто в регіоні «{$place->name}»");
        $faq[] = [
            $ru ? "Как подать объявление {$where}?" : "Як подати оголошення {$where}?",
            $ru
                ? "Нажмите «Подать объявление», выберите раздел и {$choose}, добавьте описание, фото и контакты. Размещение бесплатное, регистрация не обязательна."
                : "Натисніть «Подати оголошення», оберіть розділ і {$choose}, додайте опис, фото й контакти. Розміщення безкоштовне, реєстрація не обов'язкова.",
        ];
        $faq[] = self::safetyItem();

        return $faq;
    }

    /**
     * FAQPage для JSON-LD.
     */
    /**
     * Ручні FAQ у тексті розділу: блок <div class="article-faq"> з парами <h3>питання</h3><p>відповідь</p>
     * (як у статтях блогу). Повертає [пари, текст без блоку і без заголовка «Часті запитання» перед ним] —
     * на сторінці лишається один блок FAQ і один FAQPage.
     */
    public static function extractFromHtml(string $html): array
    {
        if (!preg_match('~(?:<h2[^>]*>[^<]*(?:Часті запитання|Частые вопросы)[^<]*</h2>\s*)?<div class="article-faq">(.*?)</div>~su', $html, $m)) {
            return [[], $html];
        }
        $pairs = [];
        if (preg_match_all('~<h3[^>]*>(.*?)</h3>\s*<p[^>]*>(.*?)</p>~su', $m[1], $found, PREG_SET_ORDER)) {
            foreach ($found as $p) {
                $q = trim(html_entity_decode(strip_tags($p[1]), ENT_QUOTES, 'UTF-8'));
                $a = trim(html_entity_decode(strip_tags($p[2]), ENT_QUOTES, 'UTF-8'));
                if ($q !== '' && $a !== '') {
                    $pairs[] = [$q, $a];
                }
            }
        }

        return [$pairs, str_replace($m[0], '', $html)];
    }

    public static function schema(array $faq): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function ($item) {
                return [
                    '@type' => 'Question',
                    'name' => $item[0],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item[1]],
                ];
            }, $faq),
        ];
    }

    protected static function safetyItem(): array
    {
        return self::ru()
            ? ['Как безопасно купить по объявлению?', 'Не вносите предоплату незнакомым продавцам, проверяйте товар при получении, общайтесь через чат сайта. У интернет-магазинов смотрите рейтинг и отзывы покупателей — они показаны рядом с названием магазина.']
            : ['Як безпечно купити за оголошенням?', 'Не вносьте передоплату незнайомим продавцям, перевіряйте товар при отриманні, спілкуйтеся через чат сайту. В інтернет-магазинів дивіться рейтинг і відгуки покупців — вони показані поруч із назвою магазину.'];
    }

    /**
     * Мін., макс. і медіана цін (лише гривня, ціна > 0) серед активних оголошень. Кеш 6 год.
     *
     * @return array|null [min, max, median]
     */
    protected static function uahPriceRange(array $categoryIds, $place): ?array
    {
        $placeKey = $place ? (($place instanceof AdCity ? 'c' : 'r') . $place->id) : 'all';
        $key = 'geo_faq_prices_' . md5(implode(',', $categoryIds) . '|' . $placeKey);

        $prices = Cache::remember($key, self::TTL, function () use ($categoryIds, $place) {
            $q = DB::table('ads')
                ->join('ad_currencies', 'ad_currencies.id', '=', 'ads.currency_id')
                ->where('ads.status', 1)
                ->where('ads.price', '>', 0)
                ->where('ad_currencies.code', 'UAH')
                ->whereIn('ads.category_id', $categoryIds);
            if ($place instanceof AdCity) {
                $q->where('ads.city_id', $place->id);
            } elseif ($place instanceof AdRegion) {
                $q->join('ad_cities', 'ad_cities.id', '=', 'ads.city_id')->where('ad_cities.region_id', $place->id);
            }
            $n = (clone $q)->count();
            if ($n < 3) {
                return [];   // замало оголошень для чесного діапазону
            }
            $row = (clone $q)->selectRaw('MIN(ads.price) AS mn, MAX(ads.price) AS mx')->first();
            $median = (clone $q)->orderBy('ads.price')->offset(intdiv($n, 2))->limit(1)->value('ads.price');

            return [(float) $row->mn, (float) $row->mx, (float) $median];
        });

        return $prices ?: null;
    }

    /** «у місті Київ», «у регіоні Київська обл» */
    protected static function placePhrase($place): string
    {
        if ($place instanceof AdCity) {
            return (self::ru() ? 'в городе ' : 'у місті ') . $place->name;
        }

        return (self::ru() ? 'в регионе ' : 'у регіоні ') . $place->name;
    }

    protected static function ru(): bool
    {
        return app()->getLocale() === 'ru';
    }

    protected static function num(int $n): string
    {
        return number_format($n, 0, ',', ' ');
    }

    protected static function money(float $v): string
    {
        return number_format($v, $v == floor($v) ? 0 : 2, ',', ' ');
    }

    protected static function plural(int $n, array $forms): string
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
