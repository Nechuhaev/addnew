@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">Статистика магазинів</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @include('admin.shops.partials.stats-styles')
    @php
        $fmt = function ($n) { return number_format((float) $n, 0, '', ' '); };
        $min = (array) request('min', []);
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.shops') }}" class="btn btn-sm btn-secondary mb-3">&larr; До магазинів</a>
                    <p class="text-muted" style="font-size:13px;">
                        Перегляди сторінки магазину, перегляди товарів, покази контактів і переходи на сайт за обраний період.
                        Фільтри — у рядку під заголовками (числові: «від»), сортування — клік по заголовку. Назва магазину відкриває деталізацію по товарах за той самий період.
                    </p>

                    @include('admin.shops.partials.stats-toolbar')

                    <div class="st-tiles">
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($shopsCount) }}</div><div class="st-tile__l">Магазинів (за фільтрами)</div></div>
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['shop_views']) }}</div><div class="st-tile__l">Переглядів магазинів</div></div>
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['product_views']) }}</div><div class="st-tile__l">Переглядів товарів</div></div>
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['click_contacts']) }}</div><div class="st-tile__l">Показів контактів</div></div>
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['click_shop_link']) }}</div><div class="st-tile__l">Переходів на сайт ({{ $totals['ctr'] }}%)</div></div>
                    </div>

                    <form method="get" action="{{ route('admin.shops.stats') }}" id="st-filters">
                        @foreach(request()->only(['period', 'from', 'to', 'sort', 'dir', 'per_page']) as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover st-table">
                                <thead>
                                    <tr>
                                        @include('admin.shops.partials.stats-sort', ['col' => 'id', 'label' => '#'])
                                        @include('admin.shops.partials.stats-sort', ['col' => 'name', 'label' => 'Магазин'])
                                        @foreach($numeric as $col => $label)
                                            @include('admin.shops.partials.stats-sort', ['col' => $col, 'label' => $label, 'class' => 'text-center', 'title' => $titles[$col] ?? null])
                                        @endforeach
                                    </tr>
                                    <tr class="st-filter">
                                        <th></th>
                                        <th>
                                            <div class="d-flex" style="gap:6px;">
                                                <input type="search" name="q" value="{{ request('q') }}" placeholder="Назва, email, сайт" class="form-control">
                                                <select name="active_only" class="form-control" style="max-width:140px;" onchange="this.form.submit()" aria-label="Активність">
                                                    <option value="">Усі</option>
                                                    <option value="1" {{ request('active_only') ? 'selected' : '' }}>З активністю</option>
                                                </select>
                                            </div>
                                        </th>
                                        @foreach($numeric as $col => $label)
                                            <th><input type="number" min="0" step="any" name="min[{{ $col }}]" value="{{ $min[$col] ?? '' }}" placeholder="від" class="form-control" aria-label="{{ $label }} від"></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($shops as $shop)
                                        <tr>
                                            <td>{{ $shop['id'] }}</td>
                                            <td class="st-name">
                                                <a href="{{ route('admin.shops.stats.show', array_merge(['id' => $shop['id']], $period->query())) }}">{{ $shop['name'] }}</a>
                                                <br><small class="text-muted">{{ $shop['email'] }}@if($shop['site']) · {{ $shop['site'] }}@endif</small>
                                            </td>
                                            <td class="text-center">{{ $fmt($shop['products_count']) }}</td>
                                            <td class="text-center">{{ $fmt($shop['active_products']) }}</td>
                                            <td class="text-center">{{ $fmt($shop['shop_views']) }}</td>
                                            <td class="text-center">{{ $fmt($shop['product_views']) }}</td>
                                            <td class="text-center">{{ $fmt($shop['click_contacts']) }}</td>
                                            <td class="text-center">{{ $fmt($shop['click_shop_link']) }}</td>
                                            <td class="text-center">{{ $shop['ctr'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ 2 + count($numeric) }}" class="text-muted">Немає магазинів за цими фільтрами.</td></tr>
                                    @endforelse
                                </tbody>
                                @if($shops->total() > 0)
                                    <tfoot>
                                        <tr>
                                            <td colspan="2">Разом ({{ $fmt($shopsCount) }})</td>
                                            @foreach($numeric as $col => $label)
                                                <td class="text-center">{{ $col === 'ctr' ? $totals['ctr'] : $fmt($totals[$col]) }}</td>
                                            @endforeach
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-sm btn-primary">Застосувати фільтри</button>
                            @if(request()->hasAny(['q', 'min', 'active_only']))
                                <a href="{{ route('admin.shops.stats', $period->query()) }}" class="btn btn-sm btn-link">Скинути фільтри</a>
                            @endif
                        </div>
                    </form>

                    @include('admin.shops.partials.stats-pager', ['paginator' => $shops])
                </div>
            </div>
        </div>
    </div>
@endsection
