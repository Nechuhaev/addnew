<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('front.list');
});


Route::get('/index', function () {
    return view('front.index');
})->name('index');

Route::get('/category', function () {
    return view('front.ad.category');
})->name('category');

Route::get('/search', function () {
    return view('front.ad.search');
})->name('search');

Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');



Route::prefix('admin')->group(function () {
    Route::get('/', 'AdminPageController@index')->name('admin.index');

    Route::get('/users', 'AdminPageController@users')->name('admin.users');
    Route::get('/user', 'AdminPageController@user')->name('admin.user');

    Route::get('/ads', 'AdminPageController@ads')->name('admin.ads');
    Route::get('/ad', 'AdminPageController@ad')->name('admin.ad');
    Route::get('/adCategories', 'AdminPageController@adCategories')->name('admin.adCategories');
    Route::get('/adCategory', 'AdminPageController@adCategory')->name('admin.adCategory');
    Route::get('/countries', 'AdminPageController@countries')->name('admin.countries');
    Route::get('/country', 'AdminPageController@country')->name('admin.country');
    Route::get('/cities', 'AdminPageController@cities')->name('admin.cities');
    Route::get('/city', 'AdminPageController@city')->name('admin.city');
    Route::get('/adTags', 'AdminPageController@adTags')->name('admin.adTags');
    Route::get('/adTag', 'AdminPageController@adTag')->name('admin.adTag');

    Route::get('/pages', 'AdminPageController@pages')->name('admin.pages');
    Route::get('/page', 'AdminPageController@page')->name('admin.page');

    Route::get('/articles', 'AdminPageController@articles')->name('admin.articles');
    Route::get('/article', 'AdminPageController@article')->name('admin.article');
    Route::get('/articleCategories', 'AdminPageController@articleCategories')->name('admin.articleCategories');
    Route::get('/articleCategory', 'AdminPageController@articleCategory')->name('admin.articleCategory');


    Route::get('/adSenseBlocks', 'AdminPageController@adSenseBlocks')->name('admin.adSenseBlocks');
    Route::get('/adSenseBlock', 'AdminPageController@adSenseBlock')->name('admin.adSenseBlock');
});