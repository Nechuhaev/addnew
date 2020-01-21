<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCity;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class City extends Controller
{
    public function page($country, $region, $city)
    {
        $entity = AdCity::where('slug', '=', $city)->first();

        if (!$entity) abort(404);

        $seo_field = SeoField::where('index', 'ad-city')->first();

        if ($seo_field) {
            $entity_values = [
                '---city_name---'  => $entity->name,
                '---region_name---'  => $entity->region->name,
                '---country_name---'  => $entity->region->country->name,
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

        $results = Ad::getAds()->where('ad_cities.id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        $microdata_info = DB::table('ad_countries')
            ->selectRaw('min(ads.price) as min, max(ads.price) as max, count(ads.id) as ads_count')
            ->leftJoin('ad_regions', 'ad_regions.country_id', '=', 'ad_countries.id')
            ->leftJoin('ad_cities', 'ad_cities.region_id', '=', 'ad_regions.id')
            ->leftJoin('ads', 'ads.city_id', '=', 'ad_cities.id')
            ->where('ad_cities.id', $entity->id)
            ->where('ads.price', '>', 0)
            ->first();

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'city.page',
            'microdata' => $microdata_info,
            'meta' => $meta
        ]);
    }
}
