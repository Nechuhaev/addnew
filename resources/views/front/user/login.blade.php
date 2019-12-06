@extends('front.layout')

@section('content')
    <main class="login-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Вход</span></li>
            </ul>

            <h1>{{ __('user/login.heading') }}</h1>

            @if(session()->has('success'))
                <div class="alert alert-success">
                    {!! session()->get('success')  !!}
                </div>
            @endif

            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div><p>{{ __('user/login.info') }}</p></div>
                        </div>
                    </div>
                </aside>
                <div class="column-content">

                    <div class="notice-wrap visible-xs">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>{{ __('user/login.info') }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="form-login" action="{{ route('login') }}">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="padding: 0 0 0 10px;margin: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @csrf
                        <div class="form-group">
                            <label>{{ __('user/login.email') }}</label>
                            <input type="email"
                                   value="{{ old('email') }}"
                                   name="email"
                                   class="form-control required"
                                   autocomplete="email" autofocus>
                        </div>

                        <div class="form-group">
                            <label>{{ __('user/login.password') }}</label>
                            <input id="password" type="password" class="form-control required" name="password" required autocomplete="current-password">
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <span class="check-option">{{ __('user/login.remember') }}</span></label>
                            </div>
                        </div>

                        <div class="form-action">
                            <button name="submit" type="submit" class="btn-form">
                                <span class="btn-text">{{ __('user/login.button_enter') }}</span>
                            </button>
                        </div>
                        <div class="form-group">
                            <label>{{ __('user/login.enter_with_social') }}:</label>
                            <div class="form-social">
                                <a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/vk.svg') }}" /></a>
                                <a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/facebook-alt.svg') }}" /></a>
                                <a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/twitter.svg') }}" /></a>
                                <a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/at.svg') }}" /></a>
                                <a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/google-plus.svg') }}" /></a>
                                <a href="#" class="form-social-link"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/social/ok.svg') }}" /></a>
                            </div>

                        </div>
                        <a href="{{ route('password.request') }}">{{ __('user/login.text_forgot_password') }}</a><br>
                        <a href="{{ route('register') }}">{{ __('user/login.text_register') }}</a>
                    </form>
                </div> <!-- column-content -->
            </div> <!-- columns -->
        </div> <!-- container -->
    </main>
@endsection