<?php

// Главная
Breadcrumbs::for('admin.index', function ($trail) {
    $trail->push('Главная', route('admin.index'));
});

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

