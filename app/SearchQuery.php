<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

/**
 * Журнал пошукових запитів (статистика в адмінці: «Пошукові запити»).
 */
class SearchQuery extends Model
{
    const UPDATED_AT = null;

    protected $guarded = [];

    const SOURCES = [
        'site' => 'Пошук по сайту',
        'stores' => 'Список магазинів',
        'shop' => 'Товари магазину',
    ];

    /** Скільки днів зберігаємо записи */
    const KEEP_DAYS = 365;

    const BOT_PATTERN = '/bot|crawl|spider|slurp|bingpreview|facebookexternalhit|yandex|baidu|semrush|ahrefs|mj12|petal|python|curl|wget|headless|lighthouse/i';

    public static function normalize(string $query): string
    {
        $q = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $query)));
        return mb_substr($q, 0, 191);
    }

    /**
     * Записати пошук. Без ботів, лише перша сторінка, і не частіше
     * ніж раз на 30 хв для того самого запиту в межах сесії
     * (зміна фільтрів/сортування не роздуває статистику).
     */
    public static function record(string $query, int $results, string $source = 'site', ?int $shopId = null): void
    {
        try {
            $request = request();
            $q = static::normalize($query);
            if ($q === '' || (int) $request->get('page', 1) > 1) {
                return;
            }
            if (preg_match(self::BOT_PATTERN, (string) $request->userAgent()) || !$request->userAgent()) {
                return;
            }

            $key = md5($source . '|' . $shopId . '|' . $q);
            $seen = (array) session('search_logged', []);
            $now = time();
            if (isset($seen[$key]) && $now - $seen[$key] < 1800) {
                return;
            }
            $seen = array_filter($seen, function ($t) use ($now) {
                return $now - $t < 1800;
            });
            $seen[$key] = $now;
            session(['search_logged' => array_slice($seen, -50, null, true)]);

            static::create([
                'query' => $q,
                'results' => $results,
                'source' => $source,
                'shop_id' => $shopId,
                'locale' => app()->getLocale() === 'ru' ? 'ru' : 'uk',
                'is_auth' => auth()->check(),
            ]);
        } catch (\Throwable $e) {
            // Статистика не повинна ламати пошук
            Log::warning('SearchQuery::record failed: ' . $e->getMessage());
        }
    }
}
