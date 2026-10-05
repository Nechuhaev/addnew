<?php

namespace App\Services\Seo;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\ShopReview;
use App\Translation;
use App\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * SEO-текст під списком товарів на сторінці магазину (/author/{id}),
 * зібраний із РЕАЛЬНИХ даних: кількість товарів, основні категорії, міста,
 * діапазон цін, відгуки, підтвердження email, рік реєстрації.
 *
 * Замінює шаблон SeoField 'ad-user', який був універсальним текстом для
 * тегів із підстановкою назви (звідси безглузді фрази на кшталт
 * «кількість Top Pack на сайті понад 12 000»). Унікальність тексту
 * забезпечують унікальні дані, а не перефразування, і жодних тверджень,
 * яких платформа не може підтвердити (доставка, гарантії).
 *
 * Без LLM, без зовнішніх викликів. Результат кешується на 6 годин.
 */
class ShopSeoText
{
    public function build(User $shop): string
    {
        $locale = app()->getLocale() === 'ru' ? 'ru' : 'uk';

        return Cache::remember("shop_seo_text:{$shop->id}:{$locale}", now()->addHours(6), function () use ($shop, $locale) {
            return $this->generate($shop, $locale);
        });
    }

    /**
     * Некешований варіант (для перегляду з консолі й тестів).
     */
    public function generate(User $shop, string $locale): string
    {
        $ru = $locale === 'ru';
        $name = trim((string) $shop->username);
        $total = Ad::where('user_id', $shop->id)->count();

        if ($name === '' || $total === 0) {
            return '';
        }

        // --- Категорії (топ-3 за кількістю товарів) ---
        $catRows = DB::table('ads')
            ->where('user_id', $shop->id)
            ->whereNotNull('category_id')
            ->select('category_id', DB::raw('COUNT(*) AS c'))
            ->groupBy('category_id')
            ->orderByDesc('c')
            ->limit(100)
            ->get();
        $catCount = $catRows->count();
        $catNames = $this->namesById(AdCategory::class, $catRows->take(3)->pluck('category_id')->all());

        // --- Міста (топ-2) ---
        $cityRows = DB::table('ads')
            ->where('user_id', $shop->id)
            ->whereNotNull('city_id')
            ->select('city_id', DB::raw('COUNT(*) AS c'))
            ->groupBy('city_id')
            ->orderByDesc('c')
            ->limit(2)
            ->get();
        $cityNames = $this->namesById(AdCity::class, $cityRows->pluck('city_id')->all());

        // --- Діапазон цін (стійкий до сміттєвих значень типу 2147483647) ---
        $range = PriceRange::summarize(function ($q) use ($shop) {
            $q->where('ads.user_id', $shop->id);
        });

        // --- Довіра ---
        $reviewCount = ShopReview::where('shop_user_id', $shop->id)->count();
        $avg = $reviewCount ? round((float) ShopReview::where('shop_user_id', $shop->id)->avg('rating'), 1) : null;
        $verified = false;
        try {
            $last = DB::table('shop_email_checks')->where('user_id', $shop->id)->orderByDesc('checked_at')->first();
            $verified = $last ? (bool) $last->matched : false;
        } catch (\Throwable $e) {
            $verified = false;
        }
        $host = $this->host($shop->site_url);
        $since = null;
        try {
            $since = $shop->created_at ? (int) $shop->created_at->year : null;
        } catch (\Throwable $e) {
            $since = null;
        }

        // --- Збір тексту ---
        $nameE = e($name);

        $p1 = $ru
            ? $nameE . ' — магазин на доске бесплатных объявлений Addnew.biz. В каталоге ' . $total . ' ' . $this->plural($total, ['товар', 'товара', 'товаров'])
            : $nameE . ' — магазин на дошці безкоштовних оголошень Addnew.biz. У каталозі ' . $total . ' ' . $this->plural($total, ['товар', 'товари', 'товарів']);
        if ($catCount >= 2) {
            $p1 .= $ru
                ? ', ассортимент охватывает ' . $catCount . ' ' . $this->plural($catCount, ['категорию', 'категории', 'категорий'])
                : ', асортимент охоплює ' . $catCount . ' ' . $this->plural($catCount, ['категорію', 'категорії', 'категорій']);
        }
        $p1 .= '.';
        if ($catNames) {
            $p1 .= ' ' . ($ru ? 'Основные категории: ' : 'Основні категорії: ') . e(implode(', ', $catNames)) . '.';
        }

        $p2 = '';
        if ($cityNames) {
            $p2 .= ($ru ? 'География объявлений: ' : 'Географія оголошень: ') . e(implode(', ', $cityNames)) . '. ';
        }
        if ($range) {
            $lead = $range['approx']
                ? ($ru ? 'Большинство цен — от ' : 'Більшість цін — від ')
                : ($ru ? 'Цены — от ' : 'Ціни — від ');
            $p2 .= $lead . e($range['lo']) . ' до ' . e($range['hi']) . '.';
        }

        $p3 = '';
        if ($verified && $host) {
            $p3 .= $ru
                ? 'Email магазина подтверждён сверкой с его сайтом ' . e($host) . '. '
                : 'Email магазину підтверджено звіркою з його сайтом ' . e($host) . '. ';
        }
        if ($reviewCount > 0 && $avg !== null) {
            $avgText = rtrim(rtrim(number_format($avg, 1, ',', ''), '0'), ',');
            $p3 .= $ru
                ? 'Покупатели оставили ' . $reviewCount . ' ' . $this->plural($reviewCount, ['отзыв', 'отзыва', 'отзывов']) . ', средняя оценка — ' . $avgText . ' из 5. '
                : 'Покупці залишили ' . $reviewCount . ' ' . $this->plural($reviewCount, ['відгук', 'відгуки', 'відгуків']) . ', середня оцінка — ' . $avgText . ' з 5. ';
        }
        if ($since && $since > 2000) {
            $p3 .= $ru
                ? 'Магазин представлен на Addnew.biz с ' . $since . ' года.'
                : 'Магазин представлений на Addnew.biz з ' . $since . ' року.';
        }

        $p4 = $ru
            ? 'Чтобы уточнить наличие или условия, напишите магазину через чат на Addnew.biz' . ($host ? ' или перейдите на его сайт ' . e($host) : '') . '.'
            : 'Щоб уточнити наявність чи умови, напишіть магазину через чат на Addnew.biz' . ($host ? ' або перейдіть на його сайт ' . e($host) : '') . '.';

        $html = '<h2>' . ($ru ? 'О магазине ' : 'Про магазин ') . $nameE . '</h2>';
        foreach ([$p1, $p2, $p3, $p4] as $p) {
            $p = trim($p);
            if ($p !== '') {
                $html .= '<p>' . $p . '</p>';
            }
        }

        return $html;
    }

    /**
     * Назви (з урахуванням локалі через аксесори моделей) у порядку списку id.
     */
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

    protected function host($url): ?string
    {
        $url = trim((string) $url);
        if ($url === '') {
            return null;
        }
        if (!preg_match('~^https?://~i', $url)) {
            $url = 'http://' . $url;
        }
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            return null;
        }

        return preg_replace('/^www\./i', '', mb_strtolower($host));
    }

    protected function plural(int $n, array $forms): string
    {
        return $forms[Translation::slavicPluralIndex(abs($n))];
    }
}
