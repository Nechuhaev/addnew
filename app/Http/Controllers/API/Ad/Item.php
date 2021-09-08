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

                
            $results = Ad::getAds();
            $results->where(function($query) use ($name) {
                $query->where('ads.name', 'LIKE', "%".$name."%")
                    ->orWhere('ads.content', 'LIKE', "%".$name."%")
                    ->take(10);
            });
            $results = $results->paginate(11);

            $items = [];
            foreach ($results as $ad) {
                $category = AdCategory::find($ad->category_id);
                if ($category->parent_id != '0') {
                    $category = AdCategory::find($category->parent_id);
                }
                $ad->image = '/'.$category->image;
                $items[] = $ad;
            }

        } else {
            $cities = null;
            $items = Ad::orderBy('region_id', 'desc')
                ->orderBy('name', 'asc')
                ->take(10)
                ->get();
        }
        
        if ($cities) {
            foreach ($items as $attr => $value) {
                $cities[] = $value;
            }
        } else {
            $cities = $items;
        }

        if ($items) {
            return response()->json($cities);
        } else {
            return response()->json(['error' => 'Объявлений не найдено']);
        }
    }
}
