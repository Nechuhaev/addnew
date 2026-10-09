@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">Пошукові запити</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
@php
    $fmt = function ($n) { return number_format((int) $n, 0, '', ' '); };
    $searches = (int) $totals->searches;
    $zeroShare = $searches ? round($totals->zero / $searches * 100, 1) : 0;
    $maxDay = max(1, collect($daily)->max('n'));
    $qs = function (array $over) use ($days, $source, $filter) {
        return route('admin.searchQueries', array_filter(array_merge(['days' => $days, 'source' => $source, 'q' => $filter], $over), function ($v) { return $v !== null && $v !== ''; }));
    };
    $frontSearch = function ($q, $src = 'site', $shopId = null) {
        if ($src === 'stores') return route('stores', ['q' => $q]);
        if ($src === 'shop' && $shopId) return route('author', ['id' => $shopId, 's' => $q]);
        return route('ad.search', ['s' => $q]);
    };
@endphp
<style>
    .sq-tiles{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:16px}
    .sq-tile{padding:14px 16px;border:1px solid #e9ecef;border-radius:6px;background:#fff}
    .sq-tile__v{font-size:26px;font-weight:700;line-height:1.1}
    .sq-tile__l{color:#6c757d;font-size:13px}
    .sq-chart{display:flex;align-items:flex-end;gap:2px;height:160px;padding-top:6px;border-bottom:1px solid #dee2e6}
    .sq-bar{flex:1 1 0;display:flex;flex-direction:column-reverse;min-width:2px;height:100%;cursor:default}
    .sq-bar__ok{background:#3b5998;border-radius:0 0 0 0}
    .sq-bar__zero{background:#e8a33d;border-bottom:1px solid #fff}
    .sq-bar:hover .sq-bar__ok{background:#19346c}
    .sq-bar:hover .sq-bar__zero{background:#c27c12}
    .sq-axis{display:flex;justify-content:space-between;color:#6c757d;font-size:12px;margin-top:4px}
    .sq-legend{display:flex;gap:16px;font-size:13px;color:#495057;margin-bottom:6px}
    .sq-legend i{display:inline-block;width:12px;height:12px;border-radius:2px;margin-right:5px;vertical-align:-1px}
    .sq-table td,.sq-table th{padding:.45rem .6rem;vertical-align:middle}
    .sq-q{word-break:break-word}
    .sq-filters{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:16px}
    .sq-filters .form-control{width:auto}
</style>

<div class="card">
    <div class="card-body">
        <form class="sq-filters" method="get" action="{{ route('admin.searchQueries') }}">
            <div class="btn-group" role="group" aria-label="Період">
                @foreach($periods as $p)
                    <a href="{{ $qs(['days' => $p]) }}" class="btn btn-sm {{ $p === $days ? 'btn-primary' : 'btn-outline-primary' }}">{{ $p === 365 ? 'Рік' : $p . ' днів' }}</a>
                @endforeach
            </div>
            <input type="hidden" name="days" value="{{ $days }}">
            <select name="source" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Усі джерела</option>
                @foreach($sources as $key => $label)
                    <option value="{{ $key }}" {{ $source === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="search" name="q" value="{{ $filter }}" class="form-control form-control-sm" placeholder="Фільтр за текстом запиту">
            <button type="submit" class="btn btn-sm btn-secondary">Показати</button>
            @if($source || $filter !== '')
                <a href="{{ route('admin.searchQueries', ['days' => $days]) }}" class="btn btn-sm btn-link">Скинути</a>
            @endif
        </form>

        <div class="sq-tiles">
            <div class="sq-tile"><div class="sq-tile__v">{{ $fmt($searches) }}</div><div class="sq-tile__l">Пошуків</div></div>
            <div class="sq-tile"><div class="sq-tile__v">{{ $fmt($totals->unique_queries) }}</div><div class="sq-tile__l">Унікальних запитів</div></div>
            <div class="sq-tile"><div class="sq-tile__v">{{ $fmt($totals->zero) }}</div><div class="sq-tile__l">Без результатів ({{ $zeroShare }}%)</div></div>
            @foreach($sources as $key => $label)
                <div class="sq-tile"><div class="sq-tile__v">{{ $fmt($bySource[$key] ?? 0) }}</div><div class="sq-tile__l">{{ $label }}</div></div>
            @endforeach
            <div class="sq-tile"><div class="sq-tile__v">{{ $fmt($byLocale['uk'] ?? 0) }} / {{ $fmt($byLocale['ru'] ?? 0) }}</div><div class="sq-tile__l">Українська / російська версія</div></div>
        </div>

        <h5 class="card-title mb-2">Пошуки по днях</h5>
        <div class="sq-legend"><span><i style="background:#3b5998"></i>З результатами</span><span><i style="background:#e8a33d"></i>Без результатів</span></div>
        <div class="sq-chart" role="img" aria-label="Кількість пошуків по днях за {{ $days }} днів">
            @foreach($daily as $day)
                @php($ok = $day['n'] - $day['zero'])
                <div class="sq-bar" title="{{ $day['date']->format('d.m.Y') }}: {{ $day['n'] }} пошуків, без результатів — {{ $day['zero'] }}">
                    <div class="sq-bar__ok" style="height:{{ round($ok / $maxDay * 100, 2) }}%"></div>
                    <div class="sq-bar__zero" style="height:{{ round($day['zero'] / $maxDay * 100, 2) }}%"></div>
                </div>
            @endforeach
        </div>
        <div class="sq-axis"><span>{{ $daily[0]['date']->format('d.m.Y') }}</span><span>макс. {{ $fmt($maxDay) }} / день</span><span>{{ end($daily)['date']->format('d.m.Y') }}</span></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Топ запитів</h5>
                @if($top->isEmpty())
                    <p class="text-muted mb-0">За цей період пошуків немає.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover sq-table">
                            <thead><tr><th>Запит</th><th class="text-right">Разів</th><th class="text-right">Сер. результатів</th><th class="text-right">Без рез.</th></tr></thead>
                            <tbody>
                                @foreach($top as $row)
                                    <tr>
                                        <td class="sq-q"><a href="{{ $frontSearch($row->query) }}" target="_blank" rel="noopener">{{ $row->query }}</a></td>
                                        <td class="text-right">{{ $fmt($row->n) }}</td>
                                        <td class="text-right">{{ $fmt($row->avg_results) }}</td>
                                        <td class="text-right {{ $row->zero ? 'text-warning' : 'text-muted' }}">{{ $fmt($row->zero) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Шукали, але не знайшли</h5>
                <p class="text-muted" style="font-size:13px;">Ідеї, які товари чи магазини залучити, або які синоніми додати в описи.</p>
                @if($zero->isEmpty())
                    <p class="text-muted mb-0">Немає запитів без результатів.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover sq-table">
                            <thead><tr><th>Запит</th><th class="text-right">Разів</th><th class="text-right">Останній</th></tr></thead>
                            <tbody>
                                @foreach($zero as $row)
                                    <tr>
                                        <td class="sq-q">{{ $row->query }}</td>
                                        <td class="text-right">{{ $fmt($row->n) }}</td>
                                        <td class="text-right text-muted" style="white-space:nowrap;">{{ \Carbon\Carbon::parse($row->last_at)->format('d.m.Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Останні запити</h5>
        @if($recent->isEmpty())
            <p class="text-muted mb-0">Записів немає.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover sq-table">
                    <thead><tr><th>Час</th><th>Запит</th><th>Джерело</th><th class="text-right">Результатів</th><th>Мова</th><th>Користувач</th></tr></thead>
                    <tbody>
                        @foreach($recent as $row)
                            <tr>
                                <td class="text-muted" style="white-space:nowrap;">{{ $row->created_at->format('d.m.Y H:i') }}</td>
                                <td class="sq-q"><a href="{{ $frontSearch($row->query, $row->source, $row->shop_id) }}" target="_blank" rel="noopener">{{ $row->query }}</a></td>
                                <td>
                                    {{ $sources[$row->source] ?? $row->source }}
                                    @if($row->shop_id && isset($shopNames[$row->shop_id]))
                                        <br><a href="{{ route('admin.shops.edit', $row->shop_id) }}" class="text-muted" style="font-size:12px;">{{ $shopNames[$row->shop_id]->username }}</a>
                                    @endif
                                </td>
                                <td class="text-right {{ $row->results ? '' : 'text-warning' }}">{{ $fmt($row->results) }}</td>
                                <td>{{ strtoupper($row->locale) }}</td>
                                <td>{{ $row->is_auth ? 'увійшов' : 'гість' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
        <p class="text-muted mb-0 mt-2" style="font-size:12px;">Боти не враховуються; повтор того самого запиту в одній сесії рахується раз на 30 хв. Записи старші за {{ \App\SearchQuery::KEEP_DAYS }} днів видаляються автоматично.</p>
    </div>
</div>
@endsection
