<?php

namespace App\Widgets\Front;

use Arrilot\Widgets\AbstractWidget;

class AdTags extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'heading' => 'Категории',
        'tags' => []
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

//        $categories = AdCategory::where('parent_id', 0)->get();
//
//        foreach ($categories as $category) {
//            $this->config['categories'][] = [
//                'name' => $category->name,
//                'url' => $category->slug,
//                'image' => $category->image
//            ];
//        }

        return view('widgets.front.ad_tags', [
            'config' => $this->config,
        ]);
    }
}
