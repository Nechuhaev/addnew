<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\AdRegion;
use App\AdCountry;
use App\AdTag;
use App\Http\AdSense;
use App\Http\Controllers\Controller;
use App\Localization\Localization;
use App\SeoField;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Region extends Controller
{
    public function page(Localization $localization, $country, $region)
    {
        $entity = AdRegion::where('slug', '=', $region)->first();
        if (!$entity) abort(404);

        $cache_key = sprintf('region_categories_%s', $entity->id);
        $categories = Cache::remember($cache_key, 43200, function () use ($entity) {
            $parents = AdCategory::where('parent_id', 0)
                ->orderBy('sort_order', 'ASC')->get();

            $_category_list = [];
            foreach ($parents as $parent) {
                $key = 0;

                // В зависимости от порядка сортировки помещаем в колонку
                if (in_array($parent['sort_order'], range(0, 99))) {
                    $key = 0;
                }
                if (in_array($parent['sort_order'], range(100, 199))) {
                    $key = 1;
                }
                if (in_array($parent['sort_order'], range(200, 299))) {
                    $key = 2;
                }
                if (in_array($parent['sort_order'], range(300, 399))) {
                    $key = 3;
                }

                $_category_list[$key][] = $parent;
            }

            $categories = [];
            foreach ($_category_list as $list_item_key => $list_item_value) {
                foreach ($list_item_value as $parent_category) {
                    $categories[] = [
                        'name' => $parent_category->name,
                        'url' => $parent_category->getFilteredUrl($entity->slug),
                        'image' => $parent_category->image,
                    ];
                }
            }


            $categories = collect($categories);
            return $categories->toArray();
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

        $shop_users = User::withCount('ads')
            ->whereHas('ads', function ($query) use ($localization) {
                $query->where('is_product', 1)->whereIn('city_id', $localization->citiesIds());
            })
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();

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
