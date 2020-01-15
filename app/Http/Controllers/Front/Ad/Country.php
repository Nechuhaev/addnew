<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCountry;
use App\AdRegion;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;

use Debugbar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Country extends Controller
{
    public function getList()
    {

        $data['countries'] = Cache::remember('countries_info', 50000, function () {
            $countries = DB::table('ad_countries')
                ->selectRaw('ad_countries.id, ad_countries.name, ad_countries.slug, ad_countries.image, count(DISTINCT ads.id) as ads_count')
                ->leftJoin('ad_regions', 'ad_countries.id', '=', 'ad_regions.country_id')
                ->leftJoin('ad_cities', 'ad_regions.id', '=', 'ad_cities.region_id')
                ->leftJoin('ads', 'ads.city_id', '=', 'ad_cities.id')
                ->where('ad_countries.image', '<>', '')
                ->groupBy('ad_countries.id')
                ->having('ads_count', '>', '0')
                ->orderBy('ads_count', 'DESC')
                ->get();

            $countries_array = [];
            foreach ($countries as $country) {
                $results = DB::table('ad_cities')
                    ->select('ad_cities.slug', 'ad_regions.slug AS region_slug', 'ad_cities.name', DB::raw("COUNT(ads.id) as ads_count"))
                    //->select('ad_cities.slug', 'ad_cities.name', DB::raw("1 as ads_count"))
                    ->leftJoin('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
                    ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
                    ->leftJoin('ads', 'ads.city_id', '=', 'ad_cities.id')
                    ->where('ad_countries.id', $country->id)
                    ->orderBy('ads_count', 'desc')
                    ->groupBy("ad_cities.id")
                    ->having('ads_count', '>', '0')
                    ->limit('10')
                    ->get();

                $cities = [];
                foreach ($results as $result) {

                    $path = $country->slug . '/' . $result->slug;
                    $cities[] = [
                        'url' => route('city.page', [
                            'country' => $country->slug,
                            'region' => $result->region_slug,
                            'city' => $result->slug]),
                        'name' => $result->name,
                        'ads_count' => $result->ads_count,
                    ];
                }

                $countries_array[] = [
                    'url' => route('country.page', ['country' => $country->slug]),
                    'name' => $country->name,
                    'image' => $country->image,
                    'cities' => $cities
                ];
            }

            return $countries_array;
        });




//        Debugbar::info($data['countries']));

        // SEO поля
        $seo_field = SeoField::where('index', 'countries')->first();
        if ($seo_field) {
            $data['meta'] = [
                'meta_title' => $seo_field->meta_title,
                'meta_description' => $seo_field->meta_description,
                'description' => $seo_field->description
            ];
        } else {
            $data['meta'] = [
                'meta_title' => false,
                'meta_description' => false,
                'description' => false
            ];
        }




        return view('front.ad.countries')->with($data);
    }

    public function page($country)
    {

        $entity = AdCountry::where('slug', '=', $country)->first();

        if (!$entity) abort(404);

        $seo_field = SeoField::where('index', 'ad-country')->first();

        if ($seo_field) {
            $entity_values = [
                '---country_name---'  => $entity->name,
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

        $results = Ad::getAds()->where('ad_countries.id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'children' => $entity->regions,
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'country.page',
            'meta' => $meta
        ]);
    }


}
