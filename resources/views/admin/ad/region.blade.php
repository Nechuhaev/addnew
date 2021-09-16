@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $region->name ?? 'Области / Регионы' }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.adRegions') }}
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
                        @if ($region)
                            <input type="hidden" name="region_id" value="{{ $region->id }}">
                        @endif

                        <div class="form-group">
                            <label>Страна</label>
                            <div>
                                <select name="country_id" class="form-control" id="country_id">
                                    <option value="0">Выберите страну</option>
                                    @if($countries)
                                        @foreach($countries as $country)
                                            @if (old('parent_id'))
                                                <option {{ (old('country_id') == $country['id']) ? 'selected' : '' }} value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                                            @elseif (isset($region->country_id))
                                                <option {{ ($region->country_id == $country['id']) ? 'selected' : '' }} value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                                            @else
                                                <option value="{{ $country['id'] }}">{{ $country['name'] }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Область</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? $region->name ?? '' }}"
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
                                       value="{{ old('slug') ?? $region->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="content">Описание для области</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? $region->content ?? '' }}</textarea>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_title">Meta-тег title</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? $region->meta_title ?? '' }}"
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
                                          class="form-control form-control-line">{{ old('meta_description') ?? $region->meta_description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Порядок сортировки</label>
                            <div>
                                <input type="text"
                                       name="sort_order"
                                       id="sort_order"
                                       value="{{ old('sort_order') ?? $region->sort_order ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
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
                                       value="{{ $requested_region ?? "" }}"
                                       placeholder="Поиск областей"
                                       class="form-control form-control-line">
                            </div>
                            <div class="col-md-4"><button class="btn btn-primary btn-block">Найти область</button></div>
                        </div>
                    </form>

                    <hr>

                    @if ($regions)
                    <table class="table table-bordered table-hover table-middle-cell">
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Область (Регион)</th>
                            <th>Страна</th>
                            <th style="max-width: 80px" class="text-center">Городов</th>
                            <th style="max-width: 80px" class="text-center"><a style="color: black" href="?order=ads_count&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">Кол-во</a> {!! ($order == 'ads_count') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-down"></i>' : '<i class="mdi mdi-arrow-up"></i>' : '';   !!}</th>
                            <th class="text-center cell-actions">
                            </th>
                        </tr>

                        @foreach($regions as $region_item)
                            <tr>
                                <td class="text-center">{{ $region_item->id }}</td>
                                <td><b>{{ $region_item->name }}</b></td>
                                <td>{{ $region_item->country->name }}</td>
                                <td style="max-width: 80px" class="text-center">{{ $region_item->cities->count() }}</td>
                                <td style="max-width: 80px" class="text-center">{{ $region_item->ads_count }}</td>
                                <td class="text-center cell-actions">
                                    <a href="{{ route('admin.adRegions.edit', ['id' => $region_item->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                    <a href="{{ route('admin.adRegions.delete', ['id' => $region_item->id]) }}" onclick="return confirm('Вы пытаетесь удалить регион {{ $region_item->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                    {{ $regions->links() }}
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection