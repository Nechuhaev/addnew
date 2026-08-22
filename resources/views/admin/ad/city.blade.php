@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $city->name ?? 'Города' }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.adCities') }}
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
                        @if ($city)
                            <input type="hidden" name="city_id" value="{{ $city->id }}">
                        @endif
                        <div class="form-group">
                            <label>Страна</label>
                            <div>
                                <select name="country_id" onchange="city.loadRegions(this);" class="form-control" id="">
                                    <option value="0">Страна не выбрана</option>
                                    @if($countries)
                                        @foreach($countries as $country)
                                            @if($city && $city->region->country->id && $city->region->country->id == $country->id)
                                                <option selected value="{{ $country->id }}">{{ $country->name }}</option>
                                            @else
                                                <option value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Область</label>
                            <div>
                                <select name="region_id" onchange="" class="form-control" id="">
                                    @if($regions && $city)
                                        <option value="0">Выберите область</option>
                                        @foreach($regions as $region)
                                            @if($region->id == $city->region->id)
                                                <option selected value="{{ $region->id }}">{{ $region->name }}</option>
                                            @else
                                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                                            @endif
                                        @endforeach
                                    @else
                                    <option value="0">Сначала выберите страну</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Город (RU)</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? optional($city)->getOriginal('name') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name_uk" style="color:#2e7d32;">Місто (UK)</label>
                            <div>
                                <input type="text"
                                       name="name_uk"
                                       id="name_uk"
                                       value="{{ old('name_uk') ?? optional($city)->getOriginal('name_uk') ?? '' }}"
                                       placeholder="Якщо порожньо — покаже RU-версію"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <div>
                                <input type="text"
                                       name="slug"
                                       id="slug"
                                       value="{{ old('slug') ?? $city->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content">Описание для города (RU)</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? optional($city)->getOriginal('content') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content_uk" style="color:#2e7d32;">Опис міста (UK)</label>
                            <div>
                                <textarea name="content_uk"
                                          id="content_uk"
                                          class="content form-control form-control-line">{{ old('content_uk') ?? optional($city)->getOriginal('content_uk') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_title">Meta-тег title (RU)</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? optional($city)->getOriginal('meta_title') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_title_uk" style="color:#2e7d32;">Meta-тег title (UK)</label>
                            <div>
                                <input type="text"
                                       name="meta_title_uk"
                                       id="meta_title_uk"
                                       value="{{ old('meta_title_uk') ?? optional($city)->getOriginal('meta_title_uk') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_description">Meta-тег description (RU)</label>
                            <div>
                                <textarea name="meta_description"
                                          rows="5"
                                          id="meta_description"
                                          class="form-control form-control-line">{{ old('meta_description') ?? optional($city)->getOriginal('meta_description') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_description_uk" style="color:#2e7d32;">Meta-тег description (UK)</label>
                            <div>
                                <textarea name="meta_description_uk"
                                          rows="5"
                                          id="meta_description_uk"
                                          class="form-control form-control-line">{{ old('meta_description_uk') ?? optional($city)->getOriginal('meta_description_uk') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Порядок сортировки</label>
                            <div>
                                <input type="text"
                                       name="sort_order"
                                       id="sort_order"
                                       value="{{ old('sort_order') ?? $city->sort_order ?? '' }}"
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
                            <div class="col-md-7">
                                <input type="text"
                                       name="name"
                                       value="{{ $requested_city ?? "" }}"
                                       placeholder="Поиск городов"
                                       class="form-control form-control-line">
                            </div>
                            <div class="col-md-3"><button class="btn btn-primary btn-block">Найти город</button></div>
                            <div class="col-2">
                                <a id="deleteMany" href="/admin/cities/deleteMany" class="btn btn-danger btn-block">Удалить</a>
                            </div>
                        </div>
                    </form>
                    <hr>
                    @if ($cities->items())
                        <table class="table table-bordered table-hover table-middle-cell">
                            <tr>
                                <th></th>
                                <th class="text-center">ID</th>
                                <th>Город</th>
                                <th>Регион</th>
                                <th>Страна</th>
                                <th><a style="color: black" href="?order=ads_count&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">Кол-во</a> {!! ($order == 'ads_count') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-down"></i>' : '<i class="mdi mdi-arrow-up"></i>' : '';   !!}</th>
                                <th></th>
                            </tr>
                            @foreach($cities as $city_item)
                                <tr>
                                    <td style="width: 30px"><input type="checkbox" name="id[]" value="{{ $city_item->id }}"></td>
                                    <td class="text-center">{{ $city_item->id }}</td>
                                    <td><b>{{ $city_item->name }}</b></td>
                                    <td>{{ $city_item->region->name }}</td>
                                    <td>{{ $city_item->region->country->name }}</td>
                                    <td>{{ $city_item->ads_count }}</td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adCities.edit', ['id' => $city_item->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adCities.delete', ['id' => $city_item->id]) }}" onclick="return confirm('Вы пытаетесь удалить город {{ $city_item->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        {{ $cities->appends($_GET)->links() }}
                    @else
                        <p>Города не найдены</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection