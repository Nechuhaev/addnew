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
                                    <img src="{{ asset($parent_category['image']) }}" alt="{{ $parent_category['name'] }}" class="catalog-img">
                                    <a href="{{ $parent_category['url'] }}">{{ $parent_category['name'] }}</a>
                                </li>
                                @if($parent_category['children'])
                                    @foreach($parent_category['children'] as $child)
                                    <li><a href="{{ $child['url'] }}">{{ $child['name'] }}</a></li>
                                    @endforeach
                                @endif
                                </ul>
                            @endforeach
                            @if($loop->iteration == 3)
                                {{ $adsense::block('home-vertical') }}
                            @endif
                        </div>
                @endforeach
                </div>
            @endif

            <div class="show-more">
                <section class="show-more__text">
                    {!! $meta['description']  !!}
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">{{ __('front.show_more') }}</span>
            </div>

            <div class="banner">
                @include('front.adsense.bottom')
            </div>

            @if ($ads_groups)
                <h2 class="last-advs-header">{{ __('front.latest_ads') }}</h2>
                    @foreach($ads_groups as $group)
                    <div class="last-advs" {!!  ($loop->iteration != 1) ? 'style="border:none;"' : ''  !!}>
                        @foreach($group as $ad)
                        <a href="{{ $ad['url'] }}">
                        <span class="last-adv-title">
                            <img src="{{ $ad['image'] }}" alt="{{ $ad['name'] }}">
                            <strong> {{ $ad['name'] }}</strong>
                        </span>
                            <span class="last-adv-price"> {{ $ad['price'] }} </span>
                        </a>
                        @endforeach
                    </div>
                    @endforeach

            @endif
            
            @if($blog_articles && $blog_articles->count())
                <h2 class="last-advs-header">Останні статті в блозі</h2>
                <div class="related-ads">
                    @foreach($blog_articles as $article)
                        <div class="related-ad">
                            <div class="image">
                                <a href="{{ $article['url'] }}">
                                    <img src="{{ $article['image'] ?: asset('assets/front/img/placeholder.png') }}" alt="{{ $article['name'] }}" class="img-responsive">
                                </a>
                            </div>
                            <a href="{{ $article['url'] }}" class="ad-heading">{{ $article['name'] }}</a>
                        </div>
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

            @if($tags)
                <div class="random-cities widget-tag-cloud" style="padding-top: 25px; height: initial">
                    @foreach($tags as $tag)
                        <a href="{{ $tag['url'] }}">{{ $tag['name'] }}</a>
                    @endforeach
                </div>
            @endif

            <section>
                <p class="section-heading">{{ __('front.popular_shops') }}</p>
                <p class="text-center"><a class="btn" href="{{ route('stores') }}">{{ __('front.all_shops_link') }}</a></p>
                <div class="related-ads">
                    @foreach($shop_users as $user)
                        <div class="related-ad">
                            <div class="image">
                                <a href="{{ route('author', $user->id)  }}" title="{{ __('front.seller_on_site', ['name' => $user->username]) }}">
                                    <img src="{{ $user->image ?? asset('assets/front/img/placeholder.png') }}" alt="{{ __('front.shop_page_on_site', ['name' => $user->username]) }}" class="img-responsive">
                                </a>
                            </div>
                            <div class="price">{{ \App\Translation::pluralChoice('front', 'offers_count', $user->ads_count) }}</div>
                            <a href="{{ route('author', $user->id) }}" title="{{ __('front.seller_on_site', ['name' => $user->username]) }}" class="ad-heading">{{ $user->username }}</a>
                        </div>
                    @endforeach
                </div>
            </section>

        </div>

    </main>
@endsection
