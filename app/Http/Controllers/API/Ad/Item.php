<?php

namespace App\Http\Controllers\API\Ad;

use App\Ad;
use App\AdCity;
use App\AdCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

//use \App\Http\Resources\Ad\City as CityResourse;

class Item extends Controller
{
    public function autocomplete($name = null) {

        if ($name) {
            $cities = AdCity::where('name', 'like', "%$name%")->orderBy('name', 'asc')
                ->where('region_id', 642)
                ->take(10)
                ->get();

            $items = Ad::where('name', 'like', "%$name%")->orderBy('name', 'asc')
                ->take(10)
                ->get();
        } else {
            $cities = null;
            $items = Ad::orderBy('region_id', 'desc')
                ->orderBy('name', 'asc')
                ->take(10)
                ->get();
        }

        for ($i=0; $i < 10; $i++) { 
            $category = AdCategory::find($items[$i]->category_id);
            if ($category->parent_id != '0') {
                $category = AdCategory::find($category->parent_id);
            }
            $items[$i]->image = '/'.$category->image;
        }
        
        if ($cities) {
            foreach ($items as $attr => $value) {
                $cities[] = $value;
            }
        }

        if ($items) {
            //return CityResourse::collection($cities);
            return response()->json($cities);
        } else {
            return response()->json(['error' => 'Объявлений не найдено']);
        }
    }
}
