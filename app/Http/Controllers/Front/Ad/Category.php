<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Category extends Controller
{
    public function page($category, $subcategory = null) {

        $categories_in = [];

        if ($subcategory) {
            $category = $subcategory;
        }
        $entity = AdCategory::where('slug', $category)->first();

        if (!$entity) abort(404);

        $categories_in[] = $entity->id;
        if ($entity->children()) {
            foreach ($entity->children()->pluck('id') as $category_in) {
                $categories_in[] = $category_in;
            }
        }
        //dd($categories_in);



        $results = Ad::getAds()->whereIn('ad_categories.id', $categories_in)
            ->paginate(15);
        $ads = Ad::getLoopArray($results);

        $seo_field = SeoField::where('index', 'ad-category')->first();

        if ($seo_field) {
            $parent = $entity->parent;
            $entity_values = [
                '---category_name---'  => $entity->name,
                '---parent_name---'    => ($parent) ? $entity->parent->name : '',
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

        $microdata_info = DB::table('ad_categories')
            ->selectRaw('min(ads.price) as min, max(ads.price) as max, count(ads.id) as ads_count')
            ->leftJoin('ads', 'ad_categories.id', '=', 'ads.category_id')
            ->whereIn('ad_categories.id', $categories_in)
            ->where('ads.price', '>', 0)
            ->first();

        return view('front.ad.category')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'category.page',
            'microdata' => $microdata_info,
            'meta' => $meta
        ]);
    }
}
