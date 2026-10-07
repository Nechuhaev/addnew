<?php

namespace App\Http\Controllers\Front;

use App\AdTag;
use App\Http\AdSense;
use App\Http\Controllers\Controller;
use App\Localization\Localization;
use App\SeoField;
use App\Services\GeoPageBlocks;

use App\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{

    public function index(Localization $localization)
    {
        $categories = Cache::remember('home_categories_' . app()->getLocale(), 43200, function () {
            $_category_list = GeoPageBlocks::parentCategoriesByColumn();

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
            return GeoPageBlocks::latestAdsGroups($localization->ads());
        });
        
        // Останні статті блогу — пріоритет неіндексованим у Google
        // (для прискорення індексації), доповнюємо найсвіжішими, якщо треба.
        $blog_articles = Cache::remember('home_blog_articles', 300, function () {
            $notIndexedIds = Article::leftJoin('article_index_status', 'article_index_status.article_id', '=', 'articles.id')
                ->where(function ($q) {
                    $q->whereNull('article_index_status.verdict')
                      ->orWhere('article_index_status.verdict', '!=', 'PASS');
                })
                ->orderBy('articles.created_at', 'desc')
                ->limit(6)
                ->pluck('articles.id');

            $articles = Article::whereIn('id', $notIndexedIds)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($articles->count() < 6) {
                $excludeIds = $articles->pluck('id')->toArray();
                $extra = Article::whereNotIn('id', $excludeIds)
                    ->orderBy('created_at', 'desc')
                    ->limit(6 - $articles->count())
                    ->get();
                $articles = $articles->concat($extra);
            }

            return $articles->map(function ($article) {
                return [
                    'name' => $article->name,
                    'url' => $article->url,
                    'image' => $article->image,
                ];
            });
        });

        // Рандомные города
        $cities_cache_key = sprintf('home_cities_%s', $localization->getCountry()->id);
        $cities = Cache::remember($cities_cache_key, 2280, function () use ($localization) {
            $_cities = $localization->cities()->get()->shuffle()->take(10);

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
            ])->having('ads_count', '>', 15)->get()->shuffle()->take(20);

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

        $shop_users = GeoPageBlocks::latestShops($localization);


        return view('front.index')->with([
            'categories' => $categories,
            'meta' => $meta,
            'ads_groups' => $ads_groups,
            'blog_articles' => $blog_articles,
            'cities' => $cities,
            'tags' => $tags,
            'shop_users' => $shop_users,
            'adsense' => new AdSense()
        ]);
    }

    public function subscribe(Request $request) {

        $validator = Validator::make($request->all(), ['email' => 'required|email'], [
            'email.required' => __('front.email_required'),
            'email.email' => __('front.email_invalid'),
        ]);

        if (!$validator->fails()) {

            $email = $request->get('email');

            $httpCode = \App\Services\MailchimpSubscriber::subscribe($email);

            if ($httpCode == 200) {
                return ['success' => __('front.subscribe_success')];
            } else {
                return ['error' => __('front.subscribe_error')];
            }

        } else {
            return ['error' => $validator->errors()->get('email')[0]];
        }
    }
}
