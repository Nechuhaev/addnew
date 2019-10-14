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



Route::prefix('/')->group(function () {
    Route::get('/', function () {return view('front.list');});

    /*
     * Auth routes
     */
    Route::get('login', 'Front\User\Auth\LoginController@showLoginForm')->name('login');
    Route::post('login', 'Front\User\Auth\LoginController@login');
    Route::post('logout', 'Front\User\Auth\LoginController@logout')->name('logout');
    Route::post('password/email', 'Front\User\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    Route::post('password/reset', 'Front\User\Auth\ResetPasswordController@reset')->name('password.update');
    Route::get('password/reset', 'Front\User\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
    Route::get('password/reset/{token}', 'Front\User\Auth\ResetPasswordController@showResetForm')->name('password.reset');
    Route::post('register', 'Front\User\Auth\RegisterController@register');
    Route::get('register', 'Front\User\Auth\RegisterController@showRegistrationForm')->name('register');
});

Route::prefix('profile')->group(function () {
    Route::get('/', 'Front\User\UserController@edit')->name('profile.index');
    Route::get('/ads', 'Front\User\UserController@ads')->name('profile.ads');
});

//dd(strtoupper("4oigctjdg0"));

//
Route::get('/index', function () {
    return view('front.index');
})->name('index');
//
Route::get('/category', function () {
    return view('front.ad.category');
})->name('category');

Route::get('/search', function () {
    return view('front.ad.search');
})->name('search');

//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');



Route::prefix('admin')->group(function () {
    Route::get('/', 'AdminPageController@index')->name('admin.index');

    Route::get('/users', 'AdminPageController@users')->name('admin.users');
    Route::get('/user/{id}', 'AdminPageController@user')->name('admin.user');

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

    Route::get('/articleCategories', 'Admin\Article\CategoryController@index')->name('admin.article.category.index');
    Route::get('/articleCategory', 'Admin\Article\CategoryController@add')->name('admin.article.category.add');
    Route::get('/articleCategory/{id}', 'Admin\Article\CategoryController@show')->name('admin.article.category.show');
    Route::post('/articleCategory/add', 'Admin\Article\CategoryController@create')->name('admin.article.category.create');
    Route::post('/articleCategory/update/{id}', 'Admin\Article\CategoryController@update')->name('admin.article.category.update');

    Route::get('/adSenseBlocks', 'AdminPageController@adSenseBlocks')->name('admin.adSenseBlocks');
    Route::get('/adSenseBlock', 'AdminPageController@adSenseBlock')->name('admin.adSenseBlock');
});