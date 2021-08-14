<?php

namespace App\Widgets\Front;

use App\AdCategory;
use App\AdCity;
use Arrilot\Widgets\AbstractWidget;
use Illuminate\Support\Facades\Request;

class Search extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'search_term' => '',
        'categories' => [],
        'category_id' => [],
        'subcategories' => [],
        'subcategory_id' => 0,
        'city' => '',
        'city_id' => 0,
        'action' => ''
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $request = request();

        $this->config['search_term'] = $request->get('s');

        if ($request->get('cat_id')) {
            $this->config['category_id'] = $request->get('cat_id');

            $this->config['subcategories'] = AdCategory::where('parent_id', $request->get('cat_id'))
                ->get(['id', 'name'])->toArray();

            $this->config['subcategory_id'] = $request->get('sub_cat_id');
        }

        if ($request->get('city_id')) {
            $city = AdCity::find($request->get('city_id'));
            $this->config['city_id'] = $city->id;
            $this->config['city'] = $city->name;
        }

        $this->config['categories'] = AdCategory::where('parent_id', '0')
            ->get(['id', 'name'])->toArray();




        $this->config['action'] = route('ad.search');

        return view('widgets.front.search', [
            'config' => $this->config,
        ]);
    }
}
