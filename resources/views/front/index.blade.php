@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? 'Addnew.biz | Главная страница')

@section('meta_description', $meta['meta_description'] ?? '')

@section('content')
    <main class="home-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            @if($categories)
                <div class="columns">
                @foreach($categories as $category)
                        <div class="col">

                            @foreach($category as $parent_category)
                                <ul class="catalog">
                                <li class="first first-64">
                                    <img src="{{ asset($parent_category['image']) }}" alt="" class="catalog-img">
                                    <a href="https://addnew.biz/transport/">{{ $parent_category['name'] }}</a>
                                </li>
                                @if($parent_category['children'])
                                    @foreach($parent_category['children'] as $child)
                                    <li><a href="{{ $child['url'] }}">{{ $child['name'] }}</a></li>
                                    @endforeach
                                @endif
                                </ul>
                            @endforeach
                            </ul>
                        </div>
                @endforeach
                </div>
            @endif

            <div class="show-more">
                <section class="show-more__text">
                    {!! $meta['description']  !!}
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">Показать</span>
            </div>

            <div class="banner">
                @include('front.adsense.bottom')
            </div>

            @if ($ads)
                <h2 class="last-advs-header">Последние объявления</h2>
                <div class="last-advs">
                    @foreach($ads as $ad)
                        <a href="{{ $ad['url'] }}">
                        <span class="last-adv-title">
                            <img src="{{ $ad['image'] }}" alt="{{ $ad['name'] }}">
                            <strong> {{ $ad['name'] }}</strong>
                        </span>
                            <span class="last-adv-price"> {{ $ad['price'] }} </span>
                        </a>
                    @endforeach
                </div>
            @endif

            @if($cities)
                <div class="random-cities">
                    @foreach($cities as $city)
                        <a href="{{ $city['url'] }}">{{ $city['name'] }}</a>
                    @endforeach
                </div>
            @endif

        </div>

    </main>
@endsection