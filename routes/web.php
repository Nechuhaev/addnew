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

Route::get('/system/cron-runner/{token}', 'CronRunnerController@run');

Route::middleware(['admin'])->prefix('admin')->group(function () {
    Route::get('/', 'AdminPageController@index')->name('admin.index');
    
    Route::post('/shops/skipped-domains', 'Admin\Shop\SkippedDomainController@store')->name('admin.shops.skippedDomains.store');
    Route::delete('/shops/skipped-domains/{id}', 'Admin\Shop\SkippedDomainController@destroy')->name('admin.shops.skippedDomains.destroy');
    
    Route::get('/shops', 'Admin\Shop\ShopController@index')->name('admin.shops');
    Route::get('/shops/skipped-domains', 'Admin\Shop\SkippedDomainController@index')->name('admin.shops.skippedDomains');
    Route::get('/shops/stats', 'Admin\Shop\ShopStatsController@index')->name('admin.shops.stats');
    Route::get('/shops/{id}', 'Admin\Shop\ShopController@edit')->name('admin.shops.edit');
    Route::post('/shops/{id}/update', 'Admin\Shop\ShopController@update')->name('admin.shops.update');
    Route::get('/shops/{id}/products', 'Admin\Shop\ShopController@products')->name('admin.shops.products');
    Route::get('/shops/{id}/impersonate', 'Admin\Shop\ShopController@impersonate')->name('admin.shops.impersonate');
    Route::delete('/shops/product/{id}', 'Admin\Shop\ShopController@deleteProduct')->name('admin.shops.product.delete');
    Route::delete('/shops/{id}', 'Admin\Shop\ShopController@destroy')->name('admin.shops.destroy');
    Route::delete('/shops/{id}/products/delete-inactive', 'Admin\Shop\ShopController@bulkDeleteInactive')->name('admin.shops.products.deleteInactive');
    Route::post('/shops/{id}/message', 'Admin\Shop\ShopController@sendMessage')->name('admin.shops.message');

    Route::get('/shop-message-templates', 'Admin\Shop\ShopMessageTemplateController@showForm')->name('admin.shopMessageTemplates');
    Route::get('/shop-message-templates/{id}', 'Admin\Shop\ShopMessageTemplateController@showForm')->name('admin.shopMessageTemplates.edit');
    Route::post('/shop-message-templates/create', 'Admin\Shop\ShopMessageTemplateController@create')->name('admin.shopMessageTemplates.create');
    Route::post('/shop-message-templates/update', 'Admin\Shop\ShopMessageTemplateController@update')->name('admin.shopMessageTemplates.update');
    Route::get('/shop-message-templates/delete/{id}', 'Admin\Shop\ShopMessageTemplateController@delete')->name('admin.shopMessageTemplates.delete');
    
Route::get('/shops/{id}/stats', 'Admin\Shop\ShopStatsController@show')->name('admin.shops.stats.show');
    
    
    
    
        Route::get('/translations', 'Admin\Translation\TranslationController@index')->name('admin.translations');
    Route::post('/translations', 'Admin\Translation\TranslationController@store')->name('admin.translations.store');
    Route::post('/translations/update', 'Admin\Translation\TranslationController@update')->name('admin.translations.update');
    Route::delete('/translations', 'Admin\Translation\TranslationController@destroy')->name('admin.translations.destroy');

        Route::get('/prompts', 'Admin\Prompt\PromptController@index')->name('admin.prompts');
    Route::get('/prompts/{id}/edit', 'Admin\Prompt\PromptController@edit')->name('admin.prompts.edit');
    Route::post('/prompts/{id}', 'Admin\Prompt\PromptController@update')->name('admin.prompts.update');
    Route::post('/prompts/{id}/reset', 'Admin\Prompt\PromptController@reset')->name('admin.prompts.reset');

    Route::prefix('stat')->group(function () {
        Route::get('/ads', 'Admin\Stat\AdStatController@index')->name('admin.stat.ads');
        Route::get('/ad-categories', 'Admin\Stat\AdCategoryStatController@index')->name('admin.stat.adCategories');
        Route::get('/articles', 'Admin\Stat\ArticleStatController@index')->name('admin.stat.articles');
        Route::get('/indexing', 'Admin\Stat\IndexingStatController@index')->name('admin.stat.indexing');
    });


    // Список запрещенных email адресов
    Route::get('/blocked-emails', 'Admin\BlockedEmails\BlockedEmailsController@showEmailsList')->name('admin.blocked-emails');
    Route::get('/blocked-emails/new', 'Admin\BlockedEmails\BlockedEmailsController@new')->name('admin.blocked-email.new');
    Route::post('/blocked-emails/create', 'Admin\BlockedEmails\BlockedEmailsController@create')->name('admin.blocked-email.create');
    Route::get('/blocked-emails/{id}/delete', 'Admin\BlockedEmails\BlockedEmailsController@delete')->name('admin.blocked-email.delete');
    Route::get('/blocked-emails/deleteMany/{id}', 'Admin\BlockedEmails\BlockedEmailsController@deleteMany')->name('admin.blocked-email.deleteMany');

    // Стоп-слова
    Route::get('/stop-words', 'Admin\StopWord\StopWordController@index')->name('admin.stop-word');
    Route::post('/stop-words/check', 'Admin\StopWord\StopWordController@check')->name('admin.stop-word.check');
    Route::post('/stop-words/delete', 'Admin\StopWord\StopWordController@delete')->name('admin.stop-word.delete');
    Route::post('/stop-words/store', 'Admin\StopWord\StopWordController@store')->name('admin.stop-word.store');
    Route::delete('/stop-words/{id}', 'Admin\StopWord\StopWordController@destroy')->name('admin.stop-word.destroy');

    // Пользователи
    Route::get('/users', 'Admin\User\UserController@showUsersList')->name('admin.users');
    Route::get('/users/search', 'Admin\User\UserController@search')->name('admin.users.search');
    Route::get('/user/{id}', 'Admin\User\UserController@showUserInformation')->name('admin.user');
    Route::post('/user/update', 'Admin\User\UserController@update')->name('admin.user.update');
    Route::get('/user/{id}/delete', 'Admin\User\UserController@delete')->name('admin.user.delete');
    Route::get('/user/deleteMany/{id}', 'Admin\User\UserController@deleteMany')->name('admin.user.deleteMany');


    // Объявления
    Route::get('/ads', 'Admin\Ad\Ad@showList')->name('admin.ads');
    Route::get('/ads/{search}', 'Admin\Ad\Ad@showList')->name('admin.ads.search');
    Route::get('/ad', 'Admin\Ad\Ad@show')->name('admin.ad');
    Route::get('/ad/uploader', 'Admin\Ad\Uploader@index')->name('admin.ad.uploader');
    Route::post('/ad/uploader', 'Admin\Ad\Uploader@uploadFile')->name('admin.ad.uploader.init');
    Route::post('/ad/uploader/delete', 'Admin\Ad\Uploader@deleteSelected')->name('admin.ad.uploader.delete');
    Route::post('/ad/uploader/publish', 'Admin\Ad\Uploader@publish')->name('admin.ad.uploader.publish');
    Route::get('/ad/{id}', 'Admin\Ad\Ad@edit')->name('admin.ad.edit');
    Route::post('/ad/create', 'Admin\Ad\Ad@create')->name('admin.ad.create');
    Route::post('/ad/update', 'Admin\Ad\Ad@update')->name('admin.ad.update');
    Route::post('/ad/delete', 'Admin\Ad\Ad@delete')->name('admin.ad.delete');
    Route::get('/ad/delete/{ids}', 'Admin\Ad\Ad@deleteMany')->name('admin.ad.deleteMany');
    Route::post('/ad/archive', 'Admin\Ad\Ad@archive')->name('admin.ad.archive');

    // Объявления > Категории
    Route::get('/adCategories', 'Admin\Ad\Category@showForm')->name('admin.adCategories');
    Route::get('/adCategories/{id}', 'Admin\Ad\Category@showForm')->name('admin.adCategories.edit');
    Route::post('/adCategories/create', 'Admin\Ad\Category@create')->name('admin.adCategories.create');
    Route::post('/adCategories/update', 'Admin\Ad\Category@update')->name('admin.adCategories.update');
    Route::get('/adCategories/delete/{id}', 'Admin\Ad\Category@delete')->name('admin.adCategories.delete');
    Route::get('/adCategories/deleteMany/{id}', 'Admin\Ad\Category@deleteMany')->name('admin.adCategories.deleteMany');

    // Объявления > Теги
    Route::get('/adTags', 'Admin\Ad\Tag@showForm')->name('admin.adTags');
    Route::get('/adTags/search', 'Admin\Ad\Tag@search')->name('admin.adTags.search');
    Route::get('/adTags/{id}', 'Admin\Ad\Tag@showForm')->name('admin.adTags.edit');
    Route::post('/adTags/create', 'Admin\Ad\Tag@create')->name('admin.adTags.create');
    Route::post('/adTags/update', 'Admin\Ad\Tag@update')->name('admin.adTags.update');
    Route::get('/adTags/delete/{id}', 'Admin\Ad\Tag@delete')->name('admin.adTags.delete');
    Route::get('/adTags/deleteMany/{ids}', 'Admin\Ad\Tag@deleteMany')->name('admin.adTags.deleteMany');

    // Объявления > Страны
    Route::get('/countries', 'Admin\Ad\Country@showForm')->name('admin.adCountries');
    Route::get('/countries/search', 'Admin\Ad\Country@search')->name('admin.adCountries.search');
    Route::get('/countries/{id}', 'Admin\Ad\Country@showForm')->name('admin.adCountries.edit');
    Route::post('/countries/create', 'Admin\Ad\Country@create')->name('admin.adCountries.create');
    Route::post('/countries/update', 'Admin\Ad\Country@update')->name('admin.adCountries.update');
    Route::get('/countries/delete/{id}', 'Admin\Ad\Country@delete')->name('admin.adCountries.delete');
    Route::get('/countries/deleteMany/{ids}', 'Admin\Ad\Country@deleteMany')->name('admin.adCountries.deleteMany');

    // Объявления > Области / Регионы
    Route::get('/regions', 'Admin\Ad\Region@showForm')->name('admin.adRegions');
    Route::get('/regions/search', 'Admin\Ad\Region@search')->name('admin.adRegions.search');
    Route::get('/regions/{id}', 'Admin\Ad\Region@showForm')->name('admin.adRegions.edit');
    Route::post('/regions/create', 'Admin\Ad\Region@create')->name('admin.adRegions.create');
    Route::post('/regions/update', 'Admin\Ad\Region@update')->name('admin.adRegions.update');
    Route::get('/regions/delete/{id}', 'Admin\Ad\Region@delete')->name('admin.adRegions.delete');
    Route::get('/regions/deleteMany/{ids}', 'Admin\Ad\Region@deleteMany')->name('admin.adRegions.deleteMany');

    // Объявления > Города
    Route::get('/cities', 'Admin\Ad\City@showForm')->name('admin.adCities');
    Route::get('/cities/search', 'Admin\Ad\City@search')->name('admin.adCities.search');
    Route::get('/cities/{id}', 'Admin\Ad\City@showForm')->name('admin.adCities.edit');
    Route::post('/cities/create', 'Admin\Ad\City@create')->name('admin.adCities.create');
    Route::post('/cities/update', 'Admin\Ad\City@update')->name('admin.adCities.update');
    Route::get('/cities/delete/{id}', 'Admin\Ad\City@delete')->name('admin.adCities.delete');
    Route::get('/cities/deleteMany/{ids}', 'Admin\Ad\City@deleteMany')->name('admin.adCities.deleteMany');

    // Объявления > Валюты
    Route::get('/currencies', 'Admin\Ad\Currency@showForm')->name('admin.adCurrencies');
    Route::get('/currencies/search', 'Admin\Ad\Currency@search')->name('admin.adCurrencies.search');
    Route::get('/currencies/{id}', 'Admin\Ad\Currency@showForm')->name('admin.adCurrencies.edit');
    Route::post('/currencies/create', 'Admin\Ad\Currency@create')->name('admin.adCurrencies.create');
    Route::post('/currencies/update', 'Admin\Ad\Currency@update')->name('admin.adCurrencies.update');
    Route::get('/currencies/delete/{id}', 'Admin\Ad\Currency@delete')->name('admin.adCurrencies.delete');



//    Route::get('/adCategory', 'AdminPageController@adCategory')->name('admin.adCategory');
//    Route::get('/country', 'AdminPageController@country')->name('admin.country');
//    Route::get('/city', 'AdminPageController@city')->name('admin.city');

    Route::get('/pages', 'Admin\Page\PageController@showPages')->name('admin.pages');
    Route::get('/pages/add', 'Admin\Page\PageController@showForm')->name('admin.page.new');
    Route::get('/page/{id}', 'Admin\Page\PageController@showForm')->name('admin.page.edit');
    Route::post('/page/create', 'Admin\Page\PageController@create')->name('admin.page.create');
    Route::post('/page/update', 'Admin\Page\PageController@update')->name('admin.page.update');
    Route::post('/page/delete', 'Admin\Page\PageController@delete')->name('admin.page.delete');

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

    Route::get('/seo', 'Admin\Seo\Seo@page')->name('admin.seo');
    Route::post('/seo/update', 'Admin\Seo\Seo@update')->name('admin.seo.update');
    Route::get('/seo/index', 'Admin\Seo\Index@form')->name('admin.seo.index');
    Route::get('/seo/contacts', 'Admin\Seo\Contacts@form')->name('admin.seo.contacts');
    Route::get('/seo/countries', 'Admin\Seo\Countries@form')->name('admin.seo.countries');
    Route::get('/seo/ad', 'Admin\Seo\Ad@form')->name('admin.seo.ad');
    Route::get('/seo/ad-product', 'Admin\Seo\AdProduct@form')->name('admin.seo.ad-product');
    Route::get('/seo/ad-user', 'Admin\Seo\AdUser@form')->name('admin.seo.ad-user');
    Route::get('/seo/ad-tag', 'Admin\Seo\AdTag@form')->name('admin.seo.ad-tag');
    Route::get('/seo/ad-category', 'Admin\Seo\AdCategory@form')->name('admin.seo.ad-category');
    Route::get('/seo/ad-country', 'Admin\Seo\AdCountry@form')->name('admin.seo.ad-country');
    Route::get('/seo/ad-region', 'Admin\Seo\AdRegion@form')->name('admin.seo.ad-region');
    Route::get('/seo/ad-city', 'Admin\Seo\AdCity@form')->name('admin.seo.ad-city');
    Route::get('/seo/search', 'Admin\Seo\Search@form')->name('admin.seo.search');
    Route::get('/seo/shop-list', 'Admin\Seo\ShopList@form')->name('admin.seo.shop-list');
});

