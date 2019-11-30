<?php

namespace App\Http\Controllers\Front\Ad;

use App\Ad;
use App\AdCategory;
use App\AdTag;
use App\Http\Controllers\Controller;
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



        return view('front.ad.category')->with([
            'entity' => $entity,
            'ads' => $ads,
            'links' => $results->links('front.widgets.paginate'),
            'tags' => AdTag::getAdsTags($ads),
            'breadcrumbs' => 'category.page'
        ]);
    }
}
