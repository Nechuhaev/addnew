<?php

/**
 * *******************************************************************************
 * ******************************* Главная ***************************************
 * *******************************************************************************
 */

// Главная
use App\AdCity;
use App\AdCountry;
use App\AdRegion;

Breadcrumbs::for('admin.index', function ($trail) {
    $trail->push('Главная', route('admin.index'));
});


/**
 * *******************************************************************************
 * ******************************* Стоп - слова **********************************
 * *******************************************************************************
 */

// Главная > Пользователи
Breadcrumbs::for('admin.stop-word', function ($trail) {
    $trail->parent('admin.index');
    $trail->push('Стоп-слова', route('admin.stop-word'));
});


/**
 * *******************************************************************************
 * ******************************* Пользователи **********************************
 * *******************************************************************************
 */

// Главная > Пользователи
Breadcrumbs::for('admin.users', function ($trail) {
    $trail->parent('admin.index');
    $trail->push('Пользователи', route('admin.users'));
});

// Главная > Пользователи > Пользователь %s
Breadcrumbs::for('admin.user', function ($trail, $user) {
    $trail->parent('admin.users');

    $title = "Пользователь " . $user->username;
    $trail->push($title , route('admin.users', $user->id));
});


/**
 * *******************************************************************************
 * ******************************* Статьи ****************************************
 * *******************************************************************************
 */
// Главная > Категории статей
Breadcrumbs::for('admin.article.categories', function ($trail) {
    $trail->parent('admin.index');
    $trail->push('Категории', route('admin.article.category.index'));
});
// Главная > Категории статей > Добавить новую / Редактировать
Breadcrumbs::for('admin.article.category', function ($trail, $category = null) {
    $trail->parent('admin.article.categories');

    if (!$category) {
        $title = 'Добавить новую';
        $route = route('admin.article.category.add');
    } else {
        $title = $category->name;
        $route = route('admin.article.category.update', $category->id);
    }

    $trail->push($title, $route);
});

// Главная > Статьи
Breadcrumbs::for('admin.article.list', function ($trail) {
    $trail->parent('admin.index');
    $trail->push('Статьи', route('admin.articles'));
});
// Главная > Статьи > Добавить новую / Редактировать
Breadcrumbs::for('admin.article.article', function ($trail, $article = null) {
    $trail->parent('admin.article.list');

    if (!$article) {
        $title = 'Добавить статью';
        $route = route('admin.article.add');
    } else {
        $title = $article->name;
        $route = route('admin.article.update', $article->id);
    }

    $trail->push($title, $route);
});


/**
 * *******************************************************************************
 * ******************************* Объявления ************************************
 * *******************************************************************************
 */

// Главная > Объявления
Breadcrumbs::for('admin.ads', function ($trail) {
    $trail->parent('admin.index');
    $trail->push('Объявления', route('admin.ads'));
});

// Главная > Объявления > Добавить
Breadcrumbs::for('admin.ad', function ($trail) {
    $trail->parent('admin.ads');
    $trail->push('Добавить', route('admin.ad'));
});
// Главная > Объявления > Изменить
Breadcrumbs::for('admin.ad.edit', function ($trail, $ad = null) {
    $trail->parent('admin.ads');
    $trail->push($ad->name, route('admin.ad.edit', ['id' => $ad->id]));
});

// Главная > Объявления > Категории
Breadcrumbs::for('admin.adCategories', function ($trail) {
    $trail->parent('admin.ads');
    $trail->push('Категории', route('admin.adCategories'));
});
// Главная > Объявления > Теги
Breadcrumbs::for('admin.adTags', function ($trail) {
    $trail->parent('admin.ads');
    $trail->push('Метки', route('admin.adTags'));
});
// Главная > Объявления > Страны
Breadcrumbs::for('admin.adCountries', function ($trail) {
    $trail->parent('admin.ads');
    $trail->push('Страны', route('admin.adCountries'));
});
// Главная > Объявления > Страны > Области
Breadcrumbs::for('admin.adRegions', function ($trail) {
    $trail->parent('admin.adCountries');
    $trail->push('Области', route('admin.adRegions'));
});
// Главная > Объявления > Страны > Области > Города
Breadcrumbs::for('admin.adCities', function ($trail) {
    $trail->parent('admin.adRegions');
    $trail->push('Города', route('admin.adCities'));
});
// Главная > Объявления > Валюьы
Breadcrumbs::for('admin.adCurrencies', function ($trail) {
    $trail->parent('admin.ads');
    $trail->push('Валюты', route('admin.adCurrencies'));
});




/**
 * *******************************************************************************
 * ******************************* FRONT PART ************************************
 * *******************************************************************************
 */
Breadcrumbs::for('index', function ($trail) {
    $trail->push('Главная', route('index'));
});


/**
 * ******************************* ПРОФИЛЬ *******************************
 */
// Главная > Редактировать профиль
Breadcrumbs::for('profile.edit', function ($trail) {
    $trail->parent('index');
    $trail->push(__('profile.edit_heading'), route('profile.index'));
});

// Главная > Изменить пароль
Breadcrumbs::for('profile.password', function ($trail) {
    $trail->parent('index');
    $trail->push(__('profile.password_heading'), route('profile.password'));
});

// Главная > Мои объявления
Breadcrumbs::for('profile.ads', function ($trail) {
    $trail->parent('index');
    $trail->push(__('profile.my_ads_heading'), route('profile.ads'));
});

