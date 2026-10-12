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
    @php($min = (array) request('min', []))
                    <form action="{{ route('admin.adCities') }}" method="GET" id="geo-filters">
                        @foreach(request()->only(['sort', 'dir', 'per_page']) as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;">
                            <button class="btn btn-sm btn-primary">Застосувати фільтри</button>
                            @if(request()->hasAny(['q', 'country_id', 'region_id', 'has_ads', 'min']))
                                <a href="{{ route('admin.adCities') }}" class="btn btn-sm btn-link">Скинути</a>
                            @endif
                            <span class="text-muted" style="font-size:13px;">Знайдено: {{ $cities->total() }}</span>
                            <a id="deleteMany" href="/admin/cities/deleteMany" class="btn btn-sm btn-danger ml-auto">Удалить выбранные</a>
                        </div>
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover table-middle-cell">
                            <thead>
                                <tr>
                                    <th></th>
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCities', 'col' => 'id', 'label' => 'ID', 'class' => 'text-center'])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCities', 'col' => 'name', 'label' => 'Город', 'class' => ''])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCities', 'col' => 'region', 'label' => 'Регион', 'class' => ''])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCities', 'col' => 'country', 'label' => 'Страна', 'class' => ''])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCities', 'col' => 'ads_count', 'label' => 'Оголошень', 'class' => 'text-center'])
                                    @include('admin.ad.partials.sort-th', ['route' => 'admin.adCities', 'col' => 'active_count', 'label' => 'Активних', 'class' => 'text-center'])
                                    <th></th>
                                </tr>
                                <tr class="geo-filter">
                                    <th></th><th></th>
                                    <th><input type="search" name="q" value="{{ request('q') }}" placeholder="Назва, slug або ID" class="form-control"></th>
                                    <th>
                                        <select name="region_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">Усі</option>
                                            @foreach($allRegions as $r)
                                                @if(!request('country_id') || (int) request('country_id') === (int) $r->country_id)
                                                    <option value="{{ $r->id }}" {{ (string) request('region_id') === (string) $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </th>
                                    <th>
                                        <select name="country_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">Усі</option>
                                            @foreach($countries as $c)
                                                <option value="{{ $c->id }}" {{ (string) request('country_id') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </th>
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
                            @forelse($cities as $city_item)
                                @php($cRegion = $city_item->region)
                                @php($cCountry = optional($cRegion)->country)
                                <tr>
                                    <td style="width: 30px"><input type="checkbox" name="id[]" value="{{ $city_item->id }}" form="geo-select"></td>
                                    <td class="text-center">{{ $city_item->id }}</td>
                                    <td><b>{{ $city_item->name }}</b></td>
                                    <td>{{ optional($cRegion)->name }}</td>
                                    <td>{{ optional($cCountry)->name }}</td>
                                    <td class="text-center geo-count">
                                        @if($city_item->ads_count > 0)
                                            <a href="{{ route('admin.ads', ['city_id' => $city_item->id]) }}" title="Усі оголошення міста в адмінці">{{ $city_item->ads_count }}</a>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center geo-count">
                                        @if($city_item->active_count > 0)
                                            <a href="{{ route('admin.ads', ['city_id' => $city_item->id, 'status' => 1]) }}" title="Активні оголошення міста в адмінці">{{ $city_item->active_count }}</a>
                                            @if($cCountry && $cRegion && $city_item->slug)
                                                <a href="{{ route('city.page', ['country' => $cCountry->slug, 'region' => $cRegion->slug, 'city' => $city_item->slug]) }}" target="_blank" rel="noopener" title="Сторінка міста на сайті" class="ml-1"><i class="mdi mdi-open-in-new"></i></a>
                                            @endif
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adCities.edit', ['id' => $city_item->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adCities.delete', ['id' => $city_item->id]) }}" onclick="return confirm('Вы пытаетесь удалить город {{ $city_item->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-muted">Міст за цими фільтрами немає.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        </div>
                    </form>
                    @include('admin.ad.partials.list-pager', ['paginator' => $cities, 'route' => 'admin.adCities'])
                </div>
            </div>
        </div>
    </div>
@endsection