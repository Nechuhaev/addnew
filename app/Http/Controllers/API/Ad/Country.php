<?php

namespace App\Http\Controllers\API\Ad;

use App\AdCountry;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\Ad\Country as CountryResource;

class Country extends Controller
{

    public function all()
    {
        $countries = AdCountry::all();
        return CountryResource::collection($countries);
    }

    /**
     * Получить данные страны
     * @param $id integer required Идентификатор страны
     * @return CountryResource|\Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $country = AdCountry::find($id);
        if ($country) {
            return new CountryResource($country);
        } else {
            return response()->json(['message' => 'Not Found.'], 404);
        }
    }
}
