<?php

namespace App\Http\Controllers\Front;

use App\Ad;
use App\AdCategory;
use App\AdCity;
use App\AdTag;
use App\Http\AdSense;
use App\Http\Controllers\Controller;
use App\Localization\Localization;
use App\SeoField;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    public function index(Localization $localization)
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

        $ads_cache_key = sprintf('home_ads_%s', $localization->getCountry()->id);
        $ads_groups = Cache::remember($ads_cache_key, 120, function () use ($localization) {
            $_ads_groups = $localization->ads()->orderBy('created_at', 'desc')->groupBy('user_id')->take(20)->get()->chunk(5);

            $ads_groups = [];
            foreach ($_ads_groups as $key => $group) {
                foreach ($group as $ad) {
                    $ads_groups[$key][] = [
                        'name' => $ad->name,
                        'url' => $ad->url,
                        'price' => $ad->formetted_price,
                        'image' => $ad->image
                    ];
                }
            }

            return $ads_groups;
        });


        // Рандомные города
        $cities_cache_key = sprintf('home_cities_%s', $localization->getCountry()->id);
        $cities = Cache::remember($cities_cache_key, 2280, function () use ($localization) {
            $_cities = $localization->cities()->get()->random(10);

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

        // Рандомные теги
        $tags_cache_key = sprintf('home_tags_%s', $localization->getCountry()->id);
        $tags = Cache::remember($tags_cache_key, 2280, function () use ($localization) {
            $_tags = AdTag::withCount([
                'ads' => function ($query) use ($localization) {
                    return $query->whereIn('city_id', $localization->citiesIds());
                }
            ])->having('ads_count', '>', 15)->get()->random(20);

            if ($_tags) {
                $tags = [];
                foreach ($_tags as $tag) {
                    $tags[] = [
                        'name' => $tag->name,
                        'url' => $tag->url
                    ];
                }

                return $tags;
            }
        });
//        Cache::forget('home_tags');

        $shop_users = User::withCount('ads')
            ->whereHas('ads', function ($query) use ($localization) {
                $query->where('is_product', 1)->whereIn('city_id', $localization->citiesIds());
            })
            ->orderBy('created_at', 'desc')
            ->take(12)
            ->get();


        return view('front.index')->with([
            'categories' => $categories,
            'meta' => $meta,
            'ads_groups' => $ads_groups,
            'cities' => $cities,
            'tags' => $tags,
            'shop_users' => $shop_users,
            'adsense' => new AdSense()
        ]);
    }

    public function subscribe(Request $request) {

        $validator = Validator::make($request->all(), ['email' => 'required|email'], ['email.required' => 'Введите свой email!', 'email.email' => 'Неверный email!']);

        if (!$validator->fails()) {

            $email = $request->get('email');

            $apiKey = '94492d7246f58de6bdc22950014e9744-us19';
            $listId = 'b435fcadb5';

            $memberId = md5(strtolower($email));
            $dataCenter = substr($apiKey,strpos($apiKey,'-')+1);
            $url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $listId . '/members/' . $memberId;
            //dd($url);

            $json = json_encode([
                'email_address' => $email,
                'status'        => 'subscribed',
            ]);

            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_USERPWD, 'user:' . $apiKey);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode == 200) {
                return ['success' => "Вы успешно подписались на новости!"];
            } else {
                return ['error' => "Ошибка подписки на новости."];
            }

        } else {
            return ['error' => $validator->errors()->get('email')[0]];
        }





    }
}
