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
                <li><a href="{{ route('index') }}">Главная</a></li>
                <li><span>Подать объявление</span></li>
            </ul>

            <ul class="steps-row" data-steps="4">
                <li class="steps-done">Категория</li>
                <li class="steps-done">Детали</li>
                <li class="steps-done">Предпросмотр</li>
                <li class="steps-done">Спасибо</li>
            </ul>
            <div class="steps-content" id="step-4">
                <h2>Объявление принято</h2>
                <p>Спасибо за то, что пользуетесь нашим сайтом!</p>
                <div class="buttons">
                    <a href="{{ route('index') }}" class="btn btn-step">На главную</a>
                    <a href="{{ route('ad.page', ['slug' => $ad->slug]) }}" class="btn btn-step">К объявлению</a>
                </div>
            </div>
        </div> <!-- container -->
    </main>
@endsection