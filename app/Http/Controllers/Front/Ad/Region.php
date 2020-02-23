<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdRegion;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Region extends Controller
{
    public function page($country, $region)
    {
        $entity = AdRegion::where('slug', '=', $region)->first();

        $seo_field = SeoField::where('index', 'ad-region')->first();

        if ($seo_field) {
            $entity_values = [
                '---region_name---'  => $entity->name,
                '---country_name---'  => $entity->country->name,
            ];
            $meta = [
                'meta_title' => $entity->meta_title ?? strtr($seo_field->meta_title, $entity_values),
                'meta_description' => $entity->meta_description ?? strtr($seo_field->meta_description, $entity_values),
                'description' => $entity->content ?? strtr($seo_field->description, $entity_values)
            ];
        } else {
            $meta = [
                'meta_title' => $entity->meta_title,
                'meta_description' => $entity->meta_description,
                'description' => $entity->content,
            ];
        }

        if (request()->get('page')) {
            $meta['description'] = false;
        }

        $results = Ad::getAds()->where('ad_regions.id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        $microdata_info = DB::table('ad_countries')
            ->selectRaw('min(ads.price) as min, max(ads.price) as max, count(ads.id) as ads_count')
            ->leftJoin('ad_regions', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->leftJoin('ad_cities', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->leftJoin('ads', 'ads.city_id', '=', 'ad_cities.id')
            ->where('ad_regions.id', $entity->id)
            ->where('ads.price', '>', 0)
            ->first();


        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'children' => $entity->cities,
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'region.page',
            'microdata' => $microdata_info,
            'meta' => $meta
        ]);
    }
}
