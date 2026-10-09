@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">Фіди магазинів (автооновлення)</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <a href="{{ route('admin.shops') }}" class="btn btn-sm btn-secondary mb-3">&larr; До магазинів</a>
            <a href="{{ route('admin.shops.feeds') }}" class="btn btn-sm mb-3 {{ request('status') ? 'btn-outline-primary' : 'btn-primary' }}">Усі</a>
            <a href="{{ route('admin.shops.feeds', ['status' => 'failed']) }}" class="btn btn-sm mb-3 {{ request('status') === 'failed' ? 'btn-danger' : 'btn-outline-danger' }}">З помилками ({{ $failedCount }})</a>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($feeds->isEmpty())
                <p class="text-muted mb-0">Фідів немає.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr><th>Магазин</th><th>URL</th><th>Статус</th><th>Частота</th><th>Останнє</th><th>Наступне</th><th class="text-right">Останній запуск</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($feeds as $feed)
                                @php
                                    $run = $lastRuns->get($feed->id);
                                @endphp
                                @php
                                    $badgeMap = ['ok' => 'success', 'failed' => 'danger', 'running' => 'warning', 'idle' => 'secondary'];
                                    $badge = $badgeMap[$feed->isRunning() ? 'running' : $feed->status] ?? 'secondary';
                                @endphp
                                <tr>
                                    <td><a href="{{ route('admin.shops.edit', $feed->user_id) }}">{{ optional($feed->user)->username ?? '#' . $feed->user_id }}</a></td>
                                    <td style="max-width:280px; word-break:break-all; font-size:12px;">{{ $feed->url }}</td>
                                    <td>
                                        <span class="badge badge-{{ $badge }}">{{ $feed->isRunning() ? 'оновлюється' : $feed->status }}</span>
                                        @if(!$feed->enabled)<span class="badge badge-secondary">вимкнено</span>@endif
                                        @if($feed->last_error)<div class="text-danger" style="font-size:12px;">{{ \Illuminate\Support\Str::limit($feed->last_error, 120) }} ({{ $feed->fail_count }}×)</div>@endif
                                    </td>
                                    <td>{{ $feed->frequency_hours }} год</td>
                                    <td style="white-space:nowrap;">{{ optional($feed->last_run_at)->format('d.m H:i') ?? '—' }}</td>
                                    <td style="white-space:nowrap;">{{ $feed->enabled ? (optional($feed->next_run_at)->format('d.m H:i') ?? '—') : '—' }}</td>
                                    <td class="text-right" style="font-size:12px; white-space:nowrap;">
                                        @if($run)
                                            {{ $run->total }} у фіді · +{{ $run->new_count }} · ↻{{ $run->update_count }} · ✕{{ $run->error_count }} · зникло {{ $run->missing_count }}
                                        @else — @endif
                                    </td>
                                    <td>
                                        <form action="{{ route('admin.shops.feeds.run', $feed->id) }}" method="post">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary" {{ $feed->isRunning() ? 'disabled' : '' }}>Оновити</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $feeds->links() }}
            @endif
        </div>
    </div>
@endsection