// Публічний трекінг статистики товарів/магазину (без auth, без CSRF -- див. VerifyCsrfToken::except).
Route::post('/api/track/product/{id}', 'Api\TrackingController@trackProductEvent');
Route::post('/api/track/product/{id}/duration', 'Api\TrackingController@trackProductDuration');
Route::post('/api/track/shop/{userId}', 'Api\TrackingController@trackShopView');



/**
 * *******************************************************************************
 * ***************** ДВОМОВНІ FRONT-МАРШРУТИ (uk за замовчуванням, ru — /ru) *****
 * *******************************************************************************
 *
 * Увесь набір публічних маршрутів визначений ОДИН раз у $frontRoutes нижче,
 * і реєструється Laravel'ом ДВІЧІ — під префіксом /ru і без префіксу.
 * Контролери й логіка лишаються повністю незмінними для обох мов —
 * різниця лише в тому, яку локаль встановлює middleware 'setlocale'
 * (і, у Фазі 3, яку колонку БД читатимуть моделі).
 *
 * ВАЖЛИВО: /ru-група зареєстрована ПЕРШОЮ, до "catch-all" маршрутів
 * /{category} наприкінці — інакше запит /ru міг би помилково зловитись
 * українською групою як category="ru" ще до того, як Laravel дійде до
 * власне російської групи.
 */
