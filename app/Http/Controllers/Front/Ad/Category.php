<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;

class Category extends Controller
{
    public function page($category, $subcategory = null) {


        if ($subcategory) {
            $category = $subcategory;
        }
        $entity = AdCategory::where('slug', $category)->first();

        //dd($category);

        $results = Ad::getAds()->where('ad_categories.id', $entity->id)
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

        return view('front.ad.category')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'category.page',
            'meta' => $meta
        ]);
    }
}
