@extends('front.layout')

@section('meta_title', ($meta['meta_title'] ?? '') ?: (app()->getLocale() === 'ru' ? 'Addnew.biz | Главная страница' : 'Addnew.biz | Головна сторінка'))

@section('meta_description', $meta['meta_description'] ?? '')

@section('content')

    <main class="home-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <h1>{{ $meta['meta_title'] }}</h1>

            @if($categories)
                <div class="columns">
                    @foreach($categories as $category)
                        <div class="col">
                            <ul class="catalog">
                                <li class="first first-64">
                                    <img src="{{ asset($category['image']) }}" alt="{{ $category['name'] }}" class="catalog-img">
                                    <a href="{{ $category['url'] }}">{{ $category['name'] }}</a>
                                    @if(isset($categoryCounts) && isset($category['id']))
                                        @php
                                            $catCount = $categoryCounts[$category['id']] ?? 0;
                                        @endphp
                                        <span class="geo-count{{ $catCount ? '' : ' geo-count--zero' }}" title="{{ app()->getLocale() === 'ru' ? 'Объявлений' : 'Оголошень' }}">{{ number_format($catCount, 0, '', ' ') }}</span>
                                    @endif
                                </li>
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
                <span class="show-more__btn btn-show">{{ app()->getLocale() === 'ru' ? 'Показать' : 'Показати' }}</span>
            </div>

            <div class="banner">
                @include('front.adsense.bottom')
            </div>


            @if($cities)
                <div class="random-cities">
                    @foreach($cities as $city)
                        <a href="{{ $city['url'] }}">{{ $city['name'] }}@isset($city['count'])<span class="geo-count{{ $city['count'] ? '' : ' geo-count--zero' }}">{{ $city['count'] }}</span>@endisset</a>
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

            @if ($ads_groups)
                <h2 class="last-advs-header">{{ app()->getLocale() === 'ru' ? 'Последние объявления' : 'Останні оголошення' }}</h2>
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

            <section>
                @php
                    $ruShops = app()->getLocale() === 'ru';
                    // Тексти — ті самі переклади, що й на головній; запасний варіант, якщо ключа немає
                    $tr = function ($key, $uk, $ru, $repl = []) use ($ruShops) {
                        $v = __('front.' . $key, $repl);
                        return $v === 'front.' . $key ? ($ruShops ? $ru : $uk) : $v;
                    };
                @endphp
                <p class="section-heading">{{ $tr('popular_shops', 'Популярні магазини', 'Популярные магазины') }}</p>
                <p class="text-center"><a class="btn" href="{{ route($ruShops ? 'ru.stores' : 'stores') }}">{{ $tr('all_shops_link', 'Перейти до списку всіх магазинів', 'Перейти к списку всех магазинов') }}</a></p>
                <div class="related-ads">
                    @foreach($shop_users as $user)
                        <div class="related-ad">
                            <div class="image">
                                <a href="{{ route('author', $user->id)  }}" title="{{ $tr('seller_on_site', 'Продавець ' . $user->username . ' на addnew.biz', 'Продавец ' . $user->username . ' на addnew.biz', ['name' => $user->username]) }}">
                                    <img src="{{ $user->image ?? asset('assets/front/img/placeholder.png') }}" alt="{{ $tr('shop_page_on_site', 'Сторінка магазину ' . $user->username, 'Страница магазина ' . $user->username, ['name' => $user->username]) }}" class="img-responsive" loading="lazy">
                                </a>
                            </div>
                            @php
                                $offers = \App\Translation::pluralChoice('front', 'offers_count', $user->ads_count);
                            @endphp
                            <div class="price">{{ strpos($offers, 'front.') === 0 ? $user->ads_count . ' ' . ($ruShops ? 'предложений' : 'пропозицій') : $offers }}</div>
                            <a href="{{ route('author', $user->id) }}" title="{{ $tr('seller_on_site', 'Продавець ' . $user->username . ' на addnew.biz', 'Продавец ' . $user->username . ' на addnew.biz', ['name' => $user->username]) }}" class="ad-heading">{{ $user->username }}</a>
                        </div>
                    @endforeach
                </div>
            </section>


        </div>

    </main>
@endsection