<?php

/**
 * *******************************************************************************
 * ******************************* Главная ***************************************
 * *******************************************************************************
 */

// Главная
Breadcrumbs::for('admin.index', function ($trail) {
    $trail->push('Главная', route('admin.index'));
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

    $title = "Пользователь " . $user->fullname;
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
    $trail->push('Редактировать профиль', route('profile.index'));
});

// Главная > Изменить пароль
Breadcrumbs::for('profile.password', function ($trail) {
    $trail->parent('index');
    $trail->push('Изменить пароль', route('profile.password'));
});

// Главная > Мои объявления
Breadcrumbs::for('profile.ads', function ($trail) {
    $trail->parent('index');
    $trail->push('Мои объявления', route('profile.ads'));
});

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