АНАЛІТИКА СТАТЕЙ — ПОВНИЙ ПАКЕТ ДЕПЛОЮ
=========================================

Структура архіву повторює шляхи на сервері 1-в-1.
Розпакуйте і скопіюйте кожен файл у відповідне місце в
/home/addnew/addnew.biz/www/

  database/migrations/2026_08_12_100000_create_article_views_table.php
  app/ArticleView.php
  app/Models/Stats/Article.php
  app/Http/Controllers/Admin/Stat/ArticleStatController.php
  resources/views/admin/stat/articles.blade.php

(Директорію app/Models/Stats/ і Admin/Stat/, якщо їх ще нема — створити,
хоча Stats/ вже має існувати, бо там лежить Ad.php)

ПОРЯДОК ДІЙ НА СЕРВЕРІ
-----------------------

1. Залити файли (scp / файловий менеджер хостингу) точно за шляхами вище.

2. Перевірити, що клас реально на місці:
   ls -la app/ArticleView.php
   ls -la app/Models/Stats/Article.php

3. Роут — переконатись, що в routes/web.php у групі:
   Route::prefix('stat')->group(function () {
       Route::get('/ads', 'Admin\Stat\AdStatController@index')->name('admin.stat.ads');
       Route::get('/articles', 'Admin\Stat\ArticleStatController@index')->name('admin.stat.articles');
   });
   (цей рядок вже мав бути доданий раніше — просто звірте)

4. Пункт меню — resources/views/admin/menu.blade.php — вже додано раніше,
   звірте наявність:
   <li class="sidebar-item">
       <a href="{{ route('admin.stat.articles') }}" class="sidebar-link">
           <i class="mdi mdi-chart-bar"></i>
           <span class="hide-menu">Статьи</span>
       </a>
   </li>

5. Міграція (ЩЕ НЕ ВИКОНАНА — обов'язковий крок):
   php7.4 artisan migrate
   Перевірити, що таблиця article_views створилась:
   php7.4 artisan migrate:status | grep article_views

6. Перегенерувати автозавантажувач (про всяк випадок, після нових класів):
   php7.4 /usr/local/bin/composer dump-autoload

7. Очистити кеші:
   php7.4 artisan config:clear
   php7.4 artisan cache:clear

8. Перевірити в браузері: /admin/stat/articles
   Графік буде порожній (0 переглядів) — це нормально, бо запис
   переглядів (крок 9) ще не підключено.

9. ЩЕ НЕ ЗРОБЛЕНО — окремий крок, потребує файл
   Front\Article\ArticleController@page (рендерить /blog/{slug}):
   Треба додати виклик:

   \App\ArticleView::record(
       $article->id,
       $request->ip(),
       'view',
       $request->header('referer')
   );

   Без цього кроку таблиця article_views залишиться порожньою —
   графік і топ-статті показуватимуть нулі.

ВАЖЛИВО
-------
- APP_DEBUG поверніть на false в .env, якщо ще не повернули після
  діагностики попередньої помилки.
- Debugbar (barryvdh/laravel-debugbar) залишився встановленим як
  звичайна (не dev) залежність після composer install — це не
  критично, але для продакшн-сайту його варто прибрати з
  config/app.php (масив providers) в майбутньому, щоб не тягнув
  зайве на кожен запит.
