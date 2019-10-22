@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Dashboard</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="setting-groups">
        <div class="row">
            <div class="col-12">
                <h2>Блог</h2>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        <a href="#">Категория</a>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tr>
                                <td>Title</td>
                                <td>Не задано</td>
                            </tr>
                            <tr>
                                <td>Meta title</td>
                                <td>Не задано</td>
                            </tr>
                            <tr>
                                <td>Meta description</td>
                                <td>Не задано</td>
                            </tr>
                            <tr>
                                <td>Описание</td>
                                <td>Не задано</td>
                            </tr>
                            <tr>
                                <td>Рекламные блоки</td>
                                <td>2 / 3</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-header">
                        Статья
                    </div>
                    <div class="card-body">
                        Эта страница еще в разработке. Проявим немного терпения и накопим денег для того, чтобы расплатиться с разработчиком.
                    </div>
                </div>
            </div>
        </div>


    </div>

@endsection