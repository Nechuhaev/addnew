<?php

namespace App\Models\Stats;

use App\ArticleView;

/**
 * Статистика по статтях блогу (перегляди, у майбутньому — конверсії).
 * Дзеркалить патерн App\Models\Stats\Ad.
 *
 * Class Article
 * @package App\Models\Stats
 */
class Article
{
    public static function getTotalViewsGroupByMonth($year, array $months)
    {
        $counts = ArticleView::where('event_type', 'view')
            ->whereYear('created_at', $year)
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as total')
            ->groupBy('month')
            ->pluck('total', 'month');

        $x = [];
        foreach ($months as $month) {
            $x[] = (int) ($counts[$month] ?? 0);
        }

        return ['x' => $x];
    }

    public static function totalViewsGroupByYears(array $years)
    {
        $counts = ArticleView::where('event_type', 'view')
            ->selectRaw('YEAR(created_at) as year, COUNT(*) as total')
            ->groupBy('year')
            ->pluck('total', 'year');

        $x = [];
        foreach ($years as $year) {
            $x[] = (int) ($counts[$year] ?? 0);
        }

        return ['x' => $x];
    }

    /**
     * Топ статей за переглядами, опційно за конкретний рік/місяць.
     */
    public static function topArticles($limit = 10, $year = null, $month = null)
    {
        $query = ArticleView::where('event_type', 'view')
            ->join('articles', 'articles.id', '=', 'article_views.article_id');

        if ($year) {
            $query->whereYear('article_views.created_at', $year);
        }
        if ($month) {
            $query->whereMonth('article_views.created_at', $month);
        }

        return $query
            ->selectRaw('articles.id, articles.name, articles.slug, COUNT(*) as total_views')
            ->groupBy('articles.id', 'articles.name', 'articles.slug')
            ->orderByDesc('total_views')
            ->limit($limit)
            ->get();
    }
}
