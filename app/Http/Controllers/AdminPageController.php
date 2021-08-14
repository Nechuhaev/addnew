<?php

namespace App\Http\Controllers;

use App\Ad;
use App\AdCountry;
use App\ArticleCategory;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdminPageController extends Controller
{
    /**
     * Admin home page controller
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index() {
        $data['customers_count'] = User::count();
        $data['customers_count_today'] = User::whereDate('created_at', Carbon::today())->count();
        $data['customers_count_week'] = User::whereDate('created_at', '>', Carbon::today()->subDays(30))->count();

        $data['ads_count'] = Ad::count();
        $data['ads_count_today'] = Ad::whereDate('created_at', Carbon::today())->count();
        $data['ads_count_week'] = Ad::whereDate('created_at', '>', Carbon::today()->subDays(30))->count();

        $data['shops_count'] = User::withCount('ads')
            ->whereHas('ads', function ($query) {
                $query->where('is_product', 1);
            })->count();
        $data['shops_ads_count'] = Ad::products()->count();
        $data['shops_ads_views'] = Ad::products()->sum('total_views');



        $data['recent_ads'] = Ad::orderBy('created_at', 'desc')->take(10)->get();

        $data['top_countries'] = DB::table('ad_countries')
            ->selectRaw('ad_countries.name, COUNT(ads.id) as ads_count, MAX(ads.created_at) as last_created_at')
            ->leftJoin('ad_regions', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->leftJoin('ad_cities', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->leftJoin('ads', 'ads.city_id', '=', 'ad_cities.id')
            ->groupBy('ad_countries.name')->orderBy('ads_count', 'desc')->limit(10)->get();


        $data['top_categories'] = DB::table('ad_categories')
            ->selectRaw('ad_categories.name, COUNT(ads.id) as ads_count, MAX(ads.created_at) as last_created_at')
            ->leftJoin('ads', 'ads.category_id', '=', 'ad_categories.id')
            ->groupBy('ad_categories.name')->orderBy('ads_count', 'desc')->limit(10)->get();

        return view('admin.index')->with($data);
    }

    public function getChartData() {

    }

}
