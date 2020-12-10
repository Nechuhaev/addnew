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
        'active_category' => null,
        'parent' => null,
        'filter' => null
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


            $url = $category->url;

            $this->config['categories'][] = [
                'active' => ($this->config['active_category'] && $category->id == $this->config['active_category']->id),
                'name' => $category->name,
                'url' => $url,
            ];
        }

        return view('widgets.front.ad_subcategories', [
            'config' => $this->config,
        ]);
    }
}
