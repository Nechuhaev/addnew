<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Tag extends Controller
{
    public function page($tag) {
        $entity = AdTag::where('slug', '=', $tag)->first();

        if (!$entity) abort(404);

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

        if (request()->get('page')) {
            $meta['description'] = false;
        }

        $results = Ad::getAds()
            ->leftJoin('ad_tag', 'ad_tag.ad_id', '=', 'ads.id')
            ->where('ad_tag.tag_id', $entity->id)
            ->paginate(15);

        $ads = Ad::getLoopArray($results);

        $microdata_info = DB::table('ad_tags')
            ->selectRaw('min(ads.price) as min, max(ads.price) as max, count(ads.id) as ads_count')
            ->leftJoin('ad_tag', 'ad_tag.tag_id', '=', 'ad_tags.id')
            ->leftJoin('ads', 'ads.id', '=', 'ad_tag.ad_id')
            ->where('ad_tags.id', $entity->id)
            ->where('ads.price', '>', 0)
            ->first();


        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'children' => $entity->regions,
            'tags' => AdTag::getAdsTags($ads),
            'microdata' => $microdata_info,
            'breadcrumbs' => 'ad_tag',
            'meta' => $meta
        ]);
    }
}
