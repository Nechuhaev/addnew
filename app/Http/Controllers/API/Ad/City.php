<?php

namespace App\Http\Controllers\API\Ad;

use App\AdCity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use \App\Http\Resources\Ad\City as CityResourse;

class City extends Controller
{
    public function autocomplete($name = null) {

        if ($name) {
            $cities = AdCity::where('name', 'like', "%$name%")->orderBy('name', 'asc')
                //->where('region_id', 642)
                ->take(10)
                ->get();
        } else {
            $cities = AdCity::orderBy('region_id', 'desc')
                ->orderBy('name', 'asc')
                ->take(10)
                ->get();
        }

        if ($cities) {
            return CityResourse::collection($cities);
        } else {
            return response()->json(['error' => 'Городов не найдено']);
        }
    }
}
