<?php

namespace App\Http\Controllers\Admin\Stat;

use App\Models\Stats\Article as ArticleStat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class ArticleStatController extends Controller
{
    public function index(Request $request)
    {
        $data = [];
        $data['filters'] = []; // Список фильтров для чартлиста
        $data['filters']['filter_years'] = [
            'heading' => 'Год',
            'values' => []
        ];

        $allowed_years_to_filter = [];
        for ($init_year = 2016; $init_year <= date("Y"); $init_year++) {
            $allowed_years_to_filter[] = $init_year;
            $is_active = ($request->has('filter_year') && $request->get('filter_year') == $init_year);
            $value = !$is_active
                ? route(Route::currentRouteName(), "filter_year={$init_year}")
                : route(Route::currentRouteName());
            $data['filters']['filter_years']['values'][] = [
                'name' => $init_year,
                'value' => $value,
                'is_active' => $is_active,
            ];
        }

        if ($request->has('filter_year')) {
            $filter_year = $request->get('filter_year');
            $allowed_months_to_filter = [];
            // Если выбран текущий год - ограничиваем по месяцам
            $max_month = ($filter_year == date("Y")) ? date('m') : 12;
            for ($init_month = 1; $init_month <= $max_month; $init_month++) {
                $allowed_months_to_filter[] = $init_month;
            }

            $data['chartlist_labels'] = $allowed_months_to_filter;
            $data['chartlist_lines'] = [];
            $data['chartlist_lines']['views'] = ArticleStat::getTotalViewsGroupByMonth($filter_year, $allowed_months_to_filter);

            $data['top_articles'] = ArticleStat::topArticles(10, $filter_year);
        } else {
            $data['chartlist_labels'] = $allowed_years_to_filter;
            $data['chartlist_lines'] = [];
            $data['chartlist_lines']['views'] = ArticleStat::totalViewsGroupByYears($allowed_years_to_filter);

            $data['top_articles'] = ArticleStat::topArticles(10);
        }

        return view('admin.stat.articles')->with($data);
    }
}
