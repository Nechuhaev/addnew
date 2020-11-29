@extends('front.layout')

@section('meta_title', "Регистрация бизнес-пользователя | Доска объявлений addnew.biz")
@section('meta_description', "Регистрация бизнес-пользователя | Доска объявлений addnew.biz")

@section('content')
    <main class="login-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Регистрация бизнес-пользователя</span></li>
            </ul>

            <h1>Регистрация бизнес-пользователя</h1>

            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>Регистрация бизнес-пользователя</p>
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

                    <form class="form-registration" action="{{ route('post-business-register') }}" method="POST">
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

                        <div class="form-group">
                            <label>Придумайте пароль</label>
                            <input type="password" name="password" value="" class="form-control required" aria-required="true">
                        </div>

                        <div class="form-group">
                            <label>Повторите пароль</label>
                            <input type="password" name="password_confirmation" value="" class="form-control required" aria-required="true">
                        </div>

                        <div class="form-group">
                            <label>Название магазина (не обязательно)</label>
                            <input type="text" name="shop_name" value="{{ old('shop_name') }}" class="form-control required" aria-required="true">
                        </div>

                        <div class="form-group">
                            <label>Ссылка на интернет-магазин (не обязательно)</label>
                            <input type="text" name="shop_url" value="{{ old('shop_url') }}" class="form-control required" aria-required="true">
                        </div>

                        <div class="form-action">
                            <button name="submit" type="submit" class="btn-form">
                                <span class="btn-text">{{ __('user/register.button') }}</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div> <!-- column-content -->
        </div> <!-- columns -->

        </div> <!-- container -->
    </main>
@endsection