// OAuth соціальний вхід — свідомо ПОЗА uk/ru системою: callback URL має
// бути один фіксований, зареєстрований у Google/Facebook Developer Console.
Route::get('/login/{provider}', 'Front\User\Auth\SocialAuthController@redirect')->name('social.login');
Route::get('/login/{provider}/callback', 'Front\User\Auth\SocialAuthController@callback')->name('social.callback');
 
$frontRoutes = function () {
    // Главная
    Route::get('/', "Front\HomeController@index")->name('index');
    Route::get('/subscribe', "Front\HomeController@subscribe")->name('subscribe');

    Route::get('/contacts', "Front\Page\PageController@contacts")->name('contacts');
    Route::post('/contacts', "Front\Page\PageController@contacts")->name('contacts.submit');
    Route::post('ulogin', 'ULoginController@login');

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
// Роут регистрации маркетплейсов
    Route::post('/business-register', 'Front\User\Auth\BusinessRegisterController@register')->name('post-business-register');
    Route::get('/business-register', 'Front\User\Auth\BusinessRegisterController@showRegistrationForm')->name('business-register');

// Профиль
    Route::middleware(['auth'])->group(function () {
        Route::get('/profile/', 'Front\User\UserController@edit')->name('profile.index');
        Route::post('profile/update', 'Front\User\UserController@updateUser')->name('profile.update');
        Route::get('/profile/ads', 'Front\User\UserController@ads')->name('profile.ads');
        Route::get('/profile/password', 'Front\User\UserController@password')->name('profile.password');
        Route::post('profile/password/update', 'Front\User\UserController@updatePassword')->name('profile.password.update');

        Route::get('/profile/type', 'Front\User\ProfileTypeController@index');
        Route::post('/profile/type', 'Front\User\ProfileTypeController@switchIsShopOwner')->name('switch-profile-type');
        Route::get('/profile/shop', 'Front\User\Shop\ShopDashboardController@index')->name('profile.shop.dashboard');
        Route::get('/profile/shop/stats', 'Front\User\Shop\ShopStatsController@index')->name('profile.shop.stats');
        Route::get('/profile/shop/import-export', 'Front\User\Shop\AllActionsController@index')->name('profile.shop');
        Route::get('/profile/shop/import', 'Front\User\Shop\ProductImportController@index')->name('profile.shop.import')->middleware('shop_owner');
        Route::post('/profile/shop/import/upload', 'Front\User\Shop\ProductImportController@upload')->name('profile.shop.import.upload')->middleware('shop_owner');
        Route::post('/profile/shop/import/confirm', 'Front\User\Shop\ProductImportController@confirm')->name('profile.shop.import.confirm')->middleware('shop_owner');
        Route::get('/profile/shop/import/progress', 'Front\User\Shop\ProductImportController@progress')->name('profile.shop.import.progress')->middleware('shop_owner');
        Route::post('/profile/shop/import/cancel', 'Front\User\Shop\ProductImportController@cancel')->name('profile.shop.import.cancel')->middleware('shop_owner');
        Route::get('/profile/shop/export', 'Front\User\Shop\AllActionsController@export')->name('profile.shop.export');
        Route::post('/profile/shop/export', 'Front\User\Shop\AllActionsController@generateExport')->name('profile.shop.export.generate');
        Route::get('/profile/shop/info', 'Front\User\Shop\ShopInfoController@index')->name('profile.shop.info');
        Route::post('/profile/shop/info', 'Front\User\Shop\ShopInfoController@update')->name('profile.shop.info.update');
        Route::get('/profile/shop/product/{id}/edit', 'Front\User\Shop\ProductEditController@edit')->name('profile.shop.product.edit');
        Route::post('/profile/shop/product/{id}/edit', 'Front\User\Shop\ProductEditController@update')->name('profile.shop.product.update');
        Route::get('/impersonate/leave', 'Front\ImpersonationController@leave')->name('impersonate.leave');
    });


// Блог
    Route::get('/blog/', 'Front\Article\CategoryController@page')->name('blog.index');
    Route::get('/blog/{slug}', 'Front\Article\ArticleController@page')->name('blog.article');
    Route::get('/blog/category/{slug}', 'Front\Article\CategoryController@page')->name('blog.category');


// Объявление
    Route::get('/ads/{slug}', 'Front\Ad\Ad@page')->name('ad.page');
    Route::get('/ads/{id}/goto-shop', 'Front\Ad\Ad@trackShopLinkRedirect')->name('ad.trackShopLink')->where('id', '[0-9]+');
    Route::post('/ads/{slug}', 'Front\Ad\Ad@message');
    Route::get('/ad/edit/{id}', 'Front\Ad\Ad@edit')->name('ad.edit');
    Route::post('/ad/edit/{id}', 'Front\Ad\Ad@update')->name('ad.update');
    Route::get('/ads/delete/{id}', 'Front\Ad\Ad@delete')->name('ad.delete');
    Route::get('/ads/status/{ad_id}/{status_id}', 'Front\Ad\Ad@changeStatus')->name('ad.changeStatus');


// Теги объявлений
    Route::get('/ad-tag/{slug}', 'Front\Ad\Tag@page')->name('tag');
    Route::get('/ad-tags/{slug}', function ($slug) {
        return redirect(route('tag', $slug), 301);
    });

// Поиск объявлений
    Route::get('/search', 'Front\Ad\Search@page')->name('ad.search');

// Страны
    Route::get('/regions/', 'Front\Ad\Region@all')->name('country.regions');
    Route::get('/regions/{country}', function () {
        return redirect( '/', 301);
    })->name('country.page');

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
    Route::get('/create-listing/creating/', 'Front\Ad\Ad@add')->name('ad.step.creating');
// step 4
    Route::get('/create-listing/success/', 'Front\Ad\Ad@create_step_success')->name('ad.step.success');
    Route::post('/create-listing/success/', 'Front\Ad\Ad@create_step_success')->name('ad.create.step.success');

// Автор объявлений
    Route::get('/author/{id}', 'Front\Ad\UserController@page')->name('author');
    Route::get('/stores', 'Front\User\StoreController@index')->name('stores');

// Бренды
    Route::get('/brands', 'Front\Brand\BrandController@index')->name('brands');

// Категории объявлений
    Route::get('/r/{filter}/{category}', 'Front\Ad\Category@filter')->name('filtered_category.page');
    Route::get('/r/{filter}/{category}/{subcategory}', 'Front\Ad\Category@filter')->name('filtered_subcategory.page');

    Route::get('/page/{slug}', 'Front\Page\PageController@page')->name('page');
    Route::get('/{category}', 'Front\Ad\Category@page')->name('category.page');
    Route::get('/{category}/{subcategory}', 'Front\Ad\Category@page')->name('sub_category.page');
};

// Російська версія — ОБОВ'ЯЗКОВО реєструється ПЕРШОЮ (до українських
// catch-all маршрутів нижче). Іменам маршрутів додається префікс "ru."
// (напр. "ru.index", "ru.blog.article"), щоб не конфліктувати з
// однойменними українськими — генерувати посилання: route('ru.blog.article', ...).
Route::middleware(['localized', 'setlocale:ru'])
    ->prefix('ru')
    ->as('ru.')
    ->group($frontRoutes);

// Українська версія — за замовчуванням, без префіксу. Імена маршрутів
// лишаються такими ж, як і завжди (route('index'), route('blog.article')
// і т.д.) — увесь наявний код і view з route()-викликами продовжують
// працювати без жодних змін.
Route::middleware(['localized', 'setlocale:uk'])
    ->group($frontRoutes);


/**
 * *******************************************************************************
 * ******************************* ADMIN PART ************************************
 * *******************************************************************************
 */

//Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');
