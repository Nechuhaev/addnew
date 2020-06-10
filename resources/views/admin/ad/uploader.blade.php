@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Загрузка из файла</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.adTags') }}
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
                    <form action="#">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="file" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Выбрать пользователя">
                            </div>
                            <div class="col-md-3">
                                <input type="text" class="form-control" placeholder="Выбрать город">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-outline-success btn-block">Загрузить</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <td class="text-center" style="max-width: 30px">
                                <a href="#">All</a>
                            </th>
                            <th><b>Цена</b></th>
                            <th><b>Город</b></th>
                            <th><b>Название</b></th>
                            <th><b>Описание</b></th>
                        </tr>
                        <tr>
                            <td class="text-center" style="max-width: 30px">
                                <input type="checkbox" class="form-check" style="margin: 0 auto">
                            </td>
                            <td>Цена</td>
                            <td>Город</td>
                            <td>Название</td>
                            <td>Описание</td>
                        </tr>
                        <tr>
                            <td class="text-center" style="max-width: 30px">
                                <input type="checkbox" class="form-check" style="margin: 0 auto">
                            </td>
                            <td>Цена</td>
                            <td>Город</td>
                            <td>Название</td>
                            <td>Описание</td>
                        </tr>
                        <tr>
                            <td class="text-center" style="max-width: 30px">
                                <input type="checkbox" class="form-check" style="margin: 0 auto">
                            </td>
                            <td>Цена</td>
                            <td>Город</td>
                            <td>Название</td>
                            <td>Описание</td>
                        </tr>
                    </table>

                    <div class="row">
                        <div class="col-md-6">
                            <button class="btn btn-success">Выбрать категории и опубликовать</button>
                            <button class="btn btn-danger">Удалить</button>
                        </div>
                        <div class="col-md-6 text-right">
                            Здесь будет пагинация

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection