<?php

namespace App\Http\Controllers\Front;

use App\AdCategory;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{

    public function index()
    {
        $parents = AdCategory::where('parent_id', 0)
            ->orderBy('sort_order', 'ASC')->get();


        $categories = [];
        foreach ($parents as $parent) {
            $key = 0;

            // В зависимости от порядка сортировки помещаем в колонку
            if (in_array($parent['sort_order'], range(0, 99))) {
                $key = 0;
            }
            if (in_array($parent['sort_order'], range(100, 199))) {
                $key = 1;
            }
            if (in_array($parent['sort_order'], range(200, 299))) {
                $key = 2;
            }
            if (in_array($parent['sort_order'], range(300, 399))) {
                $key = 3;
            }
            $categories[$key][] = $parent;

        }
        //dd($categories);

        return view('front.index')->with([
            'categories' => $categories
        ]);
    }
}
