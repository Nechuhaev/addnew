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
    <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <form action="" class="category-form">


                        <div class="form-group">
                            <label>Родительская категория</label>
                            <div>
                                <select name="" class="form-control" id="">
                                    <option value="0">У этой категории нет родителей :(</option>
                                    <option value="1">Категория 1</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Название категории</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       value=""
                                       placeholder="noobmaster69"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Slug</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       value=""
                                       placeholder="noobmaster69"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Описание категории</label>
                            <div>
                                <textarea name="excerpt" rows="5"
                                          class="form-control form-control-line"></textarea>

                            </div>
                        </div>

                        <div class="form-group">
                            <label>Meta-тег title</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       value=""
                                       placeholder="noobmaster69"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Meta-тег description</label>
                            <div>
                                <textarea name="excerpt" rows="5"
                                          class="form-control form-control-line"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Порядок сортировки</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       value=""
                                       placeholder="noobmaster69"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Изображение</label>
                            <div>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary select-image">
                                        <i class="fa fa-picture-o"></i> Выбрать
                                        </a>
                                    </span>
                                    <input id="thumbnail" value="" class="form-control" type="text" name="image">
                                </div>
                                <img id="holder" class="img-fluid" style="margin-top: 20px" src="http://placehold.it/400x250">
                            </div>
                        </div>
                        <hr>
                        <div class="form-group text-center">
                            <button class="btn btn-success">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-8">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover table-middle-cell">
                        <tr>
                            <th>Город</th>
                            <th>Регион</th>
                            <th>Страна</th>
                            <th></th>
                        </tr>

                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                        <tr>
                            <td>Город</td>
                            <td>Регион</td>
                            <td>Страна</td>
                            <td class="text-center cell-actions">
                                <a href="#"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection