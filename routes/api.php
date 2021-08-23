<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Объявления > Категории
Route::get('/ad/category/autocomplete/{name?}', 'API\Ad\Category@autocomplete');
Route::get('/ad/category/children/{parent_id?}', 'API\Ad\Category@getChildren');
Route::get('/ad/city/autocomplete/{name?}', 'API\Ad\City@autocomplete');
Route::get('/ad/item/autocomplete/{name?}', 'API\Ad\Item@autocomplete');
Route::get('/ad/tag/autocomplete/{name?}', 'API\Ad\Tag@autocomplete');
Route::get('/user/autocomplete/{search?}', 'API\User@autocomplete');
// Объявления > Страны
Route::get('/ad/countries', 'API\Ad\Country@all');
Route::get('/ad/country/{id}', 'API\Ad\Country@show')->name('api.country');

Route::get('/ad/region/{id}', 'API\Ad\Region@show');


// V2
Route::post('v2/ad-create', 'API\Ad\Ad@store');

Route::fallback(function () {
    return response()->json(['message' => 'Not Found.'], 404);
});