// Главная > Список магазинов
Breadcrumbs::for('user.store_list', function ($trail) {
    $trail->parent('index');
    $trail->push('Список магазинов', route('stores'));
});

// Главная > Мой магазин
Breadcrumbs::for('profile.shop.dashboard', function ($trail) {
    $trail->parent('index');
    $trail->push(__('front.my_shop'), route('profile.shop.dashboard'));
});

// Главная > Информация о магазине
Breadcrumbs::for('profile.shop.info', function ($trail) {
    $trail->parent('index');
    $trail->push(__('shop.info_heading'), route('profile.shop.info'));
});

// Главная > Импорт/экспорт товаров
Breadcrumbs::for('profile.shop.import-export', function ($trail) {
    $trail->parent('index');
    $trail->push(__('shop.import_export_heading'), route('profile.shop'));
});

// Главная > Импорт товаров
Breadcrumbs::for('profile.shop.import', function ($trail) {
    $trail->parent('profile.shop.import-export');
    $trail->push(__('shop_import.heading'), route('profile.shop.import'));
});

// Главная > Экспорт товаров
Breadcrumbs::for('profile.shop.export', function ($trail) {
    $trail->parent('profile.shop.import-export');
    $trail->push(__('shop.export_heading'), route('profile.shop.export'));
});

// Главная > Мой магазин > Редактировать товар
Breadcrumbs::for('profile.shop.product.edit', function ($trail, $product) {
    $trail->parent('profile.shop.dashboard');
    $trail->push(__('shop.edit_product_breadcrumb') . ': ' . $product->name, route('profile.shop.product.edit', ['id' => $product->id]));
});

/**
 * ******************************* Блог *******************************
 */
// Главная > Блог
Breadcrumbs::for('blog', function ($trail) {
    $trail->parent('index');
    $trail->push('Блог', route('blog.index'));
});
// Главная > Блог > Категория
Breadcrumbs::for('blog.category', function ($trail, \App\ArticleCategory $category) {
    $trail->parent('blog');
    $trail->push($category->name, $category->url);
});
// Главная > Блог > Категория > Статья
Breadcrumbs::for('blog.category.article', function ($trail, \App\Article $article) {
    $trail->parent('blog.category', $article->categories()->first());
    $trail->push($article->name, $article->url);
});

/**
 * ******************************* Страны *******************************
 */
// Главная > Страны
Breadcrumbs::for('countries', function ($trail) {
    $trail->parent('index');
    $trail->push('Страны', route('countries'));
});

// Главная > Страны > Страна
Breadcrumbs::for('country.page', function ($trail, $country = null) {
    $trail->parent('countries');
    $trail->push($country->name, route('country.page', ['country' => $country->slug]));
});
// Главная > Страны > Страна > Регион
Breadcrumbs::for('region.page', function ($trail, $region = null) {
    $trail->parent('country.page', $region->country);
    $trail->push($region->name, route('region.page', [
        'country' => $region->country->slug,
        'region' => $region->slug
    ]));
});
// Главная > Страны > Страна > Регион > Город
Breadcrumbs::for('city.page', function ($trail, $city = null) {
    $trail->parent('region.page', $city->region);
    $trail->push($city->name, route('city.page', [
        'country' => $city->region->country->slug,
        'region' => $city->region->slug,
        'city' => $city->slug
    ]));
});

/**
 * ******************************* Теги *******************************
 */
// Главная > Объявление с меткой "..."
Breadcrumbs::for('ad_tag', function ($trail, $tag) {
    $trail->parent('index');
    $trail->push('Объявление с меткой "' . $tag->name . '"', route('tag', ['slug' => $tag->slug]));
});

/**
 * ******************************* Юзер *******************************
 */
// Главная > Объявления пользователя "..."
Breadcrumbs::for('ad_user', function ($trail, $entity) {
    $trail->parent('index');
    $trail->push('Объявления пользователя ' . $entity->username, route('author', ['id', $entity->id]));
});


// Главная > Категория объявления
Breadcrumbs::for('category.page', function ($trail, $category) {

    $parent_category = $category->parent;
    if (!$parent_category) {
        $trail->parent('index');
        $trail->push($category->name, $category->url);
    } else {
        $trail->parent('category.page', $parent_category);
        $trail->push($category->name, $category->url);
    }
});

// Главная > Категория объявления > фильтр
Breadcrumbs::for('filtered.category.page', function ($trail, $category, $filter) {


    if ($filter) {
        $filter_entity = AdCountry::whereSlug($filter)->first();
        if (!$filter_entity) {
            $filter_entity = AdRegion::whereSlug($filter)->first();
            if (!$filter_entity) {
                $filter_entity = AdCity::whereSlug($filter)->first();
            }
        }
    }
    $name = $category->name . ' ' . $filter_entity->name;

    if (!$category->parent) {

        $url = route('filtered_category.page', [
            'filter' => $filter,
            'category' => $category->slug,
        ]);

    } else {

        $url = route('filtered_subcategory.page', [
            'filter' => $filter,
            'category' => $category->parent->slug,
            'subcategory' => $category->slug
        ]);

    }
    $trail->parent('category.page', $category);
    $trail->push($name, $url);
});


// Главная > Категория объявления > Дочерняя категория > Объявление
Breadcrumbs::for('ad.page', function ($trail, $ad) {

    $trail->parent('category.page', $ad->category);
    $trail->push($ad->name, route('ad.page', ['slug', $ad->slug]));

});