@php($ru = app()->getLocale() === 'ru')
@extends('front.layout')
@section('meta_title', ($ru ? 'Отписка' : 'Відписка') . ' | addnew.biz')
@section('style')
    <meta name="robots" content="noindex, nofollow" />
@endsection
@section('content')
    <main class="account-page">
        <div class="container">
            <h1>{{ $ru ? 'Вы отписались' : 'Ви відписалися' }}</h1>
            <p>{{ $ru ? 'Письма о снижении цены на товары из избранного больше не будут приходить.' : 'Листи про зниження ціни на товари з обраного більше не надходитимуть.' }}</p>
            <p>{{ $ru ? 'Включить их снова можно на странице' : 'Увімкнути їх знову можна на сторінці' }}
                <a href="{{ route('profile.favorites') }}">{{ $ru ? '«Избранное»' : '«Обране»' }}</a>.</p>
        </div>
    </main>
@endsection
