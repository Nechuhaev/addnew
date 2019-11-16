<?php

namespace App\Http\Controllers\Front\Ad;

use App\Http\Controllers\Controller;

use Carbon\Carbon;
use Illuminate\Http\Request;

class Ad extends Controller
{
    public function page($ad) {
        $ad = \App\Ad::where('slug', '=', $ad)->first();


        // Обновляем счетчик просмотров объявлений
        // Просмотры сегодня
        if(!Carbon::now()->isSameAs('d.m.Y', $ad->updated_at)) {
            $ad->today_views = 1; // Обнулим если сегодня новый день
        } else {
            $ad->today_views++;
        }
        // Просмотры всего
        $ad->total_views++;

        $ad->save();

        return view('front.ad.ad')->with([
            'ad' => $ad
        ]);
    }
}
