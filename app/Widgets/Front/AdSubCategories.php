<?php

namespace App\Widgets\Front;

use App\AdCategory;
use Arrilot\Widgets\AbstractWidget;

class AdSubCategories extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'heading' => 'Категории',
        'categories' => [],
        'parent' => null
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $this->config['parent'] = AdCategory::find($this->config['parent']->id);
        $categories = AdCategory::where('parent_id', $this->config['parent']->id)->get();

        foreach ($categories as $category) {
            $this->config['categories'][] = [
                'name' => $category->name,
                'url' => $category->url,
            ];
        }

        return view('widgets.front.ad_subcategories', [
            'config' => $this->config,
        ]);
    }
}
