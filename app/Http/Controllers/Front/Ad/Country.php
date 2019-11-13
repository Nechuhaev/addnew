<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCountry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Debugbar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Country extends Controller
{
    public function getList()
    {

        $data['countries'] = [];

        $countries = AdCountry::all();


        foreach ($countries as $country) {
            $results = DB::table('ad_cities')
                ->select('ad_cities.slug', 'ad_cities.name', DB::raw("COUNT(ads.id) as ads_count"))
                //->select('ad_cities.slug', 'ad_cities.name', DB::raw("1 as ads_count"))
                ->leftJoin('ad_regions', 'ad_cities.region_id', '=', 'ad_regions.id')
                ->leftJoin('ad_countries', 'ad_regions.country_id', '=', 'ad_countries.id')
                ->leftJoin('ads', 'ads.city_id', '=', 'ad_cities.id')
                ->where('ad_countries.id', $country->id)
                ->orderBy('ads_count', 'desc')
                ->groupBy("ad_cities.id")
                ->limit('10')
                ->get();

            $cities = [];
            foreach ($results as $result) {

                $path = $country->slug . '/' . $result->slug;
                $cities[] = [
                    'url' => route('country.page', ['path' => $path]),
                    'name' => $result->name,
                    'ads_count' => $result->ads_count,
                ];
            }

            $data['countries'][] = [
                'url' => route('country.page', ['path' => $country->slug]),
                'name' => $country->name,
                'image' => $country->image,
                'cities' => $cities
            ];
        }



//        Debugbar::info($data['countries']));

        return view('front.ad.countries')->with($data);
    }

    public function page($country)
    {

        $entity = AdCountry::where('slug', '=', $country)->first();

        $results = Ad::getAds()->where('ad_countries.id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'breadcrumbs' => 'countries.country'
        ]);
    }

    private function region($country, $region)
    {

    }

    public function city($country, $region, $city)
    {
        $entity = AdCountry::where('slug', '=', $country)->first();

        $results = Ad::getAds()->where('ad_countries.id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'breadcrumbs' => 'countries.country'
        ]);
    }

}
