<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCity;
use App\AdTag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class City extends Controller
{
    public function page($country, $region, $city)
    {
        $entity = AdCity::where('slug', '=', $city)->first();

        $results = Ad::getAds()->where('ad_cities.id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'city.page'
        ]);
    }
}
