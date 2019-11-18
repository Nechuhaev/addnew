<?php

namespace App\Http\Controllers\API\Ad;

use App\AdCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use \App\Http\Resources\Ad\Category as CategoryResourse;

class Category extends Controller
{
    public function all()
    {

    }

    public function autocomplete($name = null) {

        if ($name) {
            $categories = AdCategory::where('name', 'like', "%$name%")->orderBy('name', 'asc')
                ->take(10)
                ->get();
        } else {
            $categories = AdCategory::orderBy('parent_id', 'desc')
                ->orderBy('name', 'asc')
                ->take(10)
                ->get();
        }

        if ($categories) {
            return CategoryResourse::collection($categories);
        } else {
            return response()->json(['error' => 'Категорий не найдено']);
        }
    }
    public function getChildren($parent_id = null) {

        if ($parent_id) {
            $categories = AdCategory::where('parent_id', (int)$parent_id)
                ->orderBy('name', 'asc')
                ->get();

            if ($categories) {
                return CategoryResourse::collection($categories);
            }
        }


        return response()->json(['error' => 'Категорий не найдено']);
    }
}
