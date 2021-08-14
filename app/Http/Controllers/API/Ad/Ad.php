<?php

namespace App\Http\Controllers\API\Ad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Ad extends Controller
{
    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:ad_categories,id',
            'author' => 'sometimes|required|min:3',
            'telephone' => 'required|min:6',
            'city_id' => 'required|exists:ad_cities,id',
            'email' => 'required|required|email',
            'name' => 'required|min:6',
            'content' => 'required|min:70',
            'images' => 'required',
            'price' => 'required|numeric',
            'currency_id' => 'required|integer|exists:ad_currencies,id',
        ]);

        if (!$validator->fails()) {
            $ad = 123;
            return [
                'success' => true,
                'message' => $ad
            ];
        } else {
            return $validator->errors()->toJson();
        }
    }
}
