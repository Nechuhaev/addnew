<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCity;
use App\AdRegion;
use App\AdCountry;
use App\Http\AdSense;
use App\Http\Controllers\Controller;
use App\Localization\Localization;
use App\SeoField;
use App\Services\GeoPageBlocks;
use Illuminate\Support\Facades\Cache;

class Region extends Controller
{
    public function page(Localization $localization, $country, $region)
    {
        $entity = AdRegion::where('slug', '=', $region)->first();
        if (!$entity) abort(404);

        $cache_key = sprintf('region_categories_%s', $entity->id);
        $categories = Cache::remember($cache_key, 43200, function () use ($entity) {
            return GeoPageBlocks::filteredCategories($entity->slug);
        });


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

        $citiesQuery = AdCity::query()
            ->where('region_id', $entity->id)
            ->get();

        $cities = [];
        foreach ($citiesQuery as $city) {
            $cities[] = [
                'name' => $city->name,
                'url' => $city->url
            ];
        }

        $shop_users = GeoPageBlocks::latestShops($localization);

        return view('front.ad.filtered-categories')->with([
            'entity' => $entity,
            'breadcrumbs' => 'city.page',
            'meta' => $meta,
            'categories' => $categories,
            'adsense' => new AdSense(),
            'cities' => $cities,
            'tags' => [],
            'shop_users' => $shop_users,
            'ads_groups' => [],
        ]);
    }

    public function all() {
        $entity = AdCountry::getCurrentCountry();
        $seo_field = SeoField::where('index', 'ad-country')->first();

        $entity_values = [
            '---country_name---'  => $entity->name
        ];
        $meta = [
            'meta_title' => strtr($seo_field->meta_title, $entity_values) ?? strtr($seo_field->meta_title, $entity_values),
            'meta_description' => strtr($seo_field->meta_description, $entity_values) ?? strtr($seo_field->meta_description, $entity_values),
            'description' => strtr($seo_field->description, $entity_values) ?? strtr($seo_field->description, $entity_values)
        ];
        
        $results = Ad::getAds()
            ->selectRaw('COUNT(ads.id) as ads_count')
            ->where('country_id', $entity->id)
            ->groupBy('region_id')
            ->orderBy('name')
            ->get()->toArray();

             
        $ids = array_column($results, 'region_id');
        $ads_counts = array_column($results, 'ads_count', 'region_id');
        $filters = AdRegion::find($ids);
        
        $data = [];
        foreach ($filters as $filter) {

            $data[] = [
                'name' => $filter->name,
                'ads_count' => $ads_counts[$filter->id],
                'url' => '/regions/ukraine/'.$filter->slug,
                'image' => $filter->image ?? null,
            ];
        }

        return view('front.page.regions')->with([
            'entity' => $entity,
            'breadcrumbs' => 'country.page',
            'filters' => $data,
            'meta' => $meta
        ]);
    }
}
