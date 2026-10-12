@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Статистика: {{ $shop->firstname ?: $shop->email }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <a href="{{ route('admin.shops.stats', \App\Services\StatsPeriod::fromRequest(request())->query()) }}" class="btn btn-sm btn-secondary">&larr; До списку магазинів</a>
                </div>
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
                    @include('admin.shops.partials.stats-toolbar')

                    <div class="st-tiles">
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['shop_views']) }}</div><div class="st-tile__l">Переглядів сторінки магазину</div></div>
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['product_views']) }}</div><div class="st-tile__l">Переглядів товарів</div></div>
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['click_contacts']) }}</div><div class="st-tile__l">Показів контактів</div></div>
                        <div class="st-tile"><div class="st-tile__v">{{ $fmt($totals['click_shop_link']) }}</div><div class="st-tile__l">Переходів на сайт-джерело</div></div>
                    </div>

                    <h4>Статистика по товарах</h4>

                    <form method="get" action="{{ url()->current() }}">
                        @foreach(request()->only(['period', 'from', 'to', 'sort', 'dir', 'per_page']) as $name => $value)
                            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                        @endforeach
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover st-table">
                                <thead>
                                    <tr>
                                        @include('admin.shops.partials.stats-sort', ['col' => 'name', 'label' => 'Товар'])
                                        @foreach($numeric as $col => $label)
                                            @include('admin.shops.partials.stats-sort', ['col' => $col, 'label' => $label, 'class' => 'text-center'])
                                        @endforeach
                                    </tr>
                                    <tr class="st-filter">
                                        <th>
                                            <div class="d-flex" style="gap:6px;">
                                                <input type="search" name="q" value="{{ request('q') }}" placeholder="Назва або #ID" class="form-control">
                                                <select name="state" class="form-control" style="max-width:150px;" onchange="this.form.submit()">
                                                    <option value="">Усі товари</option>
                                                    <option value="active" {{ request('state') === 'active' ? 'selected' : '' }}>Активні</option>
                                                    <option value="inactive" {{ request('state') === 'inactive' ? 'selected' : '' }}>Неактивні</option>
                                                </select>
                                            </div>
                                        </th>
                                        @foreach($numeric as $col => $label)
                                            <th><input type="number" min="0" step="any" name="min[{{ $col }}]" value="{{ $min[$col] ?? '' }}" placeholder="від" class="form-control" aria-label="{{ $label }} від"></th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($products as $product)
                                        <tr class="{{ $product['active'] ? '' : 'text-muted' }}">
                                            <td class="st-name">
                                                <a href="{{ $product['url'] }}" target="_blank" style="display:flex; align-items:center; gap:10px;">
                                                    <img src="{{ $product['image'] ?: asset('assets/front/img/placeholder.png') }}" alt="" width="40" height="40" style="object-fit:cover; border-radius:4px; flex:0 0 40px;">
                                                    <span>{{ $product['name'] }}@if(!$product['active']) <span class="badge badge-secondary">неактивний</span>@endif</span>
                                                </a>
                                            </td>
                                            <td class="text-center">{{ $fmt($product['views']) }}</td>
                                            <td class="text-center">{{ $fmt($product['click_contacts']) }}</td>
                                            <td class="text-center">{{ $fmt($product['click_shop_link']) }}</td>
                                            <td class="text-center">{{ $product['avg_duration'] ? gmdate('i:s', $product['avg_duration']) : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="{{ 1 + count($numeric) }}" class="text-muted">Немає товарів за цими фільтрами.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex" style="gap:8px;">
                            <button type="submit" class="btn btn-sm btn-primary">Застосувати фільтри</button>
                            @if(request()->hasAny(['q', 'min', 'state']))
                                <a href="{{ url()->current() . ($period->query() ? '?' . http_build_query($period->query()) : '') }}" class="btn btn-sm btn-link">Скинути фільтри</a>
                            @endif
                        </div>
                    </form>

                    @include('admin.shops.partials.stats-pager', ['paginator' => $products])
                </div>
            </div>
        </div>
    </div>
@endsection
