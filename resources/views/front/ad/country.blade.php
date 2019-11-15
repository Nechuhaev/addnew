@extends('front.layout')

@section('content')

<main class="category-page">
    <div class="container">
        <div class="banner">
            <img src="{{ asset('assets/front/img/banners/banner-4.jpg') }}" alt="">
        </div>

        {{ Breadcrumbs::render($breadcrumbs, $entity) }}

        @if(isset($children))
        <div class="children">
            @foreach($children as $child)
                <div class="child">
                    <a href="{{ $child->slug }}">{{ $child->name }}</a>
                </div>
            @endforeach
        </div>
        @endif


        <div class="columns columns-nowrap">
            <div class="column-content">
                <div class="banner">
                    <img src="{{ asset('assets/front/img/banners/banner-5.jpg') }}" alt="">
                </div>

                <div class="category">
                    @if($ads)
                        @foreach($ads as $ad)
                            <div class="category-item">
                                <div class="category-count">{{ $loop->iteration }}</div>
                                <div class="category-img">
                                    <a href="{{ $ad['url'] }}" title="{{ $ad['name'] }}" class="preview" data-rel="{{ $ad['image'] }}">
                                        <img width="250" height="250" src="{{ $ad['image'] }}" class="attachment-ad-medium size-ad-medium" alt="{{ $ad['name'] }}">
                                    </a>
                                </div>
                                <div class="category-caption">
                                    <a href="{{ $ad['url'] }}">{{ $ad['name'] }}</a>
                                    <p class="category-description">{{ $ad['content'] }}</p>
                                    <p class="category-meta">
                                        <i class="st-1"><strong>Размещено:</strong><span class="st-1">Август 28, 2019 8:56 дп</span></i>
                                        <i class="st-1"><strong>Страна:</strong><span class="st-1"><a href="{{ $ad['country_url'] }}">{{ $ad['country'] }}</a></span></i>
                                        <i class="st-1"><strong>Город:</strong><span class="st-1"><a href="{{ $ad['city_url'] }}">{{ $ad['city'] }}</a></span></i>
                                    </p>
                                </div>
                                <div class="category-price">
                                    <strong>{{ $ad['price'] }}</strong>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p>Объявлений не найдено</p>
                    @endif
                </div>


                {!!  $links  !!}

                <div class="banner">
                    <img src="{{ asset('assets/front/img/banners/banner-6.jpg') }}" alt="">
                </div>


                <div class="show-more hidden">
                    <section class="show-more__text">
                        {!! $entity->content !!}
                    </section>
                    <div class="show-more__shadow"></div>
                    <span class="show-more__btn btn-show">Показать</span>
                </div>

            </div>


            <aside class="column-right">
                @widget('front.adCategories', ['heading' => $entity->name])
                <div class="banner">
                    <img src="{{ asset('assets/front/img/banners/banner-9.jpg') }}" alt="">
                </div>
                @widget('front.adTags')
            </aside>
        </div>

    </div>

</main>


@endsection