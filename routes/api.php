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

// Объявления > Страны
Route::get('/ad/countries', 'API\Ad\Country@all');
Route::get('/ad/country/{id}', 'API\Ad\Country@show')->name('api.country');

Route::fallback(function () {
    return response()->json(['message' => 'Query route not found.'], 404);
});
