@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $country->name ?? 'Страны' }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.adCountries') }}
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
                    <form action="{{ $action }}" method="POST" class="category-form">
                        @csrf
                        @if($country)
                            <input type="hidden" name="country_id" value="{{ $country->id }}">
                        @endif

                        <div class="form-group">
                            <label>Страна</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? $country->name ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <div>
                                <input type="text"
                                       name="slug"
                                       id="slug"
                                       value="{{ old('slug') ?? $country->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="content">Описание категории</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? $country->content ?? '' }}</textarea>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_title">Meta-тег title</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? $country->meta_title ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta-тег description</label>
                            <div>
                                <textarea name="meta_description"
                                          rows="5"
                                          id="meta_description"
                                          class="form-control form-control-line">{{ old('meta_description') ?? $country->meta_description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Порядок сортировки</label>
                            <div>
                                <input type="text"
                                       name="sort_order"
                                       id="sort_order"
                                       value="{{ old('sort_order') ?? $country->sort_order ?? '' }}"
                                       placeholder=""
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
                                    <input id="thumbnail" value="{{ old('image') ?? $country->image ?? '' }}" class="form-control" type="text" name="image">
                                </div>
                                <img id="holder" class="img-fluid" style="margin-top: 20px" src="{{ old('image') ?? $country->image ?? asset('assets/front/img/placeholder.png') }}">
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
                    <form action="{{ $action_search }}" method="GET">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text"
                                       name="name"
                                       value="{{ $requested_country ?? "" }}"
                                       placeholder="Поиск по странам"
                                       class="form-control form-control-line">
                            </div>
                            <div class="col-md-4"><button class="btn btn-primary btn-block">Искать</button></div>
                        </div>
                    </form>

                    <hr>

                    @if($countries->items())
                        <table class="table table-bordered table-hover table-middle-cell">
                            <tr>
                                <th class="text-center">ID</th>
                                <th>Страна</th>
                                <th class="text-center" style="max-width: 50px">Областей</th>
                                <th class="text-center" style="max-width: 50px">Городов</th>
                                <th></th>
                            </tr>
                            @foreach($countries as $country_item)
                                <tr>
                                    <td class="text-center">{{ $country_item->id }}</td>
                                    <td><b>{{ $country_item->name }}</b></td>
                                    <td class="text-center" style="max-width: 50px">{{ $country_item->regions->count() }}</td>
                                    <td class="text-center" style="max-width: 50px">{{ $country_item->total_cities }}</td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adCountries.edit', ['id' => $country_item->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adCountries.delete', ['id' => $country_item->id]) }}" onclick="return confirm('Вы пытаетесь удалить страну {{ $country_item->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        {{ $countries->links() }}
                    @else
                        <p>Стран не найдено</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection