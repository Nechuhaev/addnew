@extends('front.layout')

@section('meta_title', 'Редактировать объявление')
@section('meta_description', 'Редактировать объявление')

@section('content')
    <main class="steps-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">Главная</a></li>
                <li><span>Редактировать объявление</span></li>
            </ul>


            <form action="{{ route('ad.update', ['id' => $id]) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="steps-content" id="step-2" style="width: 600px;margin: 0 auto;max-width: 100%;">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-group">
                        <label>Название объявления<span class="star">(*)</span></label>
                        <input type="text" name="name" value="{{ $name ?? '' }}" class="form-control required">
                    </div>

                    <div class="form-group">
                        <label>Телефон<span class="star">(*)</span></label>
                        <input type="text" name="telephone" value="{{ $telephone ?? '' }}" class="form-control required">
                    </div>

                    <div class="form-group">
                        <label>Email<span class="star">(*)</span></label>
                        <input type="text" name="email" value="{{ $email ?? '' }}" class="form-control required">
                    </div>

                    <div class="form-group">
                        <label>Метки</label>
                        <input type="text"
                               name="tags"
                               data-json="{{ (isset($tags)) ? $tags : '' }}"
                               id="tags"
                               class="form-control">
                    </div>


                    <div class="form-group">
                        <label>Цена<span class="star">(*)</span></label>
                        <input type="text" name="price" value="{{ $price ?? '' }}" class="form-control required">
                    </div>

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

                    <div class="form-group">
                        <label>Описание объявления<span class="star">(*)</span></label>
                        <textarea name="content" class="form-control required" id="" cols="30" rows="10">{{ $content }}</textarea>
                    </div>

                    <div class="form-group upload-file">
                        <label>Изображение</label><br>
                        <label class="upload-label">
                            <input name="image[]" type="file" class="input-file"  multiple />
                            <div>Загрузить</div>
                            <input class="input-file-name" type="text" id="input-file-name" value="Файл не выбран." disabled />
                        </label>

                        <span class="form-help">Загрузите новые изображения. <b>Файлы, которые были добавлены ранее будут удалены.</b> Допустимое количество загружаемых файлов: 5. Максимальный размер файла: 1024 KB.</span>
                    </div>

                    <div class="form-action">
                        <input type="submit" name="submit" style="width: auto;" class="btn btn-step" value="Обновить информацию">
                    </div>



                    <hr>
                </div>
            </form>
        </div>
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