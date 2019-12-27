<?php

namespace App\Http\Controllers;

use App\Ad;
use App\ArticleCategory;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
        return view('admin.index')->with($data);
    }

    public function getChartData() {

    }

}
