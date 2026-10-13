<?php

namespace App\Http\Controllers\Front\Seo;

use App\AdCategory;
use App\AdRegion;
use App\Http\Controllers\Controller;
use App\Services\SeoRules;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * /llms.txt — короткий опис сайту для AI-пошуку (ChatGPT, Perplexity, Gemini, Claude):
 * що це за сайт, головні розділи й регіони з кількістю оголошень, магазини, блог, мови, контакти.
 * Формат — Markdown за пропозицією llmstxt.org. Кеш 24 год.
 */
class LlmsTxtController extends Controller
{
    public function show()
    {
        $text = Cache::remember('llms_txt_v1', 86400, function () {
            return $this->build();
        });

        return response($text, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    protected function build(): string
    {
        app()->setLocale('uk');
        $home = rtrim(url('/'), '/');

        // Активні оголошення по кореневих категоріях
        $byRoot = DB::table('ads')
            ->join('ad_categories', 'ad_categories.id', '=', 'ads.category_id')
            ->where('ads.status', 1)
            ->selectRaw('IF(ad_categories.parent_id = 0, ad_categories.id, ad_categories.parent_id) AS root_id, COUNT(*) AS n')
            ->groupBy('root_id')
            ->pluck('n', 'root_id');
        $total = (int) $byRoot->sum();

        $lines = [];
        $lines[] = '# Addnew.biz';
        $lines[] = '';
        $lines[] = '> Addnew.biz — безкоштовна дошка оголошень України: приватні оголошення й товари інтернет-магазинів. Розміщення й переходи безкоштовні, без комісії. Сайт двомовний: українська (за замовчуванням) і російська (/ru/).';
        $lines[] = '';
        $lines[] = '- Актуальних оголошень: ' . number_format($total, 0, ',', ' ');
        $lines[] = '- Подати оголошення можна без реєстрації; магазини завантажують товари з YML/XML/CSV-фідів, ціни й наявність оновлюються автоматично.';
        $lines[] = '- На сторінках товарів: історія ціни, «Інші продавці» того самого товару, рейтинг і відгуки магазину, чат із продавцем.';
        $lines[] = '- English: Addnew.biz is a free classifieds board for Ukraine (private listings and online-store products), in Ukrainian and Russian.';
        $lines[] = '';

        $lines[] = '## Розділи';
        $lines[] = '';
        $roots = AdCategory::where('parent_id', 0)->get()->sortByDesc(function ($c) use ($byRoot) {
            return (int) ($byRoot[$c->id] ?? 0);
        });
        foreach ($roots as $c) {
            $n = (int) ($byRoot[$c->id] ?? 0);
            if ($n > 0) {
                $lines[] = "- [{$c->name}]({$c->url}): " . $this->ads($n);
            }
        }
        $lines[] = '';

        $lines[] = '## Регіони України';
        $lines[] = '';
        $byRegion = DB::table('ads')
            ->join('ad_cities', 'ad_cities.id', '=', 'ads.city_id')
            ->join('ad_regions', 'ad_regions.id', '=', 'ad_cities.region_id')
            ->join('ad_countries', 'ad_countries.id', '=', 'ad_regions.country_id')
            ->where('ads.status', 1)
            ->where('ad_countries.slug', 'ukraina')
            ->whereNotIn('ad_countries.slug', SeoRules::noindexCountries())
            ->selectRaw('ad_regions.id, COUNT(*) AS n')
            ->groupBy('ad_regions.id')
            ->orderByDesc('n')
            ->limit(15)
            ->pluck('n', 'id');
        $regions = AdRegion::with('country')->whereIn('id', $byRegion->keys())->get()->keyBy('id');
        foreach ($byRegion as $id => $n) {
            if (isset($regions[$id])) {
                $lines[] = "- [{$regions[$id]->name}]({$regions[$id]->url}): " . $this->ads((int) $n);
            }
        }
        $lines[] = '- [Усі регіони](' . route('country.regions') . ')';
        $lines[] = '';

        $lines[] = '## Інше';
        $lines[] = '';
        $lines[] = '- [Інтернет-магазини](' . route('stores') . '): каталог магазинів, що розміщують товари на сайті';
        $lines[] = '- [Блог](' . route('blog.index') . '): поради покупцям і продавцям';
        $lines[] = '- [Подати оголошення](' . route('ad.step.category') . ')';
        $lines[] = '- [Контакти](' . route('contacts') . '): info@addnew.biz';
        $lines[] = '- [Російська версія](' . $home . '/ru)';
        $lines[] = '- [Sitemap](' . $home . '/sitemap.xml)';
        $lines[] = '';

        return implode("\n", $lines);
    }

    /** «1 оголошення», «3 оголошення», «25 оголошень» */
    protected function ads(int $n): string
    {
        $m10 = $n % 10;
        $m100 = $n % 100;
        $word = ($m10 === 1 && $m100 !== 11) || ($m10 >= 2 && $m10 <= 4 && ($m100 < 12 || $m100 > 14)) ? 'оголошення' : 'оголошень';

        return number_format($n, 0, ',', ' ') . ' ' . $word;
    }
}
