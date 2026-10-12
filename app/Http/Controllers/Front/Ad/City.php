<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCity;
use App\AdTag;
use App\Http\AdSense;
use App\Http\Controllers\Controller;
use App\Localization\Localization;
use App\SeoField;
use App\Services\GeoPageBlocks;
use Illuminate\Support\Facades\Cache;

class City extends Controller
{
    public function page(Localization $localization, $country, $region, $city)
    {
        $entity = AdCity::where('slug', '=', $city)->first();
        if (!$entity) abort(404);

        $cache_key = sprintf('city_categories_v2_%s_%s', app()->getLocale(), $entity->id);

        $categories = Cache::remember($cache_key, 43200, function () use ($entity) {
            return GeoPageBlocks::filteredCategories($entity->slug);
        });

        $counts = GeoPageBlocks::cityCounts($entity);

        $seo_field = SeoField::where('index', 'ad-city')->first();
        
        if ($seo_field) {
            $entity_values = [
                '---city_name---'  => $entity->name,
                '---region_name---'  => $entity->region->name,
                '---country_name---'  => $entity->region->country->name,
            ];
            $meta = \App\Services\LocalizedMeta::build($entity, $seo_field, $entity_values);
        } else {
            $meta = \App\Services\LocalizedMeta::build($entity, null);
        }
        
        if (request()->get('page')) {
            $meta['description'] = false;
        }

        $citiesQuery = AdCity::query()
            ->where('region_id', $entity->region_id)
            ->where('id', '>', $entity->id)
            ->limit(20)
            ->get();

        $cities = [];
        foreach ($citiesQuery as $city) {
            $cities[] = [
                'name' => $city->name,
                'url' => $city->url,
                'count' => $counts['cities'][$city->id] ?? 0,
            ];
        }

        
        $_tags = AdTag::query()->withCount('ads')
            ->whereHas('ads', function ($query) use ($entity) {
                return $query->where('city_id', $entity->id);
            })
            ->having('ads_count', '>', 15)->get();

        $displayTagsCount = ($_tags->count() > 15) ? 15 : $_tags->count();

        $tags = [];
        foreach ($_tags->random($displayTagsCount) as $tag) {
            if ($tag->name && $tag->url) {
                $tags[] = array(
                    'name' => $tag->name,
                    'url' => $tag->url,
                );
            }          
        }
        
        $shop_users = GeoPageBlocks::latestShops($localization);

        $ads_groups = GeoPageBlocks::latestAdsGroups($localization->ads()->where('city_id', $entity->id));
        
        return view('front.ad.filtered-categories')->with([
            'entity' => $entity,
            'breadcrumbs' => 'city.page',
            'meta' => $meta,
            'categories' => $categories,
            'categoryCounts' => $counts['categories'],
            'adsense' => new AdSense(),
            'cities' => $cities,
            'tags' => $tags,
            'shop_users' => $shop_users,
            'ads_groups' => $ads_groups,
        ]);
    }
}
