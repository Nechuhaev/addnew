<?php

namespace App\Models\Stats;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AdCategory extends Model
{
    /**
     * Кількість оголошень і товарів магазину по кожній категорії,
     * опційно за конкретний рік. Категорії без жодного оголошення
     * теж потрапляють у вибірку (LEFT JOIN від ad_categories).
     */
    public static function totalAdsByCategory($year = null)
    {
        $yearFilter = $year ? 'AND YEAR(created_at) = ' . (int) $year : '';

        return DB::select(DB::raw("
            SELECT c.id, c.name,
                COALESCE(a.total, 0) AS total_ads,
                COALESCE(p.total, 0) AS total_products
            FROM ad_categories c
            LEFT JOIN (
                SELECT category_id, COUNT(*) as total FROM ads
                WHERE is_product = 0 {$yearFilter}
                GROUP BY category_id
            ) a ON a.category_id = c.id
            LEFT JOIN (
                SELECT category_id, COUNT(*) as total FROM ads
                WHERE is_product = 1 {$yearFilter}
                GROUP BY category_id
            ) p ON p.category_id = c.id
            ORDER BY total_ads DESC
        "));
    }
}