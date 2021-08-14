@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Настройки SEO описаний</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Главная</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">SEO</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <form action="{{ action('Admin\Seo\Seo@update', ['index' => 'ad-category']) }}" method="post">
        @csrf
        <div class="row">
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
                        <p><span class="text-danger" style="background: #eee">---category_name---</span> <span class="float-right">Категория</span></p>
                        <p><span class="text-danger" style="background: #eee">---parent_name---</span> <span class="float-right">Родительская</span></p>
                        <button class="btn btn-success btn-block">Сохранить</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection