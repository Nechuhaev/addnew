@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Пользователь {{ $user->username }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.user', $user) }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <form class="form-horizontal form-material" method="POST" action="{{ route('admin.user.update') }}" enctype="multipart/form-data">
        @csrf
    <div class="row">
        <!-- Column -->
        <div class="col-lg-4 col-xlg-3 col-md-5">
            <div class="card">


                <div class="card-body">
                    <div class="form-group">
                        <label>Изображение</label>
                        <div>
                            <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary select-image">
                                        <i class="fa fa-picture-o"></i> Выбрать
                                        </a>
                                    </span>
                                <input id="thumbnail" value="{{ old('image') ?? $user->image ?? '' }}" class="form-control" type="text" name="image">
                            </div>

                        </div>
                    </div>
                    <center class="m-t-30">
                        <div class="">
                            <div class="inner">
                                <img id="holder" class="img-fluid" style="margin-top: 20px" src="{{ old('image') ?? $user->image ?? asset('assets/front/img/placeholder.png') }}">
                            </div>
                        </div>
                        <h4 class="card-title m-t-10">{{ $user->username}}</h4>
                        <h6 class="card-subtitle">{{ $user->email }}</h6>
                    </center>
                </div>
                <div>
                    <hr> </div>
                <div class="card-body">
                    <small class="text-muted">Email</small>
                    <h6>{{ $user->email }}</h6>
                    <small class="text-muted p-t-30 db">Телефон</small>
                    <h6>{{ $user->telephone }}</h6>
                    <small class="text-muted p-t-30 db">Количество объявлений</small>
                    <h6>6</h6>
                    <small class="text-muted p-t-30 db">Дата регистрации</small>
                    <h6>{{ $user->created_date }}</h6>
                </div>
            </div>
        </div>
        <!-- Column -->
        <!-- Column -->
        <div class="col-lg-8 col-xlg-9 col-md-7">
            <div class="card">
                <div class="card-body">
                    @if($user->id)
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                    @endif
                    <div class="form-group">
                        <label for="email" class="col-md-12">Email</label>
                        <div class="col-md-12">
                            <input type="email"
                                   name="email"
                                   id="email"
                                   placeholder="johnathan@admin.com"
                                   disabled
                                   value="{{ $user->email }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="firstname" class="col-md-12">Имя</label>
                        <div class="col-md-12">
                            <input type="text"
                                   name="firstname"
                                   id="firstname"
                                   placeholder="Johnathan"
                                   value="{{ old('firstname') ?? $user->firstname }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="col-md-12">Фамилия</label>
                        <div class="col-md-12">
                            <input type="text"
                                   id="lastname"
                                   name="lastname"
                                   placeholder="Doe"
                                   value="{{ old('lastname') ?? $user->lastname }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="telephone" class="col-md-12">Номер телефона</label>
                        <div class="col-md-12">
                            <input type="text"
                                   id="telephone"
                                   name="telephone"
                                   placeholder="+38"
                                   value="{{ old('telephone') ?? $user->telephone }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="site_url" class="col-md-12">Ссылка на сайт</label>
                        <div class="col-md-12">
                            <input type="text"
                                   id="site_url"
                                   name="site_url"
                                   placeholder="https://..."
                                   value="{{ old('site_url') ?? $user->site_url }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="facebook_url" class="col-md-12">Facebook ссылка</label>
                        <div class="col-md-12">
                            <input type="text"
                                   id="facebook_url"
                                   name="facebook_url"
                                   placeholder="https://facebook.com/..."
                                   value="{{ old('facebook_url') ?? $user->facebook_url }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="twitter_url" class="col-md-12">Twitter ссылка</label>
                        <div class="col-md-12">
                            <input type="text"
                                   id="twitter_url"
                                   name="twitter_url"
                                   placeholder="https://twitter.com/..."
                                   value="{{ old('twitter_url') ?? $user->twitter_url }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-md-12">Пароль</label>
                        <div class="col-md-12">
                            <input type="password"
                                   value=""
                                   id="password"
                                   name="password"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation" class="col-md-12">Повторить пароль</label>
                        <div class="col-md-12">
                            <input type="password"
                                   value=""
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="info" class="col-md-12">Дополнительная информация</label>
                        <div class="col-md-12">
                            <textarea rows="5"
                                      id="info"
                                      name="info"
                                      class="form-control form-control-line">{{ old('info') ?? $user->info }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label id="is_admin" class="col-md-12">Роль</label>
                        <div class="col-md-12">
                            <select name="is_admin" class="form-control" id="is_admin">
                                <option value="0" {{ ($user->is_admin == 0) ? 'selected' : '' }}>Пользователь</option>
                                <option value="1" {{ ($user->is_admin == 1) ? 'selected' : '' }}>Администратор</option>
                            </select>
                        </div>
                    </div>
                        <div class="form-group">
                        <label id="is_admin" class="col-md-12">Тип профиля</label>
                        <div class="col-md-12">
                            <select name="is_admin" class="form-control" id="is_shop_owner">
                                <option value="0" {{ ($user->is_shop_owner == 0) ? 'selected' : '' }}>Физ. лицо</option>
                                <option value="1" {{ ($user->is_shop_owner == 1) ? 'selected' : '' }}>Интернет-магазин</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12">
                            <button class="btn btn-success">Обновить профиль пользователя</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Column -->
        </div>
    </form>
@endsection