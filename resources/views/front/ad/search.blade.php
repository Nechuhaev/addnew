@extends('front.layout')

@section('content')
    <main class="category-page">
        <div class="container">
            <div class="banner">
                <img src="img/banners/banner-4.jpg" alt="">
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Поиск - "доставка цветов"</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        <img src="img/banners/banner-5.jpg" alt="">
                    </div>
                    <h1>Результат поиска '{{ request()->get('s') }}' — {{ $total }} шт.</h1>
                    @include('front.loop.ads', ['ads' => $ads])

                    {!!  $links  !!}

                    <div class="banner">
                        <img src="{{ asset('assets/front/img/banners/banner-6.jpg') }}" alt="">
                    </div>

                </div>
                <aside class="column-right">
                    @widget('front.adCategories')
                    <div class="banner">
                        <img src="img/banners/banner-9.jpg" alt="">
                    </div>
                    @widget('front.adTags', ['tags' => $tags])
                </aside>
            </div>

        </div>

    </main>
@endsection