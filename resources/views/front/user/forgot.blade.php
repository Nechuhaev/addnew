@extends('front.layout')

@section('meta_title', "Восстановление пароля | Доска объявлений addnew.biz")
@section('meta_description', "Восстановление пароля | Доска объявлений addnew.biz")

@section('content')
    <main class="login-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Восстановление пароля</span></li>
            </ul>

            <h1>{{ __('user/forgot.heading') }}</h1>

            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>{{ __('user/forgot.info') }}</p>
                            </div>
                        </div>
                    </div>
                </aside>
                <div class="column-content">

                    <div class="notice-wrap visible-xs">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>{{ __('user/forgot.info') }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="form-login" action="{{ route('password.email') }}">
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

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('user/forgot.email') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('passwords.action_reset') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>



            </div> <!-- column-content -->
        </div> <!-- columns -->


        </div> <!-- container -->
    </main>
@endsection