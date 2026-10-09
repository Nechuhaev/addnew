@extends('front.layout')

@section('meta_title', $meta['meta_title'])
@section('meta_description', $meta['meta_description'])

@section('style')
    @if($term !== '')
        <meta name="robots" content="noindex, follow" />
    @endif
@endsection

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('user.store_list') }}


                    @php($ru = app()->getLocale() === 'ru')
                    <h1>{{ $ru ? 'Список магазинов' : 'Список магазинів' }}</h1>

                    <form class="in-shop-search stores-search" method="get" action="{{ url()->current() }}" role="search">
                        @if($selected_country)
                            <input type="hidden" name="country" value="{{ $selected_country->slug }}">
                        @endif
                        <label for="stores-q" class="in-shop-search__label">{{ $ru ? 'Поиск магазина' : 'Пошук магазину' }}</label>
                        <div class="in-shop-search__row">
                            <input type="search" id="stores-q" name="q" value="{{ $term }}" maxlength="100" placeholder="{{ $ru ? 'Название магазина или сайт' : 'Назва магазину або сайт' }}">
                            <button type="submit" class="btn">{{ $ru ? 'Найти' : 'Знайти' }}</button>
                        </div>
                        @if($term !== '')
                            <a href="{{ $selected_country ? route('stores', ['country' => $selected_country->slug]) : route('stores') }}" class="in-shop-search__reset">{{ $ru ? 'Сбросить поиск' : 'Скинути пошук' }}</a>
                        @endif
                    </form>

                    @if($term !== '' && $shop_users->total() === 0)
                        <p class="search-empty__text">{{ $ru ? 'Магазинов по запросу «' . $term . '» не найдено.' : 'Магазинів за запитом «' . $term . '» не знайдено.' }}
                            <a href="{{ route($ru ? 'ru.ad.search' : 'ad.search', ['s' => $term]) }}">{{ $ru ? 'Искать среди товаров' : 'Шукати серед товарів' }}</a></p>
                    @endif

                    <div class="stores">

                        <div class="filter">
                            <div class="filter__item @if(!$selected_country) active @endif">
                                <a href="{{ route('stores') }}">{{ app()->getLocale() === 'ru' ? 'Все страны' : 'Усі країни' }}</a>
                            </div>
                            @foreach($countries as $country)
                                <div class="filter__item @if($selected_country && $country->id == $selected_country->id) active @endif">
                                    <a href="{{ route('stores', ['country' =>  $country->slug]) }}">{{ $country->name }}</a>
                                </div>
                            @endforeach

                        </div>

                        <div class="stores__list">
                            @foreach($shop_users->items() as $shop_user)
                                <div class="stores__list__item">
                                    <div class="stores__list__item-logo">
                                        <img src="{{ $shop_user->image ?? asset('assets/front/img/placeholder.png') }}" alt="{{ $shop_user->username }}">
                                    </div>
                                    <div class="stores__list__item-content">
                                        <div class="stores__list__item-name">{{ $shop_user->username }}</div>
                                        <p>{{ \Illuminate\Support\Str::words($shop_user->info, 29) }}</p>
                                        <a title="{{ $shop_user->username }} — addnew.biz" href="{{ route('author', $shop_user->id) }}" class="btn btn-success">{{ $ru ? 'Перейти к товарам' : 'Перейти до товарів' }}</a>
                                    </div>
                                    <div class="stores__list__item-offers">
                                        <div class="offer__count">{{ $shop_user->ads_count }}</div>
                                        <div class="offer_text">{{ $ru ? 'Предложений' : 'Пропозицій' }}</div>
                                    </div>
                                </div>

                                @if($loop->iteration % 6 == 0)
                                    <div class="banner">
                                        @include('front.adsense.top')
                                    </div>

                                @endif
                            @endforeach

                                @if($meta['description'])
                                    <div class="show-more">
                                        <section class="show-more__text">
                                            {!! $meta['description']  !!}
                                        </section>
                                        <div class="show-more__shadow"></div>
                                        <span class="show-more__btn btn-show">{{ app()->getLocale() === 'ru' ? 'Показать' : 'Показати' }}</span>
                                    </div>
                                @endif

                        </div>
                        {{ $shop_users->links('front.widgets.paginate') }}
                    </div>



        </div>
    </main>
@endsection