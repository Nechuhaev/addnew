<?php

namespace App\Http\Controllers\API\Ad;

use App\AdTag;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use \App\Http\Resources\Ad\Tag as TagResourse;

class Tag extends Controller
{
    public function autocomplete($name = null) {
        if ($name) {
            $cities = AdTag::where('name', 'like', "%$name%")
                ->orderBy('name', 'asc')
                ->take(10)
                ->get();
        } else {
            $cities = AdTag::orderBy('name', 'asc')
                ->take(10)
                ->get();
        }

        if ($cities) {
            return TagResourse::collection($cities);
        } else {
            return response()->json(['error' => 'Тегов не найдено']);
        }
    }
}
