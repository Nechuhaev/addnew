@extends('front.layout')

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

            <h1>{{ __('user/reset.heading') }}</h1>

            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="notice-wrap">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>{{ __('user/reset.info') }}</p>
                            </div>
                        </div>
                    </div>
                </aside>
                <div class="column-content">

                    <div class="notice-wrap visible-xs">
                        <div class="notice">
                            <i class="icon icon-lock"></i>
                            <div>
                                <p>{{ __('user/reset.info') }}</p>
                            </div>
                        </div>
                    </div>

                    <form method="POST" class="form-login" action="{{ route('password.update') }}">
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

                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('user/reset.email') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>

                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('user/reset.password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password">

                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('user/reset.repeat_password') }}</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('user/reset.save_password') }}
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