@extends('front.layout')
@section('meta_title', "Изменить пароль")
@section('meta_description', "Изменить пароль учетной записи.")
@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render('profile.password') }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('profile.password_heading') }}</h1>
                    @if(session()->has('success'))
                        <div class="alert success">
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form class="form-account" action="{{ $action }}" method="post">
                        @csrf
                        <div class="columns">
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="new_password">{{ __('profile.new_password_label') }}</label>
                                    <input type="password"
                                           id="new_password"
                                           name="new_password"
                                           value=""
                                           class="form-control">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label for="new_password_confirmation">{{ __('profile.confirm_password_label') }}</label>
                                    <input type="password"
                                           id="new_password_confirmation"
                                           name="new_password_confirmation"
                                           value=""
                                           class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-action">
                            <input type="submit" class="btn btn-edit-profile" value="{{ __('profile.password_heading') }}">
                        </div>
                    </form>
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection