<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;

class Search extends Controller
{
    public function page(Request $request) {
        //dd($request->all());

        $results = Ad::getAds();

        if ($request->has('s') && $request->get('s') != '') {
            $results->where(function($query) use ($request) {
                $query->where('ads.name', 'LIKE', '%' . $request->get('s') . '%')
                    ->orWhere('ads.content', 'LIKE', '%' . $request->get('s') . '%');
            });
        }


        if ($request->has('city_id') && $request->get('city_id') != 0) {
            $results->where('ads.city_id', (int)$request->get('city_id'));
        }

        if ($request->has('sub_cat_id') || $request->has('cat_id')) {
            $category_id = 0;

            if ($request->get('sub_cat_id')) {
                $category_id = $request->get('sub_cat_id');
                $results->where('ads.category_id', (int)$request->get('sub_cat_id'));
            } else {
                if ($request->get('cat_id')) {

                    $cat_ids = AdCategory::select('id')
                        ->where('parent_id', (int)$request->get('cat_id'))
                        ->pluck('id')
                        ->toArray();

                    if (count($cat_ids)) {
                        $results->whereIn('ads.category_id', $cat_ids);
                    }

                }
            }



            if ($category_id) {
                $category = $category = AdCategory::find($category_id);
                $results->where('ads.category_id', $category->id);
            }
        }

        if ($results->count() >= 1 && mb_strlen(trim($request->get('s'))) >= 5) {
            $tag = AdTag::where('name', trim($request->get('s')))->first();
            if (!$tag) {
                $tag = AdTag::create(['name' => trim($request->get('s')), 'slug' => null]);
                $ads_ids = $results->pluck('ads.id')->toArray();
                if (count($ads_ids)) {
                    $tag->ads()->attach($ads_ids);
                }
            }
        }

        $results = $results->paginate(15);

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
            $meta = [
                'meta_title' => "Результаты поиска на доске объевлений ADDNEW.BIZ",
                'meta_description' => "Результаты поиска на доске объевлений ADDNEW.BIZ",
                'description' => false,
            ];
        }

        if (request()->get('page')) {
            $meta['description'] = false;
        }


        return view('front.ad.search')->with([
            'ads' => $ads,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'region.page',
            'total' => $results->total(),
            'meta' => $meta
        ]);
    }
}
