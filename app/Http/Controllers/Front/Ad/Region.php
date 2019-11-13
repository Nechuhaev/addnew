<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdRegion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Region extends Controller
{
    public function page($country, $region)
    {
        $entity = AdRegion::where('slug', '=', $region)->first();

        $results = Ad::getAds()->where('ad_cities.id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'breadcrumbs' => 'region.page'
        ]);
    }
}
