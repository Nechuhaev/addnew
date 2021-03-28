<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\AdTag;
use App\Http\AdSense;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class City extends Controller
{
    // todo избавиться от всех дублирований такого кода для городов / стран / главной
    public function page($country, $region, $city)
    {
        $entity = AdCity::where('slug', '=', $city)->first();

        // Эту штуку потом хорошо бы переписать, тк она дублируется и кеш в перспективе может вызвать проблем
        $cache_key = sprintf('city_categories_%s', $entity->id);
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

                    $_children = $parent_category->children;

                    $children = [];
                    if ($_children->count()) {
                        foreach ($_children as $child) {
                            $children[] = [
                                'name' => $child->name,
                                'url' => $child->getFilteredUrl($entity->slug),
                            ];
                        }
                    }

                    $categories[$list_item_key][] = [
                        'name' => $parent_category->name,
                        'url' => $parent_category->getFilteredUrl($entity->slug),
                        'image' => $parent_category->image,
                        'children' => $children
                    ];
                }
            }
            return $categories;
        });

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

        return view('front.ad.filtered-categories')->with([
            'entity' => $entity,
            'breadcrumbs' => 'city.page',
            'meta' => $meta,
            'categories' => $categories,
            'adsense' => new AdSense()
        ]);
    }
}
