@extends('front.layout')

@section('meta_title', "Контактная информация addnew.biz");
@section('meta_description', "Контактная информация addnew.biz");

@section('content')
    <main class="contact-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">Главная</a></li>
                <li><span>Контакты</span></li>
            </ul>

            <h1>Контакты</h1>

            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-note"></i>
                            <div>
                                <p>Мы всегда готовы ответить на ваши вопросы о работе сайта, а так же принять конструктивную критику. Для более полного ответа, пожалуйста, помимо самого вопроса, предоставьте как можно больше дополнительных данных: ссылки на объявления, скриншоты.</p>
                            </div>
                        </div>
                    </div>
                </aside>
                <div class="column-content">

                    <div class="notice-wrap visible-xs">
                        <div class="notice">
                            <i class="icon icon-note"></i>
                            <div>
                                <p>Мы всегда готовы ответить на ваши вопросы о работе сайта, а так же принять конструктивную критику. Для более полного ответа, пожалуйста, помимо самого вопроса, предоставьте как можно больше дополнительных данных: ссылки на объявления, скриншоты.</p>
                            </div>
                        </div>
                    </div>

                    <form class="form-contact" action="#" method="post" enctype="multipart/form-data" >
                        <div class="form-group">
                            <label>Ваше имя <span class="star">*</span>:</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Введите ваш адрес электронной почты <span class="star">*</span>:</label>
                            <input type="mail" class="form-control" >
                        </div>
                        <div class="form-group">
                            <label>Выберите интересующий вас раздел <span class="star">*</span>:</label>
                            <select class="form-control" style="width: 100%;">
                                <option value="">-- Выберите --</option>
                                <option value="4">Проблемы регистрации и входа</option>
                                <option value="63">Объявление на модерации</option>
                                <option value="81">У меня не работает...</option>
                                <option value="173">Профиль, управление объявлениями</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Описание <span class="star">*</span></label>
                            <textarea rows="8" class="form-control"></textarea>
                        </div>

                        <div class="form-group">
                            <label>Изображения</label>
                            <input type="file">
                            <p class="help">Допустимое количество загружаемых файлов: 5. Максимальный размер файла: 1024 KB.</p>
                        </div>

                        <div class="form-action">
                            <input type="submit" class="btn btn-contact" value="Обновить профиль">
                        </div>

                    </form>

                </div>
            </div>

        </div>

    </main>
@endsection