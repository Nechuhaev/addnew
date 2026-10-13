<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdTag;
use App\Http\Controllers\Controller;
use App\SeoField;
use App\Services\ListingFilters;
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
            $meta = \App\Services\LocalizedMeta::build($entity, $seo_field, $entity_values);
        } else {
            $meta = \App\Services\LocalizedMeta::build($entity, null);
        }

        if (request()->get('page')) {
            $meta['description'] = false;
        }

        $listingFilters = ListingFilters::fromRequest(request());
        $results = $listingFilters->apply(Ad::getAds()
                ->leftJoin('ad_tag', 'ad_tag.ad_id', '=', 'ads.id')
                ->where('ad_tag.tag_id', $entity->id))
            ->paginate(15)
            ->appends($listingFilters->query());

        $ads = Ad::getLoopArray($results);

        $microdata_info = DB::table('ad_tags')
            ->selectRaw('min(ads.price) as min, max(ads.price) as max, count(ads.id) as ads_count')
            ->leftJoin('ad_tag', 'ad_tag.tag_id', '=', 'ad_tags.id')
            ->leftJoin('ads', 'ads.id', '=', 'ad_tag.ad_id')
            ->where('ad_tags.id', $entity->id)
            ->where('ads.price', '>', 0)
            ->first();


        // SEO-текст: власний (AI) текст тегу, якщо він є; інакше короткий фактичний
        // вступ із даних (TagSeoText). Універсального шаблону більше немає.
        $total = $results->total();
        // На UK — лише content_uk: RU-текст на українській сторінці не показуємо,
        // замість нього — згенерований український вступ.
        $raw = $entity->getAttributes();
        $ownContent = trim((string) (app()->getLocale() === 'uk' ? ($raw['content_uk'] ?? '') : ($raw['content'] ?? '')));
        $hasAnyContent = $ownContent !== '' || trim((string) ($raw['content'] ?? '')) !== '';
        if (request()->get('page')) {
            $meta['description'] = false;
        } elseif ($ownContent !== '') {
            $meta['description'] = $ownContent;
        } elseif ($total > 0) {
            $meta['description'] = app(\App\Services\Seo\TagSeoText::class)->build($entity, $total);
        } else {
            $meta['description'] = false;
        }

        // Тонкі теги (менше 5 оголошень і без власного тексту) не індексуємо,
        // але посилання з них лишаються доступними (noindex, follow).
        $noindex = (!$hasAnyContent && $total < 5);

        // Тег із noindex-стоп-словом («девушки по вызову», «кодграббер»…) — noindex + без AdSense
        if (\App\Services\SeoRules::isSensitiveTag($entity)) {
            $noindex = true;
            \App\Http\AdSense::disable();
        }

        return view('front.ad.country')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->onEachSide(1)->links('front.widgets.paginate'),
            'children' => $entity->regions,
            'tags' => AdTag::getAdsTags($ads),
            'microdata' => $microdata_info,
            'breadcrumbs' => 'ad_tag',
            'noindex' => $noindex,
            'listingFilters' => $listingFilters,
            'total' => $results->total(),
            'meta' => $meta
        ]);
    }
}
