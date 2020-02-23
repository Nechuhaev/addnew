@extends('front.layout')

@section('content')
    <main class="category-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Поиск - "{{ request()->get('s') }}"</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>
                    <h1>Результат поиска '{{ request()->get('s') }}' — {{ $total }} шт.</h1>
                    @include('front.loop.ads', ['ads' => $ads])

                    {!!  $links  !!}

                    <div class="banner">
                        @include('front.adsense.bottom-listing')
                    </div>

                </div>
                <aside class="column-right">
                    @widget('front.adCategories')
                    <div class="banner">
                        @include('front.adsense.category-right')
                    </div>
                    @widget('front.adTags', ['tags' => $tags])
                </aside>
            </div>

        </div>

    </main>
@endsection