<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use App\SearchQuery;
use App\Services\AdSearch;
use App\Services\ListingFilters;
use App\Services\StoreSearch;
use Illuminate\Http\Request;

class Search extends Controller
{
    public function page(Request $request) {

        $results = Ad::getAds();
        $search = new AdSearch((string) $request->get('s'));
        $search->apply($results);


        if ($request->has('city_id') && $request->get('city_id') != 0) {
            $results->where('ads.city_id', (int)$request->get('city_id'));
        }

        if ($request->has('sub_cat_id') || $request->has('cat_id')) {
            if ($request->get('sub_cat_id')) {
                $results->where('ads.category_id', (int)$request->get('sub_cat_id'));
            } elseif ($request->get('cat_id')) {
                $cat_ids = AdCategory::select('id')
                    ->where('parent_id', (int)$request->get('cat_id'))
                    ->pluck('id')
                    ->toArray();

                if (count($cat_ids)) {
                    $results->whereIn('ads.category_id', $cat_ids);
                }
            }
        }

        $listingFilters = ListingFilters::fromRequest($request, !$search->isEmpty());
        $listingFilters->apply($results);
        if (!$search->isEmpty() && $listingFilters->wantsRelevance()) {
            $search->orderByRelevance($results);
        }

        // appends: раніше друга сторінка пошуку губила сам пошуковий запит
        $results = $results
            ->paginate(15)
            ->appends(array_merge($request->only(['s', 'cat_id', 'sub_cat_id', 'city_id']), $listingFilters->query()));

        $term = trim((string) $request->get('s'));

        // Тег із запиту (SEO-сторінка /ad-tag/…): лише перші 100 найрелевантніших,
        // а не всі знайдені (широкий запит прив'язував тисячі оголошень).
        if ($results->total() >= 1 && mb_strlen($term) >= 5 && (int) $request->get('page', 1) === 1) {
            $tag = AdTag::where('name', $term)->first();
            if (!$tag) {
                $tag = AdTag::create(['name' => $term, 'slug' => null]);
                $topIds = $search->orderByRelevance($search->apply(Ad::getAds()))->limit(100)->pluck('ads.id')->toArray();
                if (count($topIds)) {
                    $tag->ads()->attach($topIds);
                }
            }
        }

        // Магазини, чия назва збігається із запитом
        $shops = collect();
        if (!$search->isEmpty() && (int) $request->get('page', 1) === 1) {
            $shops = StoreSearch::shops($term, 4);
        }

        if ($term !== '') {
            SearchQuery::record($term, $results->total(), 'site');
        }

        $ads = Ad::getLoopArray($results);


        // seo data
        $seo_field = SeoField::where('index', 'search')->first();

        if ($seo_field) {
            $entity_values = [
                '---searched_term---'  => $request->get('s'),
                '---results_count---'  => $results->total(),
            ];
            $meta = [
                'meta_title' => strtr($seo_field->meta_title, $entity_values),
                'meta_description' => strtr($seo_field->meta_description, $entity_values),
                'description' => strtr($seo_field->description, $entity_values)
            ];
        } else {
            $title = app()->getLocale() === 'ru'
                ? 'Результаты поиска на доске объявлений Addnew.biz'
                : 'Результати пошуку на дошці оголошень Addnew.biz';
            $meta = [
                'meta_title' => $title,
                'meta_description' => $title,
                'description' => false,
            ];
        }

        if (request()->get('page')) {
            $meta['description'] = false;
        }


        return view('front.ad.search')->with([
            'ads' => $ads,
            'term' => $term,
            'shops' => $shops,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'region.page',
            'total' => $results->total(),
            'listingFilters' => $listingFilters,
            'meta' => $meta
        ]);
    }
}
