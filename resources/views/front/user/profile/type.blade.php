@extends('front.layout')
@section('meta_title', "Редактировать тип профиля")
@section('meta_description', "Редактировать тип профиля")
@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render('profile.edit') }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('profile.type_heading') }}</h1>
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
                    <div class="columns">
                        <div class="col-2">
                            <div class="profile-item">
                                <h2>{{ __('profile.individual_heading') }}</h2>
                                <p>{!! __('profile.individual_text') !!}</p>
                                <div class="form-action">
                                    <form action="{{ route('switch-profile-type') }}" method="post" class="profile-type-form">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="is_shop_owner" value="0">
                                        @is_shop_owner()
                                            <input type="submit" class="btn btn-edit-profile" value="{{ __('profile.switch_button') }}">
                                        @else
                                            <input type="submit" disabled class="btn btn-edit-profile disabled" value="{{ __('profile.current_profile_button') }}">
                                        @endis_shop_owner
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="profile-item">
                                <h2>{{ __('profile.shop_heading') }}</h2>
                                <p>{{ __('profile.shop_text') }}</p>
                                <div class="form-action">
                                    <form action="{{ route('switch-profile-type') }}" method="post" class="profile-type-form">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="is_shop_owner" value="1">
                                        @is_shop_owner()
                                            <input type="submit" disabled class="btn btn-edit-profile disabled" value="{{ __('profile.current_profile_button') }}">
                                        @else
                                            <input type="submit" class="btn btn-edit-profile" value="{{ __('profile.switch_button') }}">
                                        @endis_shop_owner
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection