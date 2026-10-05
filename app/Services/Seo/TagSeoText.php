<?php

namespace App\Services\Seo;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\AdTag;
use App\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Короткий фактичний вступ для сторінки тегу (/ad-tag/{slug}), який
 * показується ЛИШЕ там, де в тегу немає власного (AI) тексту.
 *
 * Замінює універсальний шаблон SeoField 'ad-tag' з підстановкою назви,
 * однаковий для ~22 тисяч сторінок. Унікальність дають дані тегу:
 * кількість оголошень, топ-категорії, міста, діапазон цін.
 *
 * Без LLM, без зовнішніх викликів. Кеш: 12 годин на тег/мову/кількість.
 */
class TagSeoText
{
    public function build(AdTag $tag, int $total): string
    {
        $locale = app()->getLocale() === 'ru' ? 'ru' : 'uk';

        return Cache::remember("tag_seo_text:{$tag->id}:{$locale}:{$total}", now()->addHours(12), function () use ($tag, $total, $locale) {
            return $this->generate($tag, $total, $locale);
        });
    }

    /**
     * Некешований варіант (для перегляду з консолі й тестів).
     */
    public function generate(AdTag $tag, int $total, string $locale): string
    {
        $ru = $locale === 'ru';
        $name = trim((string) $tag->name);
        if ($name === '' || $total < 1) {
            return '';
        }

        // --- Топ-категорії (3) та міста (2) за оголошеннями тегу ---
        $catIds = DB::table('ad_tag')
            ->join('ads', 'ads.id', '=', 'ad_tag.ad_id')
            ->where('ad_tag.tag_id', $tag->id)
            ->whereNotNull('ads.category_id')
            ->select('ads.category_id', DB::raw('COUNT(*) AS c'))
            ->groupBy('ads.category_id')
            ->orderByDesc('c')
            ->limit(3)
            ->pluck('category_id')
            ->all();
        $catNames = $this->namesById(AdCategory::class, $catIds);

        $cityIds = DB::table('ad_tag')
            ->join('ads', 'ads.id', '=', 'ad_tag.ad_id')
            ->where('ad_tag.tag_id', $tag->id)
            ->whereNotNull('ads.city_id')
            ->select('ads.city_id', DB::raw('COUNT(*) AS c'))
            ->groupBy('ads.city_id')
            ->orderByDesc('c')
            ->limit(2)
            ->pluck('city_id')
            ->all();
        $cityNames = $this->namesById(AdCity::class, $cityIds);

        $range = PriceRange::summarize(function ($q) use ($tag) {
            $q->join('ad_tag', 'ad_tag.ad_id', '=', 'ads.id')->where('ad_tag.tag_id', $tag->id);
        });

        // --- Текст ---
        $nameE = e($name);

        $p1 = $ru
            ? 'По тегу «' . $nameE . '» на Addnew.biz опубликовано ' . $total . ' ' . $this->plural($total, ['объявление', 'объявления', 'объявлений']) . '.'
            : 'За тегом «' . $nameE . '» на Addnew.biz опубліковано ' . $total . ' ' . $this->plural($total, ['оголошення', 'оголошення', 'оголошень']) . '.';

        $p2 = '';
        if ($catNames) {
            $p2 .= ($ru ? 'Чаще всего такие объявления размещают в разделах: ' : 'Найчастіше такі оголошення розміщують у розділах: ')
                . e(implode(', ', $catNames)) . '. ';
        }
        if ($cityNames) {
            $p2 .= ($ru ? 'География объявлений: ' : 'Географія оголошень: ') . e(implode(', ', $cityNames)) . '.';
        }

        $p3 = '';
        if ($range) {
            $lead = $range['approx']
                ? ($ru ? 'Большинство цен — от ' : 'Більшість цін — від ')
                : ($ru ? 'Цены — от ' : 'Ціни — від ');
            $p3 = $lead . e($range['lo']) . ' до ' . e($range['hi']) . '.';
        }

        $html = '<h2>' . ($ru ? 'Объявления по тегу «' : 'Оголошення за тегом «') . $nameE . '»</h2>';
        foreach ([$p1, $p2, $p3] as $p) {
            $p = trim($p);
            if ($p !== '') {
                $html .= '<p>' . $p . '</p>';
            }
        }

        return $html;
    }

    protected function namesById(string $model, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $items = $model::whereIn('id', $ids)->get()->keyBy('id');
        $out = [];
        foreach ($ids as $id) {
            if (isset($items[$id]) && trim((string) $items[$id]->name) !== '') {
                $out[] = trim((string) $items[$id]->name);
            }
        }
        return $out;
    }

    protected function plural(int $n, array $forms): string
    {
        return $forms[Translation::slavicPluralIndex(abs($n))];
    }
}
