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
// Главная
Route::get('/', "Front\HomeController@index")->name('index');

// Авторизация
Route::get('/login', 'Front\User\Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Front\User\Auth\LoginController@login');
Route::post('logout', 'Front\User\Auth\LoginController@logout')->name('logout');
Route::post('password/email', 'Front\User\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::post('password/reset', 'Front\User\Auth\ResetPasswordController@reset')->name('password.update');
Route::get('/password/reset', 'Front\User\Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::get('/password/reset/{token}', 'Front\User\Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('register', 'Front\User\Auth\RegisterController@register');
Route::get('/register', 'Front\User\Auth\RegisterController@showRegistrationForm')->name('register');

// Категории объявлений
//Route::get('/{category}', 'Front\User\Auth\RegisterController@showRegistrationForm')->name('register');
//Route::get('/{category}/{sub_category}', 'Front\User\Auth\RegisterController@showRegistrationForm')->name('register');
//Route::get('/{s}', 'Front\User\Auth\RegisterController@showRegistrationForm')->name('register');



// Профиль
Route::get('/profile/', 'Front\User\UserController@edit')->name('profile.index');
Route::post('profile/update', 'Front\User\UserController@updateUser')->name('profile.update');
Route::get('/profile/ads', 'Front\User\UserController@ads')->name('profile.ads');
Route::get('/profile/password', 'Front\User\UserController@password')->name('profile.password');
Route::post('profile/password/update', 'Front\User\UserController@updatePassword')->name('profile.password.update');


// Блог
Route::get('/blog/', 'Front\Article\ArticleController@showArticles')->name('blog.index');
Route::get('/blog/{slug}', 'Front\Article\ArticleController@showArticle')->name('blog.article');
Route::get('/blog/category/{slug}', 'Front\Article\ArticleController@showCategory')->name('blog.category');


// Объявление
Route::get('/ads/{slug}', 'Front\Ad\Ad@page')->name('ad.page');


// Теги объявлений
Route::get('/ad-tags/{slug}', 'Front\Ad\Tag@page')->name('tag');

// Автор объявлений
Route::get('/author/{id}', 'Front\Article\ArticleController@showArticles')->name('author.index');

// Поиск объявлений
Route::get('/search', 'Front\Ad\Search@page')->name('ad.search');

// Страны
Route::get('/regions/', 'Front\Ad\Country@getList')->name('countries');
Route::get('/regions/{country}', 'Front\Ad\Country@page')->name('country.page');
Route::get('/regions/{country}/{region}', 'Front\Ad\Region@page')->name('region.page');
Route::get('/regions/{country}/{region}/{city}', 'Front\Ad\City@page')->name('city.page');


// Добавление объявлений
// step 1
Route::get('/create-listing/', 'Front\Ad\Ad@create_step_category')->name('ad.step.category');
Route::post('/create-listing/', 'Front\Ad\Ad@create_step_category')->name('ad.create.step.category');

// step 2
Route::get('/create-listing/details/', 'Front\Ad\Ad@create_step_details')->name('ad.step.details');
Route::post('/create-listing/details/', 'Front\Ad\Ad@create_step_details')->name('ad.create.step.details');

// step 3
Route::get('/create-listing/preview/', 'Front\Ad\Ad@create_step_preview')->name('ad.step.preview');
Route::post('/create-listing/preview/', 'Front\Ad\Ad@create_step_preview')->name('ad.create.step.preview');
// step 4
Route::get('/create-listing/success/', 'Front\Ad\Ad@create_step_success')->name('ad.step.success');
Route::post('/create-listing/success/', 'Front\Ad\Ad@create_step_success')->name('ad.create.step.success');




/**
 * *******************************************************************************
 * ******************************* ADMIN PART ************************************
 * *******************************************************************************
 */

//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');

Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/', 'AdminPageController@index')->name('admin.index');

    // Пользователи
    Route::get('/users', 'Admin\User\UserController@showUsersList')->name('admin.users');
    Route::get('/user/{id}', 'Admin\User\UserController@showUserInformation')->name('admin.user');
    Route::post('/user/update', 'Admin\User\UserController@update')->name('admin.user.update');


    // Объявления
    Route::get('/ads', 'Admin\Ad\Ad@showList')->name('admin.ads');
    Route::get('/ads/{search}', 'Admin\Ad\Ad@showList')->name('admin.ads.search');
    Route::get('/ad', 'Admin\Ad\Ad@show')->name('admin.ad');
    Route::get('/ad/{id}', 'Admin\Ad\Ad@edit')->name('admin.ad.edit');
    Route::post('/ad/create', 'Admin\Ad\Ad@create')->name('admin.ad.create');
    Route::post('/ad/update', 'Admin\Ad\Ad@update')->name('admin.ad.update');
    Route::post('/ad/delete', 'Admin\Ad\Ad@delete')->name('admin.ad.delete');
    Route::post('/ad/archive', 'Admin\Ad\Ad@archive')->name('admin.ad.archive');

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

    // Объявления > Валюты
    Route::get('/currencies', 'Admin\Ad\Currency@showForm')->name('admin.adCurrencies');
    Route::get('/currencies/search', 'Admin\Ad\Currency@search')->name('admin.adCurrencies.search');
    Route::get('/currencies/{id}', 'Admin\Ad\Currency@showForm')->name('admin.adCurrencies.edit');
    Route::post('/currencies/create', 'Admin\Ad\Currency@create')->name('admin.adCurrencies.create');
    Route::post('/currencies/update', 'Admin\Ad\Currency@update')->name('admin.adCurrencies.update');
    Route::get('/currencies/delete/{id}', 'Admin\Ad\Currency@delete')->name('admin.adCurrencies.delete');



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


// Категории объявлений
Route::get('/{category}', 'Front\Ad\Category@page')->name('category.page');
Route::get('/{category}/{subcategory}', 'Front\Ad\Category@page')->name('sub_category.page');