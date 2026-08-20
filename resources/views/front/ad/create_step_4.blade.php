@extends('front.layout')
@section('meta_title', "Шаг 4. Подать бесплатное объявление | Доска объявлений AddNew.Biz Украина")
@section('meta_description', "Доска объявлений AddNew.biz предлагает разместить бесплатное объявление любой тематики в нашем каталоге. Подать объявление могут зарегистрированные и незарегистрированные пользователи")
@section('content')
    <main class="steps-page">
        <div class="container">
            {{--<div class="banner">--}}
                {{--@include('front.adsense.top')--}}
            {{--</div>--}}
            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">{{ __('front.home') }}</a></li>
                <li><span>{{ __('ad_create.breadcrumb_post_ad') }}</span></li>
            </ul>
            <ul class="steps-row" data-steps="4">
                <li class="steps-done">{{ __('ad_create.step_category') }}</li>
                <li class="steps-done">{{ __('ad_create.step_details') }}</li>
                <li class="steps-done">{{ __('ad_create.step_preview') }}</li>
                <li class="steps-done">{{ __('ad_create.step_thanks') }}</li>
            </ul>
            <div class="steps-content" id="step-4">
                <h2>{{ __('ad_create.ad_accepted_heading') }}</h2>
                <p>{{ __('ad_create.thank_you_text') }}</p>
                <div class="buttons">
                    <a href="{{ route('index') }}" class="btn btn-step">{{ __('ad_create.to_homepage_button') }}</a>
                    <a href="{{ route('ad.page', ['slug' => $ad->slug]) }}" class="btn btn-step">{{ __('ad_create.to_ad_button') }}</a>
                </div>
            </div>
        </div> <!-- container -->
    </main>
@endsection