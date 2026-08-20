@extends('front.layout')

@section('meta_title', "Шаг 2. Подать бесплатное объявление | Доска объявлений AddNew.Biz Украина")

@section('meta_description', "Доска объявлений AddNew.biz предлагает разместить бесплатное объявление любой тематики в нашем каталоге. Подать объявление могут зарегистрированные и незарегистрированные пользователи")

@section('content')
    <main class="steps-page">
        <div class="container">
            {{--<div class="banner">--}}
                {{--@include('front.adsense.top')--}}
            {{--</div>--}}

            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">{{ __('front.home') }}</a></li>
                <li><span>{{ __('ad_create.step_details') }}</span></li>
            </ul>

            <ul class="steps-row" data-steps="4">
                <li class="steps-done">{{ __('ad_create.step_category') }}</li>
                <li class="steps-done">{{ __('ad_create.step_details') }}</li>
                <li class="steps-todo">{{ __('ad_create.step_preview') }}</li>
                <li class="steps-todo">{{ __('ad_create.step_thanks') }}</li>
            </ul>
            <div class="steps-content" id="step-2">
                <h2>{{ __('ad_create.step1_heading_prefix') }} <span>{{ __('ad_create.step_details') }}</span></h2>
                <div class="columns">
                    <div class="col-2">
                        <p class="hidden-xs">{{ __('ad_create.intro_text1') }}</p>
                        <!--more-->
                        <p class="hidden-xs">{{ __('ad_create.intro_text2') }}</p>

                    </div>
                    <div class="col-2">
                        <div class="notice-wrap">
                            <div class="notice">
                                <i class="icon icon-lock"></i>
                                <div><p>{!! __('ad_create.register_notice_html') !!}</p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="category-change">
                    <strong>{{ __('ad_create.category_label') }} <br>{{ $category->path }}</strong>
                    <a href="{{ route('ad.step.category') }}">{{ __('ad_create.change_link') }}</a>
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
                <form class="form-step" action="{{ route('ad.create.step.details') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="columns">
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.author_label') }}<span class="star">(*)</span></label>
                                <input {{ (Auth::check()) ? 'disabled' : '' }} type="text" name="author" value="{{ $author ?? '' }}" class="form-control required">
                                <span class="form-help">{{ __('ad_create.author_help') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.telephone_label') }}<span class="star">(*)</span></label>
                                <input type="text" name="telephone" value="{{ $telephone ?? '' }}" class="form-control required">
                                <span class="form-help">{{ __('ad_create.telephone_help') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.country_label') }}<span class="star">(*)</span></label>
                                @if($countries)
                                    <select name="country_id" class="form-control ad-country-id">
                                        <option selected value="62">{{ __('front.country_ukraine') }}</option>
                                        <!-- @foreach($countries as $country)
                                            <option {{ ($country['id'] == $country_id) ? 'selected' : '' }} value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                                        @endforeach -->
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.region_label') }}<span class="star">(*)</span></label>
                                @if($regions)
                                    <select name="region_id" class="form-control ad-region-id">
                                        <option value="0">{{ __('ad_create.select_dash_placeholder') }}</option>
                                            @foreach($regions as $region)
                                            <option {{ ($region['id'] == $region_id) ? 'selected' : '' }} value="{{ $region['id'] }}">{{ $region['name'] }}</option>
                                            @endforeach
                                    </select>
                                @else
                                    <select name="region_id" disabled class="form-control ad-region-id">
                                        <option value="0">{{ __('ad_create.select_country_first') }}</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.city_label') }}<span class="star">(*)</span></label>
                                @if($cities)
                                    <select name="city_id" class="form-control ad-city-id">
                                        <option value="0">{{ __('ad_create.select_dash_placeholder') }}</option>
                                        @foreach($cities as $city)
                                            <option {{ ($city['id'] == $city_id) ? 'selected' : '' }} value="{{ $city['id'] }}">{{ $city['name'] }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select name="city_id" disabled class="form-control ad-city-id">
                                        <option value="0">{{ __('ad_create.select_region_first') }}</option>
                                    </select>
                                @endif
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.email_label') }}<span class="star">(*)</span></label>
                                <input {{ (Auth::check()) ? 'disabled' : '' }} type="email" name="email" value="{{ $email ?? '' }}" class="form-control required">
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.name_label') }}<span class="star">(*)</span></label>
                                <input type="text" name="name" value="{{ $name ?? '' }}" class="form-control required">
                                <span class="form-help">{{ __('ad_create.name_help') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.tags_label') }}<span class="star">(*)</span></label>
                                <input type="text"
                                       name="tags"
                                       data-json="{{ (isset($tags)) ? $tags : '' }}"
                                       id="tags"
                                       class="form-control required"
                                       value="{{ $tags ?? '' }}">
                                <span class="form-help">{{ __('ad_create.tags_help') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.content_label') }}<span class="star">(*)</span></label>
                                <textarea rows="8" name="content" class="form-control required">{{ $content ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="col-2">

                            <div class="form-group upload-file">
                                <label>{{ __('ad_create.image_label') }}</label><br>
                                <label class="upload-label">
                                    <input name="image[]" type="file" class="input-file"  multiple />
                                    <div>{{ __('ad_create.upload_button') }}</div>
                                    <input class="input-file-name" type="text" id="input-file-name" value="{{ __('ad_create.no_file_selected') }}" disabled />
                                </label>

                                <span class="form-help">{{ __('ad_create.image_help') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.price_label') }}<span class="star">(*)</span></label>
                                <input type="text" name="price" value="{{ $price ?? '' }}" class="form-control required">
                                <span class="form-help">{{ __('ad_create.price_help') }}</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>{{ __('ad_create.currency_label') }}<span class="star">(*)</span></label>
                                <select name="currency_id" class="form-control required">
                                    <option value="">{{ __('ad_create.select_dash_placeholder') }}</option>
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
                        <input type="submit" name="step1" id="step1" class="btn btn-step" value="{{ __('ad_create.continue_button') }}">
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
                var html = '<option value="0">{{ __('ad_create.select_dash_placeholder') }}</option>';

                $.getJSON("/api/ad/country/" + value, function ( data ) {

                    $.each( data.data.regions, function ( key, val ) {
                        html += "<option value='" + val.id + "'>" + val.name + "</option>";
                    } )

                    $('.ad-region-id').html(html).prop('disabled', false);
                });
            } else {
                var html = '<option value="0">{{ __('ad_create.select_country_first') }}</option>';
                $('.ad-region-id').html(html).prop('disabled', true).trigger('change');
            }
        })

        $('.ad-region-id').on('change', function(){
            var value = $(this).val();
            if (value && value != 0) {
                var html = '<option value="0">{{ __('ad_create.select_dash_placeholder') }}</option>';

                $.getJSON("/api/ad/region/" + value, function ( data ) {

                    $.each( data.data.cities, function ( key, val ) {
                        html += "<option value='" + val.id + "'>" + val.name + "</option>";
                    } )

                    $('.ad-city-id').html(html).prop('disabled', false);
                });
            } else {
                var html = '<option value="0">{{ __('ad_create.select_region_first') }}</option>';
                $('.ad-city-id').html(html).prop('disabled', true).trigger('change');
            }
        })

        $("#tags").tagEditor({
            initialTags: $("#tags").data('json'),
            placeholder: '{{ __('ad_create.tags_placeholder') }}',
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