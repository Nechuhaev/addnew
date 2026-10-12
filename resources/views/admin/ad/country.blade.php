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
                            <label>Страна (RU)</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? optional($country)->getOriginal('name') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name_uk" style="color:#2e7d32;">Країна (UK)</label>
                            <div>
                                <input type="text"
                                       name="name_uk"
                                       id="name_uk"
                                       value="{{ old('name_uk') ?? optional($country)->getOriginal('name_uk') ?? '' }}"
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
                                       value="{{ old('slug') ?? $country->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content">Описание категории (RU)</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? optional($country)->getOriginal('content') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content_uk" style="color:#2e7d32;">Опис країни (UK)</label>
                            <div>
                                <textarea name="content_uk"
                                          id="content_uk"
                                          class="content form-control form-control-line">{{ old('content_uk') ?? optional($country)->getOriginal('content_uk') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_title">Meta-тег title (RU)</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? optional($country)->getOriginal('meta_title') ?? '' }}"
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
                                       value="{{ old('meta_title_uk') ?? optional($country)->getOriginal('meta_title_uk') ?? '' }}"
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
                                          class="form-control form-control-line">{{ old('meta_description') ?? optional($country)->getOriginal('meta_description') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_description_uk" style="color:#2e7d32;">Meta-тег description (UK)</label>
                            <div>
                                <textarea name="meta_description_uk"
                                          rows="5"
                                          id="meta_description_uk"
                                          class="form-control form-control-line">{{ old('meta_description_uk') ?? optional($country)->getOriginal('meta_description_uk') ?? '' }}</textarea>
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
    @php($min = (array) request('min', []))
                    <form action="{{ route('admin.adCountries') }}" method="GET" id="geo-filters">
                        @foreach(request()->only(['sort', 'dir', 'per_page']) as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;">
                            <button class="btn btn-sm btn-primary">Застосувати фільтри</button>
                            @if(request()->hasAny(['q', 'has_ads', 'min']))
                                <a href="{{ route('admin.adCountries') }}" class="btn btn-sm btn-link">Скинути</a>
                            @endif
                            <span class="text-muted" style="font-size:13px;">Знайдено: {{ $countries->total() }}</span>
                            <a id="deleteMany" href="/admin/countries/deleteMany" class="btn btn-sm btn-danger ml-auto">Удалить выбранные</a>
                        </div>
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover table-middle-cell">
                            <thead>
                                <tr>
                                    <th></th>
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCountries', 'col' => 'id', 'label' => 'ID', 'class' => 'text-center'])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCountries', 'col' => 'name', 'label' => 'Страна', 'class' => ''])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCountries', 'col' => 'regions_count', 'label' => 'Областей', 'class' => 'text-center'])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCountries', 'col' => 'cities_count', 'label' => 'Городов', 'class' => 'text-center'])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCountries', 'col' => 'ads_count', 'label' => 'Оголошень', 'class' => 'text-center'])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCountries', 'col' => 'active_count', 'label' => 'Активних', 'class' => 'text-center'])
                                    <th></th>
                                </tr>
                                <tr class="geo-filter">
                                    <th></th><th></th>
                                    <th><input type="search" name="q" value="{{ request('q') }}" placeholder="Назва, slug або ID" class="form-control"></th>
                                    <th><input type="number" min="0" name="min[regions_count]" value="{{ $min['regions_count'] ?? '' }}" placeholder="від" class="form-control"></th>
                                    <th><input type="number" min="0" name="min[cities_count]" value="{{ $min['cities_count'] ?? '' }}" placeholder="від" class="form-control"></th>
                                    <th>
                                        <select name="has_ads" class="form-control mb-1" onchange="this.form.submit()">
                                            <option value="">Усі</option>
                                            <option value="yes" {{ request('has_ads') === 'yes' ? 'selected' : '' }}>Є оголошення</option>
                                            <option value="no" {{ request('has_ads') === 'no' ? 'selected' : '' }}>Без оголошень</option>
                                        </select>
                                        <input type="number" min="0" name="min[ads_count]" value="{{ $min['ads_count'] ?? '' }}" placeholder="від" class="form-control">
                                    </th>
                                    <th><input type="number" min="0" name="min[active_count]" value="{{ $min['active_count'] ?? '' }}" placeholder="від" class="form-control"></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($countries as $country_item)
                                <tr>
                                    <td style="width: 30px"><input type="checkbox" name="id[]" value="{{ $country_item->id }}" form="geo-select"></td>
                                    <td class="text-center">{{ $country_item->id }}</td>
                                    <td><b>{{ $country_item->name }}</b></td>
                                    <td class="text-center"><a href="{{ route('admin.adRegions', ['country_id' => $country_item->id]) }}" title="Області країни">{{ $country_item->regions_count }}</a></td>
                                    <td class="text-center"><a href="{{ route('admin.adCities', ['country_id' => $country_item->id]) }}" title="Міста країни">{{ $country_item->cities_count }}</a></td>
                                    <td class="text-center geo-count">
                                        @if($country_item->ads_count > 0)
                                            <a href="{{ route('admin.ads', ['country_id' => $country_item->id]) }}" title="Усі оголошення країни в адмінці">{{ $country_item->ads_count }}</a>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center geo-count">
                                        @if($country_item->active_count > 0)
                                            <a href="{{ route('admin.ads', ['country_id' => $country_item->id, 'status' => 1]) }}" title="Активні оголошення країни в адмінці">{{ $country_item->active_count }}</a>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adCountries.edit', ['id' => $country_item->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adCountries.delete', ['id' => $country_item->id]) }}" onclick="return confirm('Вы пытаетесь удалить страну {{ $country_item->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-muted">Країн за цими фільтрами немає.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        </div>
                    </form>
                    @include('admin.ad.partials.list-pager', ['paginator' => $countries, 'route' => 'admin.adCountries'])
                </div>
            </div>
        </div>
    </div>
@endsection