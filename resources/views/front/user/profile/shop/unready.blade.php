@extends('front.layout')
@section('meta_title', "Страница в разработке | Доска объявлений addnew.biz")
@section('meta_description', "Страница в разработке | Доска объявлений addnew.biz")
@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render('profile.ads') }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('shop.unready_heading') }}</h1>
                    @if(session()->has('success'))
                        <div class="alert success"> <!-- success -->
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert"> <!-- success -->
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('error') }}
                        </div>
                    @endif
                    <div class="block-unready">
                        <p>{!! __('shop.unready_text') !!}
                            <a href="mailto:info@addnew.biz">info@addnew.biz</a></p>
                    </div>
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection