@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Настройки SEO описаний / Объявление</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.seo') }}">Вернуться</a>
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ action('Admin\Seo\Seo@update', ['index' => 'ad']) }}" method="post">
        <div class="row">
            @csrf
            <div class="col-8">
                <div class="card">
                    <div class="card-body">
                        @include('admin.form-widgets.seo-form')
                    </div>
                </div>
            </div>

            <div class="col-4">
                <div class="card">
                    <div class="card-body">
                        <p><span class="text-danger" style="background: #eee">---name---</span> <span class="float-right">Название объявления</span></p>
                        <p><span class="text-danger" style="background: #eee">---price---</span> <span class="float-right">Цена</span></p>
                        <p><span class="text-danger" style="background: #eee">---city_name---</span> <span class="float-right">Город</span></p>
                        <p><span class="text-danger" style="background: #eee">---region_name---</span> <span class="float-right">Область</span></p>
                        <p><span class="text-danger" style="background: #eee">---country_name---</span> <span class="float-right">Страна</span></p>
                        <p><span class="text-danger" style="background: #eee">---user_name---</span> <span class="float-right">Имя пользователя</span></p>
                        <p><span class="text-danger" style="background: #eee">---user_description---</span> <span class="float-right">Описание от юзера</span></p>
                        <p><span class="text-danger" style="background: #eee">---user_email---</span> <span class="float-right">Email пользователя</span></p>
                        <p><span class="text-danger" style="background: #eee">---user_telephone---</span> <span class="float-right">Телефон</span></p>
                        <p><span class="text-danger" style="background: #eee">---created_at---</span> <span class="float-right">Дата создания</span></p>
                        <p><span class="text-danger" style="background: #eee">---updated_at---</span> <span class="float-right">Дата обновления</span></p>
                        <button class="btn btn-success btn-block">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection