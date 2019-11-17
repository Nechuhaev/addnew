<?php

namespace App\Widgets\Front;

use App\AdTag;
use Arrilot\Widgets\AbstractWidget;

class AdTags extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'heading' => 'Метки объявлений',
        'tags' => null,
        'output' => []
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $tags = [];
        if ($this->config['tags']) {
            foreach ($this->config['tags'] as $tag) {
                $tags[] = [
                    'name' => $tag->name,
                    'url' => route('tag', ['slug' => $tag->slug]),
                ];
            }
        } else {
            //$tags =
            //$this->config['tags'] = ['random'];
        }

        $this->config['output'] = $tags;
        //dd($this->config['output']);

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
