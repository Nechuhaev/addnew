@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Індексація статей у Google</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    @if (Breadcrumbs::exists('admin.stat.indexing'))
                        {{ Breadcrumbs::render('admin.stat.indexing') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2>{{ $summary['total'] }}</h2>
                    <small class="text-muted">Всього статей</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-success">{{ $summary['indexed'] }}</h2>
                    <small class="text-muted">Проіндексовано</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-danger">{{ $summary['not_indexed'] }}</h2>
                    <small class="text-muted">НЕ проіндексовано</small>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="text-muted">{{ $summary['never_checked'] }}</h2>
                    <small class="text-muted">Ще не перевірялись</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Стаття</th>
                                    <th>Статус</th>
                                    <th>Coverage</th>
                                    <th>Останній краул</th>
                                    <th>Перевірено</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td><a href="{{ route('blog.article', $row->slug) }}" target="_blank">{{ $row->name }}</a></td>
                                        <td>
                                            @if(is_null($row->checked_at))
                                                <span class="badge badge-secondary">Не перевірено</span>
                                            @elseif($row->verdict === 'PASS')
                                                <span class="badge badge-success">Проіндексовано</span>
                                            @elseif($row->verdict === 'NEUTRAL')
                                                <span class="badge badge-warning">Виключено (NEUTRAL)</span>
                                            @else
                                                <span class="badge badge-danger">{{ $row->verdict ?? 'Проблема' }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $row->coverage_state ?? '—' }}</td>
                                        <td>{{ $row->last_crawl_time ? \Carbon\Carbon::parse($row->last_crawl_time)->format('d.m.Y H:i') : '—' }}</td>
                                        <td>{{ $row->checked_at ? \Carbon\Carbon::parse($row->checked_at)->format('d.m.Y H:i') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">Даних поки немає</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection