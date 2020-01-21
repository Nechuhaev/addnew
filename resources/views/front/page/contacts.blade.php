@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? "Контактная информация")

@section('meta_description', $meta['meta_description'] ?? "Контактная информация addnew.biz")

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

                    <form class="form-contact" action="{{ route('contacts.submit') }}" method="post" enctype="multipart/form-data" >
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="padding: 0 0 0 10px;margin: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                        @endif
                        <div class="form-group">
                            <label>Ваше имя <span class="star">*</span>:</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label>Введите ваш адрес электронной почты <span class="star">*</span>:</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="form-group">
                            <label>Выберите интересующий вас раздел <span class="star">*</span>:</label>
                            <select name="subject" class="form-control" style="width: 100%;">
                                <option value="0">-- Выберите --</option>
                                <option value="Проблемы регистрации и входа">Проблемы регистрации и входа</option>
                                <option value="Объявление на модерации">Объявление на модерации</option>
                                <option value="У меня не работает...">У меня не работает...</option>
                                <option value="Профиль, управление объявлениями">Профиль, управление объявлениями</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Описание <span class="star">*</span></label>
                            <textarea name="description" rows="8" class="form-control">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Изображения</label>
                            <input type="file" multiple name="images">
                            <p class="help">Допустимое количество загружаемых файлов: 5. Максимальный размер файла: 1024 KB.</p>
                        </div>

                        <div class="form-action">
                            <input type="submit" class="btn btn-contact" value="Отправить">
                        </div>

                    </form>

                </div>
            </div>
            <div class="show-more">
                <section class="show-more__text">
                    {!! $meta['description']  !!}
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">Показать</span>
            </div>
        </div>

    </main>
@endsection