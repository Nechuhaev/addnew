@extends('front.layout')

@section('meta_title', app()->getLocale() === 'ru' ? "Подать бесплатное объявление | Доска объявлений AddNew.Biz Украина" : "Подати безкоштовне оголошення | Дошка оголошень AddNew.Biz Україна")

@section('meta_description', app()->getLocale() === 'ru' ? "Доска объявлений AddNew.biz предлагает разместить бесплатное объявление любой тематики в нашем каталоге. Подать объявление могут зарегистрированные и незарегистрированные пользователи" : "Дошка оголошень AddNew.biz пропонує розмістити безкоштовне оголошення будь-якої тематики в нашому каталозі. Подати оголошення можуть як зареєстровані, так і незареєстровані користувачі")

@section('content')

<main class="steps-page">
    <div class="container">
        {{--<div class="banner">--}}
            {{--@include('front.adsense.top')--}}
        {{--</div>--}}

        <ul class="breadcrumb">
            <li><a href="{{ route('index') }}">{{ __('front.home') }}</a></li>
            <li><span>{{ __('ad_create.breadcrumb_category') }}</span></li>
        </ul>
        <h1>{{ __('ad_create.page_title') }}</h1>

        <ul class="steps-row" data-steps="4">
            <li class="steps-done">{{ __('ad_create.step_category') }}</li>
            <li class="steps-todo">{{ __('ad_create.step_details') }}</li>
            <li class="steps-todo">{{ __('ad_create.step_preview') }}</li>
            <li class="steps-todo">{{ __('ad_create.step_thanks') }}</li>
        </ul>
        <div class="steps-content" id="step-1">
            <h2>{{ __('ad_create.step1_heading_prefix') }} <span>{{ __('ad_create.breadcrumb_category') }}</span></h2>
            <div class="columns">
                <div class="col-2">
                    <p class="hidden-xs">{{ __('ad_create.intro_text1') }}</p>
                    <!--more-->
                    <p class="hidden-xs">{{ __('ad_create.intro_text2') }}</p>
                    <div class="notice-price hidden-xs">
                        <i class="icon icon-note"></i>
                        <div>
                            <h3>{{ __('ad_create.free_notice_heading') }}</h3>
                            <p>{{ __('ad_create.free_notice_text') }}</p>
                        </div>
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

                    <form class="form-step" action="{{ route('ad.create.step.category') }}" method="post">
                        @csrf
                        <label>{{ __('ad_create.select_category_label') }}</label>
                        @if ($parent_categories)
                            <div class="form-group">
                                <select name="category" style="width: 100%;">
                                    <option value="0">{{ __('ad_create.select_placeholder') }}</option>
                                    @foreach($parent_categories as $parent_category)

                                        <option
                                                {{ ($selected_parent_id == $parent_category['id']) ? 'selected' : '' }}
                                                value="{{ $parent_category['id'] }}">{{ $parent_category['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-group sub-category">
                            @if($children_categories)
                                <select name="sub_category" id="ad_sub_category" style="width: 100%" onchange="select_ad_category();">
                                    <option value="0">{{ __('ad_create.select_placeholder') }}</option>
                                    @foreach($children_categories as $children_category)
                                        <option {{ ($selected_child_id == $children_category['id']) ? 'selected' : '' }}
                                                value="{{ $children_category['id'] }}">{{ $children_category['name'] }}</option>
                                    @endforeach
                                </select>

                            @endif
                        </div>
                        <div class="form-action step-submit {{ (!isset($selected_parent_id)) ? 'hidden' : '' }}">
                            <input type="hidden" name="category_id" id="ad_category_id" value="{{ $selected_child_id ?? $selected_parent_id  ?? '0'}}">
                            <input type="submit" class="btn btn-step" value="{{ __('ad_create.next_button') }}">
                        </div>
                    </form>

                </div>
                <div class="col-2">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div><p>{!! __('ad_create.register_notice_html') !!}</p></div>
                        </div>
                    </div>
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <h4>{{ __('ad_create.how_to_post_heading') }}</h4>
                            </div>
                            <div class="notice-content">
                                @include('front.ad.partials.post-ad-seo-' . (app()->getLocale() === 'ru' ? 'ru' : 'uk'))
                                <span class="btn-notice-more">{{ __('ad_create.expand_button') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div> <!-- container -->
</main>
@endsection

@section('script')
    <script>
        $('[name=category]').on('change', function(){
            // Запрос на получение подкатегорий
            var value = $(this).val();

            var sub_categories = '<select name="sub_category" id="ad_sub_category" onchange="select_ad_category();"><option value="0">{{ __('ad_create.select_placeholder') }}</option>';

            $.getJSON("/api/ad/category/children/" + value, function ( data ) {

                if (data.data.length) {
                    $.each( data.data, function ( key, val ) {
                        sub_categories += "<option value='" + val.id + "'>" + val.name + "</option>";
                    } )
                    sub_categories += "</select>";
                    $('.sub-category').html(sub_categories);

                    $('.sub-category select').select2({
                        width: '100%'
                    })
                    $('.sub-category').prop('disabled', false);
                    $('.step-submit').addClass('hidden');
                    $('#ad_category_id').val(0);
                } else {
                    $('.sub-category').html("");
                    $('#ad_category_id').val(value);
                    $('.step-submit').removeClass('hidden');
                }
            });
        })

        function select_ad_category() {
            $('#ad_category_id').val($('#ad_sub_category').val());
            $('.step-submit').removeClass('hidden');
        }
    </script>
@endsection