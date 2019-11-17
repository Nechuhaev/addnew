<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdTag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Tag extends Controller
{
    public function page($tag) {
        $entity = AdTag::where('slug', '=', $tag)->first();

        //dd($children);

        $results = Ad::getAds()
            ->leftJoin('ad_tag', 'ad_tag.ad_id', '=', 'ads.id')
            ->where('ad_tag.tag_id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'children' => $entity->regions,
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'ad_tag'
        ]);
    }
}
