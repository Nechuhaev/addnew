<?php

namespace App\Widgets;

use App\Ad;
use App\AdTag;
use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Facades\Cache;

class AdsNotFoundWidget extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        //
        $ads_groups = Cache::remember('ads_not_found_widget', 120, function () {
            $_ads_groups = Ad::orderBy('created_at', 'desc')->groupBy('user_id')->take(6)->get()->chunk(3);

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


        $tags = Cache::remember('tags_not_found_widget', 120, function () {
            return AdTag::withCount('ads')->having('ads_count', '>' , 10)->orderBy('id', 'desc')->take(15)->get();
        });


        return view('widgets.ads_not_found_widget', [
            'config' => $this->config,
            'ads' => $ads_groups,
            'tags' => $tags,
        ]);
    }
}
