@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? "Список стран addnew.biz")

@section('meta_description', $meta['meta_description'] ?? "Список стран addnew.biz")


@section('content')
    <main class="country-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            <div class="columns">
                @if($countries)
                @foreach($countries as $country)
                    <div class="col">
                        <div class="country-wrap">
                            <a href="{{ $country['url'] }}" class="country-name"><img src="{{ $country['image'] }}" alt="{{ $country['name'] }}"> <span>{{ $country['name'] }}</span> </a>
                            @if($country['cities'])
                                <ul class="cities-list cities-list-visible">
                                @foreach($country['cities'] as $city)
                                    <li><a href="{{ $city['url'] }}">{{ $city['name'] }} <span class="city-rate">{{ $city['ads_count'] }}</span></a></li>
                                @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                @endforeach
                @endif
            </div>

            <div class="show-more">
                <section class="show-more__text">
                    {!! $meta['description']  !!}
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">Показать</span>
            </div>
        </div>

    </main>
@endsection