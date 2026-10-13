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

        $cache_key = sprintf('region_categories_v2_%s_%s', app()->getLocale(), $entity->id);
        $categories = Cache::remember($cache_key, 43200, function () use ($entity) {
            return GeoPageBlocks::filteredCategories($entity->slug);
        });


        $seo_field = SeoField::where('index', 'ad-region')->first();

        if ($seo_field) {
            $entity_values = [
                '---region_name---'  => $entity->name,
                '---country_name---'  => $entity->country->name,
            ];
            $meta = \App\Services\LocalizedMeta::build($entity, $seo_field, $entity_values);
        } else {
            $meta = \App\Services\LocalizedMeta::build($entity, null);
        }

        if (request()->get('page')) {
            $meta['description'] = false;
        }

        $citiesQuery = AdCity::query()
            ->where('region_id', $entity->id)
            ->get();

        $counts = GeoPageBlocks::regionCounts($entity->id);

        $cities = [];
        foreach ($citiesQuery as $city) {
            $cities[] = [
                'name' => $city->name,
                'url' => $city->url,
                'count' => $counts['cities'][$city->id] ?? 0,
            ];
        }
        // Міста з оголошеннями — першими
        usort($cities, function ($a, $b) {
            return $b['count'] <=> $a['count'] ?: strcmp($a['name'], $b['name']);
        });

        $shop_users = GeoPageBlocks::latestShops($localization);

        return view('front.ad.filtered-categories')->with([
            'faq' => request()->get('page') ? [] : \App\Services\GeoFaq::forPlace($entity, $counts),
            'noindex' => \App\Services\SeoRules::isNoindexCountry(optional($entity->country)->slug),
            'entity' => $entity,
            'breadcrumbs' => 'city.page',
            'meta' => $meta,
            'categories' => $categories,
            'categoryCounts' => $counts['categories'],
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
        if ($seo_field) {
            $meta = [
                'meta_title' => strtr((string) $seo_field->meta_title, $entity_values),
                'meta_description' => strtr((string) $seo_field->meta_description, $entity_values),
                'description' => strtr((string) $seo_field->description, $entity_values)
            ];
        } else {
            $text = app()->getLocale() === 'ru' ? 'Регионы: ' . $entity->name : 'Регіони: ' . $entity->name;
            $meta = ['meta_title' => $text, 'meta_description' => $text, 'description' => false];
        }

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
