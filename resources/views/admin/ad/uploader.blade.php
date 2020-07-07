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
                    <form action="{{ route('admin.ad.uploader.init') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" name="file_url" class="form-control" placeholder="Ссылка на файл">
                            </div>
                            <div class="col-md-3">
                                <input type="text" id="username" class="form-control" placeholder="Выбрать пользователя">
                                <input type="hidden" name="user_id" id="user_id">
                            </div>
                            <div class="col-md-2">
                                <input type="text" id="cityname" class="form-control" placeholder="Выбрать город">
                                <input type="hidden" name="city_id" id="city_id">
                            </div>
                            <div class="col-md-1">
                                <select name="delimiter" class="form-control" id="">
                                    <option value=",">,</option>
                                    <option value="|">|</option>
                                    <option value="^">^</option>
                                </select>
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
                    <form action="#" method="post" id="uploader-form">
                    @csrf
                        <table class="table table-bordered table-hover">
                            <tr>
                                <td class="text-center" style="max-width: 30px">
                                    <a href="#" onclick="uploader.checkAll(); return false;">All</a>
                                </th>

                                <th><b>Название</b></th>
                                <th><b>Описание</b></th>
                                <th><b>Цена</b></th>
                            </tr>
                            @foreach($ads->items() as $item)
                                <tr onclick="uploader.check(this)">
                                    <td class="text-center" style="width: 40px;">
                                        <input type="checkbox" name="products[{{ $item->id }}]" value="{{ $item->id }}" class="form-check" style="margin: 0 auto">
                                    </td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ \Illuminate\Support\Str::words($item->content, 20) }}</td>
                                    <td>{{ $item->price }} {{ $item->currency->code }}</td>
                                </tr>
                            @endforeach

                        </table>
                    </form>

                    <div class="row">
                        <div class="col-md-6">
                            <button onclick="jQuery('#category_modal').modal('show'); return false;" class="btn btn-success">Выбрать категории и опубликовать</button>
                            <button onclick="uploader.remove_checked('{{ route('admin.ad.uploader.delete') }}')" class="btn btn-danger">Удалить</button>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="d-inline-block">
                                {{ $ads->links() }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <div id="category_modal" class="modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div style="margin: 15px">
                    <div class="form-group">
                        <label>В какую категорию будем публиковать? <span class="star">*</span></label>
                        <input type="text" name="category_name" id="ad_category" class="form-control">
                        <input type="hidden" name="category_id" id="ad_category_id" class="form-control">
                    </div>
                    <p class="error-holder"></p>
                    <button class="btn btn-subscribe btn-subscribe-trigger" onclick="uploader.publish('{{ route('admin.ad.uploader.publish') }}')">Опубликовать</button>
                </div>
            </div>
        </div>

    </div>
@endsection