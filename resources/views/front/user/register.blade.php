@extends('front.layout')

@section('meta_title', "Регистрация пользователя | Доска объявлений addnew.biz")
@section('meta_description', "Регистрация пользователя | Доска объявлений addnew.biz")

@section('content')
    <main class="login-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Регистрация</span></li>
            </ul>

            <h1>{{ __('user/register.heading') }}</h1>

            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>{{ __('user/register.info') }}</p>
                            </div>
                        </div>
                    </div>
                </aside>
                <div class="column-content">
                    <div class="notice-wrap visible-xs">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>{{ __('user/register.info_br') }}</p>
                            </div>
                        </div>
                    </div>

                    <form class="form-registration" action="{{ route('register') }}" method="POST">
                        @csrf

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="padding: 0 0 0 10px;margin: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="form-group">
                            <label>{{ __('user/register.email') }}</label>
                            <input type="text" name="email" value="{{ old('email') }}" class="form-control required" aria-required="true">
                        </div>

                        <div class="form-action">
                            <button name="submit" type="submit" class="btn-form">
                                <span class="btn-text">{{ __('user/register.button') }}</span>
                            </button>
                        </div>

                        <div class="form-group">
                            <h2>Для интернет-магазинов</h2>
                            <label for="#">Желаете опубликовать и управлять товарами имеющегося интернет-магазина?</label>
                            <p style="font-size: 12px;">
                                Восспользуйтесь кнопкой регистрации интернет магазина для публикации своих товаров на addnew.biz.
                                <br>
                                Регистрация не займет много времени, а для добавления товаров достаточно предоставить список товаров <b>в любом формате</b>
                            </p>
                            <a href="{{ route('business-register') }}">Регистрация интернет-магазина</a>
                        </div>
                        <div class="form-group">
                            <label>{{ __('user/register.text_social_login') }}</label>
                            @include('front.widgets.social')
                            {{--<div class="form-social">--}}
                                {{--<a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/vk.svg') }}" /></a>--}}
                                {{--<a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/facebook-alt.svg') }}" /></a>--}}
                                {{--<a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/twitter.svg') }}" /></a>--}}
                                {{--<a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/at.svg') }}" /></a>--}}
                                {{--<a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/google-plus.svg') }}" /></a>--}}
                                {{--<a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/ok.svg') }}" /></a>--}}
                            {{--</div>--}}

                        </div>
                    </form>
                </div>

            </div> <!-- column-content -->
        </div> <!-- columns -->

        </div> <!-- container -->
    </main>
@endsection