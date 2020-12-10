<?php

namespace App\Models\Stats;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class Ad extends Model
{

    public static function totalAdsGroupByYears(array $years) {
        $years_union_query = collect($years)->map(function ($year) {
            return sprintf("SELECT %s AS year", $year);
        })->implode(' UNION ');


        $value = DB::select(DB::raw("
            SELECT y.year AS Y_alias, count as X_alias FROM (
                {$years_union_query}
            ) y
            LEFT JOIN (
                SELECT COUNT(*) as count, YEAR(created_at) as year FROM ads
                WHERE is_product = 0
                GROUP BY YEAR(created_at)
            ) stats ON (stats.year = y.year)
        "));

        $result = [];
        foreach ($value as $object) {
            $result["x"][] = $object->X_alias ?? 0;
            $result["y"][] = $object->Y_alias;
        }

        return $result;

//        SELECT y.year, count FROM (
//            SELECT 2018 as year
//            UNION SELECT 2019 as year
//            UNION SELECT 2020 as year
//        ) y
//        LEFT JOIN (
//                    SELECT COUNT(*) as count, YEAR(created_at) as year FROM ads
//            WHERE is_product = 0
//            GROUP BY YEAR(created_at)
//        ) stats ON (stats.year = y.year);

//        return DB::table('ads')
//            ->selectRaw("y.year, count")
//            ->fromRaw("({$years_union_query}) y")
//            ->leftJoin(DB::raw());
    }

    public static function totalProductsGroupByYears(array $years) {
        $years_union_query = collect($years)->map(function ($year) {
            return sprintf("SELECT %s AS year", $year);
        })->implode(' UNION ');


        $value = DB::select(DB::raw("
            SELECT y.year AS Y_alias, count as X_alias FROM (
                {$years_union_query}
            ) y
            LEFT JOIN (
                SELECT COUNT(*) as count, YEAR(created_at) as year FROM ads
                WHERE is_product = 1
                GROUP BY YEAR(created_at)
            ) stats ON (stats.year = y.year)
        "));

        $result = [];
        foreach ($value as $object) {
            $result["x"][] = $object->X_alias ?? 0;
            $result["y"][] = $object->Y_alias;
        }

        return $result;

//        SELECT y.year, count FROM (
//            SELECT 2018 as year
//            UNION SELECT 2019 as year
//            UNION SELECT 2020 as year
//        ) y
//        LEFT JOIN (
//                    SELECT COUNT(*) as count, YEAR(created_at) as year FROM ads
//            WHERE is_product = 1
//            GROUP BY YEAR(created_at)
//        ) stats ON (stats.year = y.year);

//        return DB::table('ads')
//            ->selectRaw("y.year, count")
//            ->fromRaw("({$years_union_query}) y")
//            ->leftJoin(DB::raw());
    }

    public static function getTotalAdsGroupByMonth(int $year, array $months) {
        $months_union_query = collect($months)->map(function ($month) {
            return sprintf("SELECT %s AS month", $month);
        })->implode(' UNION ');

        $value = DB::select(DB::raw("
                    SELECT mi.month AS Y_alias, count as X_alias FROM (
                    {$months_union_query}
                ) mi
                LEFT JOIN (SELECT COUNT(*) as count, MONTH(created_at) as month FROM ads 
                WHERE 
                    YEAR(created_at) = {$year}
                    AND  is_product = 0
                GROUP BY YEAR(created_at), MONTH(created_at)) stats ON (mi.month = stats.month)
        "));

        $result = [];
        foreach ($value as $object) {
            $result["x"][] = $object->X_alias ?? 0;
            $result["y"][] = $object->Y_alias;
        }

        return $result;


//        SELECT * FROM (
//            SELECT 1 as month_ident
//	UNION SELECT 2 as month_ident
//	UNION SELECT 3 as month_ident
//	UNION SELECT 4 as month_ident
//	UNION SELECT 5 as month_ident
//	UNION SELECT 6 as month_ident
//	UNION SELECT 7 as month_ident
//	UNION SELECT 8 as month_ident
//	UNION SELECT 9 as month_ident
//	UNION SELECT 10 as month_ident
//	UNION SELECT 11 as month_ident
//	UNION SELECT 12 as month_ident
//) mi
//LEFT JOIN (SELECT COUNT(*) as count, MONTH(created_at) as ident FROM ads
//	WHERE
//		YEAR(created_at) = 2020
//        AND  is_product = 0
//	GROUP BY YEAR(created_at), MONTH(created_at)) stats ON (mi.month_ident = stats.ident);
//

    }

    public static function getTotalProductsGroupByMonth(int $year, array $months) {
        $months_union_query = collect($months)->map(function ($month) {
            return sprintf("SELECT %s AS month", $month);
        })->implode(' UNION ');

        $value = DB::select(DB::raw("
                    SELECT mi.month AS Y_alias, count as X_alias FROM (
                    {$months_union_query}
                ) mi
                LEFT JOIN (SELECT COUNT(*) as count, MONTH(created_at) as month FROM ads 
                WHERE 
                    YEAR(created_at) = {$year}
                    AND  is_product = 1
                GROUP BY YEAR(created_at), MONTH(created_at)) stats ON (mi.month = stats.month)
        "));

        $result = [];
        foreach ($value as $object) {
            $result["x"][] = $object->X_alias ?? 0;
            $result["y"][] = $object->Y_alias;
        }

        return $result;


//        SELECT * FROM (
//            SELECT 1 as month_ident
//	UNION SELECT 2 as month_ident
//	UNION SELECT 3 as month_ident
//	UNION SELECT 4 as month_ident
//	UNION SELECT 5 as month_ident
//	UNION SELECT 6 as month_ident
//	UNION SELECT 7 as month_ident
//	UNION SELECT 8 as month_ident
//	UNION SELECT 9 as month_ident
//	UNION SELECT 10 as month_ident
//	UNION SELECT 11 as month_ident
//	UNION SELECT 12 as month_ident
//) mi
//LEFT JOIN (SELECT COUNT(*) as count, MONTH(created_at) as ident FROM ads
//	WHERE
//		YEAR(created_at) = 2020
//        AND  is_product = 0
//	GROUP BY YEAR(created_at), MONTH(created_at)) stats ON (mi.month_ident = stats.ident);
//

    }


}
