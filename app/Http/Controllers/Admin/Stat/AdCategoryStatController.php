<?php

namespace App\Http\Controllers\Admin\Stat;

use App\Models\Stats\AdCategory as AdCategoryStat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AdCategoryStatController extends Controller
{
    public function index(Request $request)
    {
        $data = [];
        $data['filters'] = [];
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

        $filter_year = $request->get('filter_year');
        $categoryStats = AdCategoryStat::totalAdsByCategory($filter_year);

        $data['category_stats'] = $categoryStats;

        // Для чарта беремо тільки топ-15 за кількістю оголошень,
        // щоб підписи по осі X не наліплювались одна на одну.
        $topForChart = collect($categoryStats)->take(15);
        $data['chartlist_labels'] = $topForChart->pluck('name')->values()->all();
        $data['chartlist_values'] = $topForChart->pluck('total_ads')->values()->all();

        return view('admin.stat.ad-categories')->with($data);
    }
}