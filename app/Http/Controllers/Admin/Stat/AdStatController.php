<?php

namespace App\Http\Controllers\Admin\Stat;

use App\Ad;
use App\Models\Stats\Ad as AdStat;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * TODO: Прежде чем добавлять новые чартлисты - рефакторим этот код,
 * продумываем логику вывода чартлистов и разгребаем бардак в моделях stats
 * Class AdStatController
 * @package App\Http\Controllers\Admin\Stat
 */
class AdStatController extends Controller
{
    public function index(Request $request) {
        $data = [];



        $data['filters'] = []; // Список фильтров для чартлиста

        $data['filters']['filter_years'] = [
            'heading' => 'Год',
            'values' => []
        ];

        $allowed_years_to_filter = [];
        for ($init_year = 2016; $init_year <= date("Y"); $init_year++) {
            $allowed_years_to_filter[] = $init_year;

            $is_active = ($request->has('filter_year') && $request->get('filter_year' ) == $init_year);

            $value = !$is_active
                ? route(Route::currentRouteName(), "filter_year={$init_year}")
                : route(Route::currentRouteName());
            $data['filters']['filter_years']['values'][] = [
                'name' => $init_year,
                'value' => $value,
                'is_active' => ($request->has('filter_year') && $request->get('filter_year' ) == $init_year)
            ];
        }

        if ($request->has('filter_year')) {

//            $data['filters']['filter_months'] = [
//                'heading' => 'Месяц',
//                'values' => []
//            ];

            $allowed_months_to_filter = [];

            // Если выбран текущий год - ограничиваем по месяцам
            $max_month = ($request->get('filter_year' ) == date("Y")) ? date('m') : 12;

            for ($init_month = 1; $init_month <= $max_month; $init_month++) {
                $allowed_months_to_filter[] = $init_month;
//                $data['filters']['filter_months']['values'][] = [
//                    'name' => $init_month,
//                    'value' => $init_month,
//                    'is_active' => false
//                ];
            }

            $data['chartlist_labels'] = $allowed_months_to_filter;
            $data['chartlist_lines'] = [];
            $data['chartlist_lines']['ads'] = AdStat::getTotalAdsGroupByMonth($request->get('filter_year'), $allowed_months_to_filter);
            $data['chartlist_lines']['products'] = AdStat::getTotalProductsGroupByMonth($request->get('filter_year'), $allowed_months_to_filter);

        } else {
            $data['chartlist_labels'] = $allowed_years_to_filter;
            $data['chartlist_lines'] = [];
            $data['chartlist_lines']['ads'] = AdStat::totalAdsGroupByYears($allowed_years_to_filter);
            $data['chartlist_lines']['products'] = AdStat::totalProductsGroupByYears($allowed_years_to_filter);
        }

        return view('admin.stat.ads')->with($data);
    }

}
