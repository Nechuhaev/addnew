<?php

namespace App\Services;

use App\ShopReview;
use Illuminate\Support\Facades\Cache;

/**
 * Середній рейтинг і кількість відгуків магазинів — для картки товару
 * й блоку «Інші продавці». Кешується на годину на кожен магазин;
 * кеш скидається при збереженні/видаленні відгуку (ShopReview::boot).
 */
class ShopRating
{
    const TTL = 3600;

    /**
     * @param int[] $userIds
     * @return array [user_id => ['avg' => 4.7, 'count' => 12]] — лише магазини з відгуками
     */
    public static function forUsers(array $userIds): array
    {
        $userIds = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        $out = [];
        $missing = [];
        foreach ($userIds as $id) {
            $cached = Cache::get(self::key($id));
            if ($cached === null) {
                $missing[] = $id;
            } elseif ($cached) {
                $out[$id] = $cached;
            }
        }

        if ($missing) {
            $rows = ShopReview::whereIn('shop_user_id', $missing)
                ->groupBy('shop_user_id')
                ->selectRaw('shop_user_id, AVG(rating) as avg_rating, COUNT(*) as cnt')
                ->get()
                ->keyBy('shop_user_id');
            foreach ($missing as $id) {
                $value = isset($rows[$id])
                    ? ['avg' => round((float) $rows[$id]->avg_rating, 1), 'count' => (int) $rows[$id]->cnt]
                    : [];   // порожній масив — «відгуків немає», теж кешуємо
                Cache::put(self::key($id), $value, self::TTL);
                if ($value) {
                    $out[$id] = $value;
                }
            }
        }

        return $out;
    }

    public static function forUser(int $userId): ?array
    {
        return self::forUsers([$userId])[$userId] ?? null;
    }

    public static function forget(int $userId): void
    {
        Cache::forget(self::key($userId));
    }

    protected static function key(int $userId): string
    {
        return 'shop_rating_' . $userId;
    }
}
