@extends('front.layout')

@section('meta_title', "Импорт товаров | Доска объявлений addnew.biz")
@section('meta_description', "Импорт товаров | Доска объявлений addnew.biz")

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.import') }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>Импорт товаров</h1>

                    @if(session()->has('success'))
                        <div class="alert success">
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert">
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('error') }}
                        </div>
                    @endif

                    <div class="block-unready">
                        <p>Данный раздел находится в разработке. Скоро вы сможете загружать прайс-листы и CSV-файлы для массового импорта товаров.</p>
                    </div>

                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection
