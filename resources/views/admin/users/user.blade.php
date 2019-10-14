@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Пользователь {{ $user->fullname }}</h4>
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
    <div class="row">
        <!-- Column -->
        <div class="col-lg-4 col-xlg-3 col-md-5">
            <div class="card">
                <div class="card-body">
                    <center class="m-t-30">
                        <div class="user-avatar large-avatar" style="{{ $user->avatar_gradient }}">
                            <div class="inner">
                                {{ $user->avatar_text }}
                            </div>
                        </div>
                        <h4 class="card-title m-t-10">{{ $user->fullname }}</h4>
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
                    <form class="form-horizontal form-material">
                        <div class="form-group">
                            <label class="col-md-12">Имя пользователя</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="noobmaster69"
                                       value="{{ $user->username }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Имя</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="Johnathan"
                                       value="{{ $user->firstname }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Фамилия</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="Doe"
                                       value="{{ $user->lastname }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="example-email" class="col-md-12">Email</label>
                            <div class="col-md-12">
                                <input type="email"
                                       placeholder="johnathan@admin.com"
                                       value="{{ $user->email }}"
                                       class="form-control form-control-line" name="example-email" id="example-email">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Номер телефона</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="+38 (093) 2888 288"
                                       value="{{ $user->telephone }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Ссылка на сайт</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="+38 (093) 2888 288"
                                       value="{{ $user->site_url }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Facebook ссылка</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="+38 (093) 2888 288"
                                       value="{{ $user->facebook_url }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Twitter ссылка</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder="+38 (093) 2888 288"
                                       value="{{ $user->twitter_url }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Пароль</label>
                            <div class="col-md-12">
                                <input type="password"
                                       value=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Повторить пароль</label>
                            <div class="col-md-12">
                                <input type="password"
                                       value=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Дополнительная информация</label>
                            <div class="col-md-12">
                                <textarea rows="5"
                                          class="form-control form-control-line">{{ $user->info }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button class="btn btn-success">Обновить профиль пользователя</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Column -->
    </div>
@endsection