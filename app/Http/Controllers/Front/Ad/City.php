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

        $cache_key = sprintf('city_categories_%s', $entity->id);
        
        $categories = Cache::remember($cache_key, 43200, function () use ($entity) {
            return GeoPageBlocks::filteredCategories($entity->slug);
        });

        

        $categories = [];

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

        $citiesQuery = AdCity::query()
            ->where('region_id', $entity->region_id)
            ->where('id', '>', $entity->id)
            ->limit(20)
            ->get();

        $cities = [];
        foreach ($citiesQuery as $city) {
            $cities[] = [
                'name' => $city->name,
                'url' => $city->url
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
            'adsense' => new AdSense(),
            'cities' => $cities,
            'tags' => $tags,
            'shop_users' => $shop_users,
            'ads_groups' => $ads_groups,
        ]);
    }
}
