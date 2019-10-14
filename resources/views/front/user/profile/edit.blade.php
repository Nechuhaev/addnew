@extends('front.layout')

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                <img src="img/banners/banner-4.jpg" alt="">
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Редактировать профиль</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>Редактировать профиль</h1>

                    <div class="account-author author-edit">
                        <div class="author-photo">
                            <img alt="" src="https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=250&amp;d=mm&amp;r=g" srcset="https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=500&amp;d=mm&amp;r=g 2x" class="author-avatar" height="250" width="250">
                        </div>

                        <div class="upload-file upload-avatar">
                            <label>Аватар</label><br>
                            <label class="upload-label">
                                <input name="file" type="file" class="input-file"  />
                                <div>Загрузить аватар</div>
                                <input class="input-file-name" type="text" id="input-file-name" value="Файл не выбран." disabled />
                            </label>

                            <span class="form-help">Максимальный размер файла: 1024 KB.</span>
                        </div>
                    </div>

                    <form class="form-account" action="#" method="post" enctype="multipart/form-data" >
                        <div class="columns">
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Имя пользователя</label>
                                    <input type="text" class="form-control" disabled="disabled" value="UserName">
                                </div>
                                <div class="form-group">
                                    <label>Псевдоним</label>
                                    <input type="text" class="form-control" value="UserName">
                                </div>
                                <div class="form-group">
                                    <label>Отображаемое имя</label>
                                    <input type="text" class="form-control" value="UserName">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Фамилия</label>
                                    <input type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Почта</label>
                                    <input type="mail" class="form-control" value="mail@mail.ru">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Обо мне</label>
                                    <textarea rows="8" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Новый пароль</label>
                                    <input type="text" class="form-control" placeholder="">
                                    <span class="form-help">Пароль должен быть минимум из семи символов.</span>
                                    <button class="btn">Сгенерировать пароль</button>
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Сайт</label>
                                    <input type="text" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Twitter:</label>
                                    <input type="text" class="form-control">
                                    <span class="form-help">Введите ваше имя пользователя в Twitter без URL-адреса.</span>
                                </div>
                                <div class="form-group">
                                    <label>Facebook:</label>
                                    <input type="text" class="form-control">
                                    <span class="form-help">Введите ваше имя пользователя в Facebook без URL-адреса. До сих пор нет? <a href="#">Получить специальный адрес</a></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-action">
                            <input type="submit" class="btn btn-edit-profile" value="Обновить профиль">
                        </div>

                    </form>
                </div>
                <aside class="column-right">
                    <h2 class="account-h2">Личный кабинет</h2>
                    <ul class="account-menu">
                        <li><a href="#">Мои объявления</a></li>
                        <li><a href="#">Редактировать профиль</a></li>
                        <li><a href="#">Выход</a></li>
                    </ul>
                    <h2 class="account-h2">Информация об учётной записи</h2>
                    <div class="account-author author">
                        <div class="author-photo">
                            <img alt="" src="https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=250&amp;d=mm&amp;r=g" srcset="https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=500&amp;d=mm&amp;r=g 2x" class="author-avatar" height="250" width="250">
                        </div>
                        <ul class="author-info">
                            <li><strong><a href="https://addnew.biz/author/lightlana/">LightLana</a></strong></li>
                            <li><strong>Активен с:</strong> Апрель 19, 2016 2:34 пп</li>
                            <li><strong>Последний вход:</strong> Сентябрь 3, 2019 8:45 дп</li>
                        </ul>
                    </div>
                    <div class="account-mail">
                        <img class="img-svg" height="20" width="20" src="img/dashicons/email.svg" />
                        <a href="mailto:mail@mail.ru">mail@mail.ru</a>
                    </div>
                    <h2 class="account-h2">Статистика учётной записи</h2>
                    <ul class="account-info">
                        <li>Активный объявлений: <strong>0</strong></li>
                        <li>Объявлений в ожидании: <strong>0</strong></li>
                        <li>Неактивных объявлений: <strong>2</strong></li>
                        <li>Всего объявлений: <strong>2</strong></li>
                    </ul>

                </aside>
            </div>

        </div>

    </main>
@endsection