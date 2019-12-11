<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;

class Tag extends Controller
{
    public function page($tag) {
        $entity = AdTag::where('slug', '=', $tag)->first();

        $seo_field = SeoField::where('index', 'ad-tag')->first();

        if ($seo_field) {
            $entity_values = [
                '---tag_name---'  => $entity->name,
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

        $results = Ad::getAds()
            ->leftJoin('ad_tag', 'ad_tag.ad_id', '=', 'ads.id')
            ->where('ad_tag.tag_id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'children' => $entity->regions,
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'ad_tag',
            'meta' => $meta
        ]);
    }
}
