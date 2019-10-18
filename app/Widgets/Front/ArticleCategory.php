<?php

namespace App\Widgets\Front;

use Arrilot\Widgets\AbstractWidget;

class ArticleCategory extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'active' => null,
        'categories' => []
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

        $categories = \App\ArticleCategory::all();

        foreach ($categories as $category) {
            $this->config['categories'][] = [
                'name' => $category->name,
                'href' => $category->href,
                'id' => $category->id,
                'posts_count' => $category->articles()->count()
            ];
        }

        return view('widgets.front.article_category', [
            'config' => $this->config,
        ]);
    }
}
