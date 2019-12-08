<?php

namespace App\Http\Controllers\Front;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\Http\Controllers\Controller;
use App\SeoField;

use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{

    public function index()
    {

        $categories = Cache::remember('home_categories', 43200, function () {
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
                                'url' => $child->url,
                            ];
                        }
                    }

                    $categories[$list_item_key][] = [
                        'name' => $parent_category->name,
                        'url' => $parent_category->url,
                        'image' => $parent_category->image,
                        'children' => $children
                    ];
                }
            }
            return $categories;
        });

        //dd($categories);




        // SEO поля
        $seo_field = SeoField::where('index', 'index')->first();
        if ($seo_field) {
            $meta = [
                'meta_title' => $seo_field->meta_title,
                'meta_description' => $seo_field->meta_description,
                'description' => $seo_field->description
            ];
        } else {
            $meta = [
                'meta_title' => false,
                'meta_description' => false,
                'description' => false
            ];
        }

        // Последние объявления
        $ads = Cache::remember('home_ads', 120, function () {
            $_ads = Ad::orderBy('created_at', 'desc')->take(5)->get();
            $ads = [];
            if ($_ads) {
                foreach ($_ads as $ad) {
                    $ads[] = [
                        'name' => $ad->name,
                        'url' => $ad->url,
                        'price' => $ad->formetted_price,
                        'image' => $ad->image
                    ];
                }

                return $ads;
            }
        });


        // Рандомные города
        $cities = Cache::remember('home_cities', 2280, function () {
            $_cities = AdCity::all()->random(10);

            if ($_cities) {
                $cities = [];
                foreach ($_cities as $city) {
                    $cities[] = [
                        'name' => $city->name,
                        'url' => $city->url
                    ];
                }

                return $cities;
            }
        });

        return view('front.index')->with([
            'categories' => $categories,
            'meta' => $meta,
            'ads' => $ads,
            'cities' => $cities
        ]);
    }
}
