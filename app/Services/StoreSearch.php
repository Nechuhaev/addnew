<?php

namespace App\Services;

use App\User;

/**
 * Пошук магазинів за назвою (ім'я магазину) і адресою сайту.
 */
class StoreSearch
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $query запит по User
     */
    public static function apply($query, string $term)
    {
        foreach (AdSearch::tokenize($term) as $t) {
            $like = '%' . addcslashes($t, '%_\\') . '%';
            $query->where(function ($q) use ($like) {
                $q->where('users.firstname', 'LIKE', $like)
                    ->orWhere('users.lastname', 'LIKE', $like)
                    ->orWhere('users.site_url', 'LIKE', $like);
            });
        }
        return $query;
    }

    /** Магазини з активними товарами, чия назва збігається із запитом (для блоку в пошуку) */
    public static function shops(string $term, int $limit = 4)
    {
        if (!AdSearch::tokenize($term)) {
            return collect();
        }
        $activeProducts = function ($q) {
            $q->where('is_product', 1)->where('status', 1);
        };
        return static::apply(User::query(), $term)
            ->whereHas('ads', $activeProducts)
            ->withCount(['ads' => $activeProducts])
            ->orderByDesc('ads_count')
            ->limit($limit)
            ->get();
    }
}
