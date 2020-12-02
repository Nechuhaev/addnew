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
                    <h1>Редактировать тип профиля</h1>

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
                                <h2>Физическое лицо</h2>
                                <p>Профиль физического лица позволяет Вам публиковать объявления в неограниченном количестве.
                                    <br>Все объявления бесплатны. Всегда бесплатны!
                                </p>

                                <div class="form-action">

                                    <form action="{{ route('switch-profile-type') }}" method="post" class="profile-type-form">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="is_shop_owner" value="0">
                                        @is_shop_owner()
                                            <input type="submit" class="btn btn-edit-profile" value="Перейти">
                                        @else
                                            <input type="submit" disabled class="btn btn-edit-profile disabled" value="Текущий профиль">
                                        @endis_shop_owner
                                    </form>
                                </div>
                            </div>

                        </div>
                        <div class="col-2">
                            <div class="profile-item">
                                <h2>Интернет-магазин</h2>
                                <p>Для владельцев интернет-магазинов мы предлагаем перейти на этот профиль. После перехода у Вас появится возможность массовой загрузки товаров для показа на addnew.biz</p>

                                <div class="form-action">
                                    <form action="{{ route('switch-profile-type') }}" method="post" class="profile-type-form">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="is_shop_owner" value="1">
                                        @is_shop_owner()
                                            <input type="submit" disabled class="btn btn-edit-profile disabled" value="Текущий профиль">
                                        @else
                                            <input type="submit" class="btn btn-edit-profile" value="Перейти">
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