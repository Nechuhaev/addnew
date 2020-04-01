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
        'categories' => [],
        'filter' => null
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {

        $categories = AdCategory::where('parent_id', 0)->get();

        foreach ($categories as $category) {

            if ($this->config['filter']) {
                if ($category->parent_id) {
                    $url = route('filtered_subcategory.page', [
                        'filter' => $this->config['filter'],
                        'category' => $category->parent->slug,
                        'subcategory' => $category->slug
                    ]);
                } else {
                    $url = route('filtered_category.page', [
                        'filter' => $this->config['filter'],
                        'category' => $category->slug,
                    ]);
                }
            } else {
                $url = $category->url;
            }

            $this->config['categories'][] = [
                'name' => $category->name,
                'url' => $url,
                'image' => asset($category->image)
            ];
        }

        return view('widgets.front.ad_categories', [
            'config' => $this->config,
        ]);
    }
}
