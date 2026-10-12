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
                            <label>Область (RU)</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? optional($region)->getOriginal('name') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="name_uk" style="color:#2e7d32;">Область (UK)</label>
                            <div>
                                <input type="text"
                                       name="name_uk"
                                       id="name_uk"
                                       value="{{ old('name_uk') ?? optional($region)->getOriginal('name_uk') ?? '' }}"
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
                                       value="{{ old('slug') ?? $region->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content">Описание для области (RU)</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? optional($region)->getOriginal('content') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content_uk" style="color:#2e7d32;">Опис області (UK)</label>
                            <div>
                                <textarea name="content_uk"
                                          id="content_uk"
                                          class="content form-control form-control-line">{{ old('content_uk') ?? optional($region)->getOriginal('content_uk') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_title">Meta-тег title (RU)</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? optional($region)->getOriginal('meta_title') ?? '' }}"
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
                                       value="{{ old('meta_title_uk') ?? optional($region)->getOriginal('meta_title_uk') ?? '' }}"
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
                                          class="form-control form-control-line">{{ old('meta_description') ?? optional($region)->getOriginal('meta_description') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_description_uk" style="color:#2e7d32;">Meta-тег description (UK)</label>
                            <div>
                                <textarea name="meta_description_uk"
                                          rows="5"
                                          id="meta_description_uk"
                                          class="form-control form-control-line">{{ old('meta_description_uk') ?? optional($region)->getOriginal('meta_description_uk') ?? '' }}</textarea>
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
                    @php
                        $min = (array) request('min', []);
                        $sortLink = function ($col, $label, $class = '') use ($sort, $dir) {
                            $isSorted = $sort === $col;
                            $q = array_merge(request()->except('page'), ['sort' => $col, 'dir' => $isSorted && $dir === 'asc' ? 'desc' : 'asc']);
                            $arrow = $isSorted ? ($dir === 'asc' ? ' <i class="mdi mdi-arrow-up"></i>' : ' <i class="mdi mdi-arrow-down"></i>') : '';
                            return '<th class="' . $class . '"><a style="color:' . ($isSorted ? '#19346c;font-weight:700' : 'inherit') . '" href="' . e(route('admin.adRegions') . '?' . http_build_query($q)) . '">' . e($label) . '</a>' . $arrow . '</th>';
                        };
                    @endphp
                    <style>
                        .rg-filter th { background:#f8f9fa; padding:.3rem .4rem; }
                        .rg-filter input, .rg-filter select { width:100%; min-width:55px; height:28px; padding:2px 6px; font-size:12px; }
                        .rg-count a { font-weight:600; }
                    </style>
                    <form action="{{ route('admin.adRegions') }}" method="GET" id="rg-filters">
                        @foreach(request()->only(['sort', 'dir', 'per_page']) as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <div class="d-flex flex-wrap align-items-center mb-2" style="gap:8px;">
                            <button class="btn btn-sm btn-primary">Застосувати фільтри</button>
                            @if(request()->hasAny(['q', 'country_id', 'has_ads', 'min']))
                                <a href="{{ route('admin.adRegions') }}" class="btn btn-sm btn-link">Скинути</a>
                            @endif
                            <span class="text-muted" style="font-size:13px;">Знайдено: {{ $regions->total() }}</span>
                            <a id="deleteMany" href="/admin/regions/deleteMany" class="btn btn-sm btn-danger ml-auto">Удалить выбранные</a>
                        </div>
                        <div class="table-responsive">
                        <table class="table table-bordered table-hover table-middle-cell">
                            <thead>
                                <tr>
                                    <th></th>
                                    {!! $sortLink('id', 'ID', 'text-center') !!}
                                    {!! $sortLink('name', 'Область (Регион)') !!}
                                    {!! $sortLink('country', 'Страна') !!}
                                    {!! $sortLink('cities_count', 'Городов', 'text-center') !!}
                                    {!! $sortLink('ads_count', 'Оголошень', 'text-center') !!}
                                    {!! $sortLink('active_count', 'Активних', 'text-center') !!}
                                    <th class="text-center cell-actions"></th>
                                </tr>
                                <tr class="rg-filter">
                                    <th></th>
                                    <th></th>
                                    <th><input type="search" name="q" value="{{ request('q') }}" placeholder="Назва, slug або ID" class="form-control"></th>
                                    <th>
                                        <select name="country_id" class="form-control" onchange="this.form.submit()">
                                            <option value="">Усі</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country['id'] }}" {{ (string) request('country_id') === (string) $country['id'] ? 'selected' : '' }}>{{ $country['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </th>
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
                            @forelse($regions as $region_item)
                                <tr>
                                    <td style="width: 40px" class="text-center"><input type="checkbox" name="id[]" value="{{ $region_item->id }}" form="rg-select"></td>
                                    <td class="text-center">{{ $region_item->id }}</td>
                                    <td><b>{{ $region_item->name }}</b></td>
                                    <td>{{ optional($region_item->country)->name }}</td>
                                    <td class="text-center"><a href="{{ route('admin.adCities', ['region_id' => $region_item->id]) }}" title="Міста області">{{ $region_item->cities_count }}</a></td>
                                    <td class="text-center rg-count">
                                        @if($region_item->ads_count > 0)
                                            <a href="{{ route('admin.ads', ['region_id' => $region_item->id]) }}" title="Усі оголошення області в адмінці">{{ $region_item->ads_count }}</a>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center rg-count">
                                        @if($region_item->active_count > 0)
                                            <a href="{{ route('admin.ads', ['region_id' => $region_item->id, 'status' => 1]) }}" title="Активні оголошення області в адмінці">{{ $region_item->active_count }}</a>
                                            @if($region_item->country && $region_item->slug)
                                                <a href="{{ route('region.page', ['country' => $region_item->country->slug, 'region' => $region_item->slug]) }}" target="_blank" rel="noopener" title="Сторінка області на сайті" class="ml-1"><i class="mdi mdi-open-in-new"></i></a>
                                            @endif
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adRegions.edit', ['id' => $region_item->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adRegions.delete', ['id' => $region_item->id]) }}" onclick="return confirm('Вы пытаетесь удалить регион {{ $region_item->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-muted">Областей за цими фільтрами немає.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                        </div>
                    </form>
                    <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap:10px;">
                        <div>{{ $regions->links() }}</div>
                        <form method="get" action="{{ route('admin.adRegions') }}" class="d-flex align-items-center" style="gap:6px;">
                            @foreach(request()->except(['page', 'per_page']) as $name => $value)
                                @if(is_array($value))
                                    @foreach($value as $k => $v)<input type="hidden" name="{{ $name }}[{{ $k }}]" value="{{ $v }}">@endforeach
                                @else
                                    <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <span class="text-muted" style="font-size:13px;">на сторінці</span>
                            <select name="per_page" class="form-control form-control-sm" style="width:auto;" onchange="this.form.submit()">
                                @foreach($perPageOptions as $n)
                                    <option value="{{ $n }}" {{ $regions->perPage() === $n ? 'selected' : '' }}>{{ $n }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    <script>
                        document.getElementById('rg-filters').addEventListener('submit', function () {
                            this.querySelectorAll('input, select').forEach(function (el) { if (el.name && el.value === '') el.disabled = true; });
                        });
                    </script>
                </div>
            </div>
        </div>
    </div>
@endsection