@extends('front.layout')

{{--@section('meta_title', $meta['meta_title'])--}}
{{--@section('meta_description', $meta['meta_description'])--}}

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('user.store_list') }}


            <h1>Список производителей</h1>

            <div class="brands">

                <div class="brands__list">
                    <a href="#" class="brands__list__item">
                        <img src="https://placehold.it/1000x1000" alt="brand 1">
                        <h2 class="brand__name">Lorem ipsum</h2>
                    </a>

                    <div class="brands__list__item">
                        <img src="https://placehold.it/1000x1000" alt="brand 2">
                        <h2 class="brand__name">Lorem ipsum</h2>
                    </div>

                    <div class="brands__list__item">
                        <img src="https://placehold.it/1000x1000" alt="brand 3">
                        <h2 class="brand__name">Lorem ipsum</h2>
                    </div>

                    <div class="brands__list__item">
                        <img src="https://placehold.it/1000x1000" alt="brand 4">
                        <h2 class="brand__name">Lorem ipsum</h2>
                    </div>

                    <div class="brands__list__item">
                        <img src="https://placehold.it/1000x1000" alt="brand 5">
                        <h2 class="brand__name">Lorem ipsum</h2>
                    </div>

{{--                    @foreach($shop_users->items() as $shop_user)--}}
{{--                        <div class="stores__list__item">--}}
{{--                            <div class="stores__list__item-logo">--}}
{{--                                <img src="{{ $shop_user->image ?? asset('assets/front/img/placeholder.png') }}" alt="">--}}
{{--                            </div>--}}
{{--                            <div class="stores__list__item-content">--}}
{{--                                <div class="stores__list__item-name">{{ $shop_user->username }}</div>--}}
{{--                                <p>{{ \Illuminate\Support\Str::words($shop_user->info, 29) }}</p>--}}
{{--                                <a title="Продавец {{ $shop_user->username }} на сайте addnew.biz" href="{{ route('author', $shop_user->id) }}" class="btn btn-success">Перейти к товарам</a>--}}
{{--                            </div>--}}
{{--                            <div class="stores__list__item-offers">--}}
{{--                                <div class="offer__count">{{ $shop_user->ads_count }}</div>--}}
{{--                                <div class="offer_text">Предложений</div>--}}
{{--                            </div>--}}
{{--                        </div>--}}

{{--                        @if($loop->iteration % 6 == 0)--}}
{{--                            <div class="banner">--}}
{{--                                @include('front.adsense.top')--}}
{{--                            </div>--}}

{{--                        @endif--}}
{{--                    @endforeach--}}

{{--                    @if($meta['description'])--}}
{{--                        <div class="show-more">--}}
{{--                            <section class="show-more__text">--}}
{{--                                {!! $meta['description']  !!}--}}
{{--                            </section>--}}
{{--                            <div class="show-more__shadow"></div>--}}
{{--                            <span class="show-more__btn btn-show">Показать</span>--}}
{{--                        </div>--}}
{{--                    @endif--}}

                </div>
{{--                {{ $shop_users->links('front.widgets.paginate') }}--}}
            </div>



        </div>
    </main>
@endsection