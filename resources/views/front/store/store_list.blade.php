@extends('front.layout')

@section('meta_title', "Мои объявления | Доска объявлений addnew.biz")
@section('meta_description', "Мои объявления | Доска объявлений addnew.biz")

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('user.store_list') }}


                    <h1>Список магазинов</h1>

                    <div class="stores">

                        <div class="filter">
                            <div class="filter__item @if(!$selected_country) active @endif">
                                <a href="{{ route('stores') }}">Все страны</a>
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
                                        <img src="{{ $shop_user->image ?? asset('assets/front/img/placeholder.png') }}" alt="">
                                    </div>
                                    <div class="stores__list__item-content">
                                        <div class="stores__list__item-name">{{ $shop_user->username }}</div>
                                        <p>{{ \Illuminate\Support\Str::words($shop_user->info, 29) }}</p>
                                        <a title="Продавец {{ $shop_user->username }} на сайте addnew.biz" href="{{ route('author', $shop_user->id) }}" class="btn btn-success">Перейти к товарам</a>
                                    </div>
                                    <div class="stores__list__item-offers">
                                        <div class="offer__count">{{ $shop_user->ads_count }}</div>
                                        <div class="offer_text">Предложений</div>
                                    </div>
                                </div>

                                @if($loop % 6 == 0)
                                    @include('front.adsense.top')
                                @endif
                            @endforeach

                        </div>
                        {{ $shop_users->links('front.widgets.paginate') }}
                    </div>



        </div>
    </main>
@endsection