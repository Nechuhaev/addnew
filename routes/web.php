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

/**
 * *******************************************************************************
 * ******************************* FRONT PART ************************************
 * *******************************************************************************
 */

Route::get('/', "Front\HomeController@index")->name('index');
Route::prefix('/')->group(function () {

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

    Route::get('blog', 'Front\Article\ArticleController@showArticles')->name('blog');
    Route::get('blog/article', 'Front\Article\ArticleController@showArticle')->name('blog.article');
});

/*
 * Profile routes
 */
Route::prefix('profile')->group(function () {
    Route::get('/', 'Front\User\UserController@edit')->name('profile.index');
    Route::post('/update', 'Front\User\UserController@updateUser')->name('profile.update');
    Route::get('/ads', 'Front\User\UserController@ads')->name('profile.ads');
    Route::get('/password', 'Front\User\UserController@password')->name('profile.password');
    Route::post('/password/update', 'Front\User\UserController@updatePassword')->name('profile.password.update');
});

Route::prefix('blog')->group(function () {
    Route::get('/', 'Front\Article\ArticleController@showArticles')->name('blog.index');
    Route::get('/{slug}', 'Front\Article\ArticleController@showArticle')->name('blog.article');
    Route::get('/category/{slug}', 'Front\Article\ArticleController@showCategory')->name('blog.category');
});

// Автор объявлений
Route::prefix('author')->group(function () {
    Route::get('/{id}', 'Front\Article\ArticleController@showArticles')->name('author.index');
});





/**
 * *******************************************************************************
 * ******************************* ADMIN PART ************************************
 * *******************************************************************************
 */

//
Route::get('/category', function () {
    return view('front.ad.category');
})->name('category');

Route::get('/search', function () {
    return view('front.ad.search');
})->name('search');

//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/', 'AdminPageController@index')->name('admin.index');

    // Пользователи
    Route::get('/users', 'Admin\User\UserController@showUsersList')->name('admin.users');
    Route::get('/user/{id}', 'Admin\User\UserController@showUserInformation')->name('admin.user');
    Route::post('/user/update', 'Admin\User\UserController@update')->name('admin.user.update');


    // Объявления
    Route::get('/ads', 'AdminPageController@ads')->name('admin.ads');
    Route::get('/ad', 'Admin\Ad\Ad@show')->name('admin.ad');

    // Объявления > Категории
    Route::get('/adCategories', 'Admin\Ad\Category@showForm')->name('admin.adCategories');
    Route::get('/adCategories/{id}', 'Admin\Ad\Category@showForm')->name('admin.adCategories.edit');
    Route::post('/adCategories/create', 'Admin\Ad\Category@create')->name('admin.adCategories.create');
    Route::post('/adCategories/update', 'Admin\Ad\Category@update')->name('admin.adCategories.update');
    Route::get('/adCategories/delete/{id}', 'Admin\Ad\Category@delete')->name('admin.adCategories.delete');

    // Объявления > Теги
    Route::get('/adTags', 'Admin\Ad\Tag@showForm')->name('admin.adTags');
    Route::get('/adTags/search', 'Admin\Ad\Tag@search')->name('admin.adTags.search');
    Route::get('/adTags/{id}', 'Admin\Ad\Tag@showForm')->name('admin.adTags.edit');
    Route::post('/adTags/create', 'Admin\Ad\Tag@create')->name('admin.adTags.create');
    Route::post('/adTags/update', 'Admin\Ad\Tag@update')->name('admin.adTags.update');
    Route::get('/adTags/delete/{id}', 'Admin\Ad\Tag@delete')->name('admin.adTags.delete');

    // Объявления > Страны
    Route::get('/countries', 'Admin\Ad\Country@showForm')->name('admin.adCountries');
    Route::get('/countries/search', 'Admin\Ad\Country@search')->name('admin.adCountries.search');
    Route::get('/countries/{id}', 'Admin\Ad\Country@showForm')->name('admin.adCountries.edit');
    Route::post('/countries/create', 'Admin\Ad\Country@create')->name('admin.adCountries.create');
    Route::post('/countries/update', 'Admin\Ad\Country@update')->name('admin.adCountries.update');
    Route::get('/countries/delete/{id}', 'Admin\Ad\Country@delete')->name('admin.adCountries.delete');

    // Объявления > Области / Регионы
    Route::get('/regions', 'Admin\Ad\Region@showForm')->name('admin.adRegions');
    Route::get('/regions/search', 'Admin\Ad\Region@search')->name('admin.adRegions.search');
    Route::get('/regions/{id}', 'Admin\Ad\Region@showForm')->name('admin.adRegions.edit');
    Route::post('/regions/create', 'Admin\Ad\Region@create')->name('admin.adRegions.create');
    Route::post('/regions/update', 'Admin\Ad\Region@update')->name('admin.adRegions.update');
    Route::get('/regions/delete/{id}', 'Admin\Ad\Region@delete')->name('admin.adRegions.delete');

    // Объявления > Города
    Route::get('/cities', 'Admin\Ad\City@showForm')->name('admin.adCities');
    Route::get('/cities/search', 'Admin\Ad\City@search')->name('admin.adCities.search');
    Route::get('/cities/{id}', 'Admin\Ad\City@showForm')->name('admin.adCities.edit');
    Route::post('/cities/create', 'Admin\Ad\City@create')->name('admin.adCities.create');
    Route::post('/cities/update', 'Admin\Ad\City@update')->name('admin.adCities.update');
    Route::get('/cities/delete/{id}', 'Admin\Ad\City@delete')->name('admin.adCities.delete');



    Route::get('/adCategory', 'AdminPageController@adCategory')->name('admin.adCategory');
    Route::get('/country', 'AdminPageController@country')->name('admin.country');
    Route::get('/city', 'AdminPageController@city')->name('admin.city');

    Route::get('/pages', 'AdminPageController@pages')->name('admin.pages');
    Route::get('/page', 'AdminPageController@page')->name('admin.page');

    Route::get('/articles', 'Admin\Article\ArticleController@showArticles')->name('admin.articles');
    Route::get('/article/add', 'Admin\Article\ArticleController@showArticleAddForm')->name('admin.article.add');
    Route::post('/article/add', 'Admin\Article\ArticleController@add')->name('admin.article.create');
    Route::get('/article/edit/{id}', 'Admin\Article\ArticleController@showArticleEditForm')->name('admin.article.edit');
    Route::post('/article/update', 'Admin\Article\ArticleController@update')->name('admin.article.update');
    Route::post('/article/delete', 'Admin\Article\ArticleController@delete')->name('admin.article.delete');

    Route::get('/articleCategories', 'Admin\Article\CategoryController@index')->name('admin.article.category.index');
    Route::get('/articleCategory', 'Admin\Article\CategoryController@add')->name('admin.article.category.add');
    Route::get('/articleCategory/{id}', 'Admin\Article\CategoryController@show')->name('admin.article.category.show');
    Route::post('/articleCategory/add', 'Admin\Article\CategoryController@create')->name('admin.article.category.create');
    Route::post('/articleCategory/update/{id}', 'Admin\Article\CategoryController@update')->name('admin.article.category.update');

    Route::get('/adSenseBlocks', 'AdminPageController@adSenseBlocks')->name('admin.adSenseBlocks');
    Route::get('/adSenseBlock', 'AdminPageController@adSenseBlock')->name('admin.adSenseBlock');
});