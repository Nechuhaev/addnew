@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Список зарегистрированных пользователей</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.users') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">


            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-1">
                            <img src="http://placehold.it/200x150" class="img-fluid" alt="">
                        </div>
                        <div class="col-3">
                            <div><small class="text-muted">Название статьи</small></div>
                            Правильно ли Вы колите орехи?
                        </div>
                        <div class="col-3">
                            <div><small class="text-muted">Категории</small></div>
                            <a href="#">Категория 1</a>, <a href="#">Категория 2</a>, <a href="#">Категория 3</a>
                        </div>
                        <div class="col-2">
                            <div><small class="text-muted">Дата публикации</small></div>
                            20.10.2019 20:13
                        </div>
                        <div class="col-2">
                            <div><small class="text-muted">SEO</small></div>
                            Шаблон
                        </div>
                        <div class="col-1 text-right">
                            <a href="{{ route('admin.article') }}"><i class="mdi mdi-24px mdi-account-edit"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-1">
                            <img src="http://placehold.it/200x150" class="img-fluid" alt="">
                        </div>
                        <div class="col-3">
                            <div><small class="text-muted">Название статьи</small></div>
                            Правильно ли Вы колите орехи?
                        </div>
                        <div class="col-3">
                            <div><small class="text-muted">Категории</small></div>
                            <a href="#">Категория 1</a>, <a href="#">Категория 2</a>, <a href="#">Категория 3</a>
                        </div>
                        <div class="col-2">
                            <div><small class="text-muted">Дата публикации</small></div>
                            20.10.2019 20:13
                        </div>
                        <div class="col-2">
                            <div><small class="text-muted">SEO title</small></div>
                            Сколько орехов Вы раскололи?
                            <div><small class="text-muted">SEO description</small></div>
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit...
                        </div>
                        <div class="col-1 text-right">
                            <a href="{{ route('admin.article') }}"><i class="mdi mdi-24px mdi-account-edit"></i></a>
                        </div>
                    </div>
                </div>
            </div>




        </div>
    </div>
@endsection