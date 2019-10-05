@extends('front.layout')

@section('content')
    <main class="start-page">
        <div class="container">
            <h1><strong>Верстка проекта: addnew.biz</strong></h1>
            <ul class="markup-menu">
                <li><a href="{{ route('index') }}">Главная</a></li>
                <li><a href="{{ route('category') }}">Категория объявлений</a></li>
                <li><a href="{{ route('search') }}">Поиск</a></li>
                <li><a href="/adv-2.html">Объявление - больше одного фото</a></li>
                <li><a href="/adv.html">Объявление (без captcha) скорость PageSpeed 100%</a></li>
                <li><a href="/adv-captcha.html">Объявление (с подключенной капчей) - скорость PageSpeed 92%-94%(mob), 100%(desk)</a></li>
                <li><a href="/account-login.html">Вход</a></li>
                <li><a href="/account-registration.html">Регистрация</a></li>
                <li><a href="/account-forgot.html">Восстановление пароля</a></li>
                <li><a href="/steps-1.html">Публикация объявления: шаг 1</a></li>
                <li><a href="/steps-2.html">Публикация объявления: шаг 2</a></li>
                <li><a href="/steps-3.html">Публикация объявления: шаг 3</a></li>
                <li><a href="/steps-4.html">Публикация объявления: шаг 4</a></li>
                <li><a href="/blog.html">Блог</a></li>
                <li><a href="/blog-author.html">Статьи блога по автору</a></li>
                <li><a href="/post.html">Статья блога скрость PageSpeed 97%(mob), 100%(desk)</a></li>
                <li><a href="/post-optimized.html">Статья блога (оптимизированные картинки) - скорость PageSpeed 100%(mob и deks)</a></li>
                <li><a href="/countries.html">Страны</a></li>
                <li><a href="/contacts.html">Контакты</a></li>
                <li><a href="/confid.html">Confid</a></li>
                <li><a href="/account-adv.html">Личный кабинет - мои объявления</a></li>
                <li><a href="/account-edit.html">Личный кабинет - редактировать профиль</a></li>
                <li><a href="/404.html">404</a></li>
                <li><a href="/style-guide.html">Style-guide</a></li>
            </ul>

        </div>
    </main>
@endsection