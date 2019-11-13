<?php

namespace App\Widgets\Front;

use App\AdCategory;
use Arrilot\Widgets\AbstractWidget;

class AdCategories extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'heading' => 'Категории',
        'categories' => []
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

        $categories = AdCategory::where('parent_id', 0)->get();

        foreach ($categories as $category) {
            $this->config['categories'][] = [
                'name' => $category->name,
                'url' => $category->slug,
                'image' => $category->image
            ];
        }

        return view('widgets.front.ad_categories', [
            'config' => $this->config,
        ]);
    }
}
