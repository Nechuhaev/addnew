@extends('front.layout')

@section('content')
    <main class="steps-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">Главная</a></li>
                <li><span>Детали</span></li>
            </ul>

            <ul class="steps-row" data-steps="4">
                <li class="steps-done">Категория</li>
                <li class="steps-done">Детали</li>
                <li class="steps-todo">Предпросмотр</li>
                <li class="steps-todo">Спасибо</li>
            </ul>
            <div class="steps-content" id="step-2">
                <h2>Размещение объявления: <span>Детали</span></h2>
                <div class="columns">
                    <div class="col-2">
                        <p class="hidden-xs">Пожалуйста, заполните поля ниже, чтобы разместить объявление на сайте. Обязательные для заполнения поля обозначены звездочкой (&nbsp;*&nbsp;). У вас будет возможность ознакомиться с вашим объявлением перед его размещением.</p>
                        <!--more-->
                        <p class="hidden-xs">Зарегистрируйтесь бесплатно и начните размещать объявления в считанные минуты. Управляйте объявлениями из личного кабинета.</p>

                    </div>
                    <div class="col-2">
                        <div class="notice-wrap">
                            <div class="notice">
                                <i class="icon icon-lock"></i>
                                <div><p><a href="#">Зарегистрируйтесь</a> бесплатно и начните размещать объявления в считанные минуты. Управляйте объявлениями из личного кабинета.</p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="category-change">
                    <strong>Категория: <br>{{ $category->path }}</strong>
                    <a href="{{ route('ad.step.category') }}">Изменить</a>
                </div>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul style="padding: 0 0 0 10px;margin: 0;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form class="form-step" action="{{ route('ad.create.step.details') }}" method="post" enctype="multipart/form-data" >
                    @csrf
                    <div class="columns">
                        <div class="col-2">
                            <div class="form-group">
                                <label>Автор объявления<span class="star">(*)</span></label>
                                <input {{ (Auth::check()) ? 'disabled' : '' }} type="text" name="author" value="{{ $author ?? '' }}" class="form-control required">
                                <span class="form-help">Введите имя, от лица которого вы публикуете объявление.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Телефон<span class="star">(*)</span></label>
                                <input type="text" name="telephone" value="{{ $telephone ?? '' }}" class="form-control required">
                                <span class="form-help">Введите телефонный номер в международной системе нумерации, например: +380501112233.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Страна<span class="star">(*)</span></label>
                                @if($countries)
                                    <select name="country_id" class="form-control ad-country-id">
                                        <option value="">-- Выберите --</option>
                                        @foreach($countries as $country)
                                            <option {{ ($country['id'] == $country_id) ? 'selected' : '' }} value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Область<span class="star">(*)</span></label>
                                @if($regions)
                                    <select name="region_id" class="form-control ad-region-id">
                                        <option value="0">-- Выберите --</option>
                                            @foreach($regions as $region)
                                            <option {{ ($region['id'] == $region_id) ? 'selected' : '' }} value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                                            @endforeach
                                    </select>
                                @else
                                    <select name="region_id" disabled class="form-control ad-region-id">
                                        <option value="0">-- Сначала выберите страну --</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Город<span class="star">(*)</span></label>
                                @if($cities)
                                    <select name="city_id" class="form-control ad-city-id">
                                        <option value="0">-- Выберите --</option>
                                        @foreach($cities as $city)
                                            <option {{ ($city['id'] == $city_id) ? 'selected' : '' }} value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select name="city_id" disabled class="form-control ad-city-id">
                                        <option value="0">-- Сначала выберите область --</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Электронная почта<span class="star">(*)</span></label>
                                <input {{ (Auth::check()) ? 'disabled' : '' }} type="email" name="email" value="{{ $email ?? '' }}" class="form-control required">
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Заголовок<span class="star">(*)</span></label>
                                <input type="text" name="name" value="{{ $name ?? '' }}" class="form-control required">
                                <span class="form-help">Введите наименование товара, объекта или услуги. Чем точнее тематические слова,
                                    тем выше вероятность показа вашего объявления в поисковиках. В заголовке не допускается:
                                    номер телефона, электронный адрес, ссылки. Не допускаются заглавные буквы (кроме аббревиатур).</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Метки</label>
                                <input type="text"
                                       name="tags"
                                       data-json="{{ (isset($tags)) ? $tags : '' }}"
                                       id="tags"
                                       class="form-control">
                                <span class="form-help">Метки - это ключевые слова, по которым поисковые системы определяют, что именно
                                    находится на странице. Это поле не обязательно к заполнению, но наличие правильных меток увеличит количество просмотров
                                    вашего объявления. Используйте только те ключевые слова, которые имеют отношение к вашему объявлению.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Описание<span class="star">(*)</span></label>
                                <textarea rows="8" name="content" class="form-control required">{{ $content ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-2">

                            <div class="form-group upload-file">
                                <label>Изображение</label><br>
                                <label class="upload-label">
                                    <input name="image[]" type="file" class="input-file"  multiple />
                                    <div>Загрузить</div>
                                    <input class="input-file-name" type="text" id="input-file-name" value="Файл не выбран." disabled />
                                </label>

                                <span class="form-help">Допустимое количество загружаемых файлов: 5. Максимальный размер файла: 1024 KB.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Цена<span class="star">(*)</span></label>
                                <input type="text" name="price" value="{{ $price ?? '' }}" class="form-control required">
                                <span class="form-help">Введите реальную стоимость вашего товара или услуги. Администрация площадки врпаве
                                    удалить объявление за некорректно предоставленную информацию.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Валюта<span class="star">(*)</span></label>
                                <select name="currency_id" class="form-control required">
                                    <option value="">-- Выберите --</option>
                                    @if($currencies)
                                        @foreach($currencies as $currency)
                                            <option {{ ($currency['id'] == $currency_id) ? 'selected' : '' }} value="{{ $currency['id'] }}">{{ $currency['code'] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="form-action">
                        <input type="submit" name="step1" id="step1" class="btn btn-step" value="Продолжить">
                    </div>

                </form>
            </div>
        </div> <!-- container -->
    </main>
@endsection

@section('style')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tag-editor/1.0.20/jquery.tag-editor.min.css" rel="stylesheet">
    <style>
        .tag-editor {
            box-sizing: border-box;
            font-size: 16px;
            width: 100%;
            max-width: 100%;
            height: 38px;
            background-color: #fff;
            border: 1px solid #e4e4e4;
            color: #727272;
            line-height: 30px;
        }
    </style>
@endsection

@section('script')
    <script>
        var jQuery = $;
    </script>
    <script src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>
    <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/caret/1.3.7/jquery.caret.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tag-editor/1.0.20/jquery.tag-editor.min.js"></script>

    <script>
        $('.ad-country-id').on('change', function(){
            var value = $(this).val();
            if (value && value != 0) {
                var html = '<option value="0">-- Выберите --</option>';

                $.getJSON("/api/ad/country/" + value, function ( data ) {

                    $.each( data.data.regions, function ( key, val ) {
                        html += "<option value='" + val.id + "'>" + val.name + "</option>";
                    } )

                    $('.ad-region-id').html(html).prop('disabled', false);
                });
            } else {
                var html = '<option value="0">-- Сначала выберите страну --</option>';
                $('.ad-region-id').html(html).prop('disabled', true).trigger('change');
            }
        })

        $('.ad-region-id').on('change', function(){
            var value = $(this).val();
            if (value && value != 0) {
                var html = '<option value="0">-- Выберите --</option>';

                $.getJSON("/api/ad/region/" + value, function ( data ) {

                    $.each( data.data.cities, function ( key, val ) {
                        html += "<option value='" + val.id + "'>" + val.name + "</option>";
                    } )

                    $('.ad-city-id').html(html).prop('disabled', false);
                });
            } else {
                var html = '<option value="0">-- Сначала выберите область --</option>';
                $('.ad-city-id').html(html).prop('disabled', true).trigger('change');
            }
        })

        $("#tags").tagEditor({
            initialTags: $("#tags").data('json'),
            placeholder: 'Добавить теги...',
            autocomplete: {
                minLength: 0,
                source: function (request, response) {
                    $.getJSON("/api/ad/tag/autocomplete/" + encodeURIComponent(request.term), function (json) {
                        response($.map(json.data, function(item){
                            return {
                                value: item.name
                            }
                        }));
                    });
                },
            }
        });
    </script>
@endsection