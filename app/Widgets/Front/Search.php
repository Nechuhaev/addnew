<?php

namespace App\Widgets\Front;

use App\AdCategory;
use Arrilot\Widgets\AbstractWidget;

class Search extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'search_term' => [],
        'categories' => [],
        'subcategories' => [],
        'city' => []
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

        $categories = AdCategory::where('parent_id', '0')->get();

        foreach ($categories as $category) {
            $this->config['categories'][] = [
                'id' => $category->id,
                'name' => $category->name,
            ];
        }


        return view('widgets.front.search', [
            'config' => $this->config,
        ]);
    }
}
