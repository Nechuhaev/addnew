@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">AI-дзвінки магазинам</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @include('admin.caller._nav')

    @php
        $missing = array_keys(array_filter($health, function ($ok) { return !$ok; }));
    @endphp

    {{-- Стан модуля --}}
    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between">
                <div class="mb-2">
                    @if(!$settings['enabled'])
                        <span class="badge badge-secondary p-2">Автонабір вимкнено</span>
                    @elseif($settings['dry_run'])
                        <span class="badge badge-warning p-2">Тестовий режим: без реальних дзвінків</span>
                    @else
                        <span class="badge badge-success p-2">Автонабір працює</span>
                    @endif
                    <span class="badge {{ $callingNow ? 'badge-info' : 'badge-light' }} p-2">
                        {{ $callingNow ? 'Зараз робочий час' : 'Поза робочим часом' }}
                        ({{ $settings['call_hours_start'] }}:00–{{ $settings['call_hours_end'] }}:00)
                    </span>
                    <span class="badge {{ $settings['email_enabled'] ? 'badge-success' : 'badge-light' }} p-2">
                        Листи: {{ $settings['email_enabled'] ? 'увімкнено' : 'вимкнено' }}
                    </span>
                </div>
                <form method="post" action="{{ route('admin.caller.runNow') }}" class="mb-2">
                    @csrf
                    <button class="btn btn-outline-primary btn-sm btn-block-xs" title="Аналіз розмов і набір, не чекаючи cron">
                        <i class="mdi mdi-play"></i> Запустити прохід зараз
                    </button>
                </form>
            </div>
            @if($missing)
                <div class="alert alert-warning mb-0 mt-2">
                    Не задано в .env: <b>{{ implode(', ', $missing) }}</b>.
                    Без цього частина функцій не працюватиме — див. <a href="{{ route('admin.caller.settings') }}">Налаштування</a>.
                </div>
            @endif
        </div>
    </div>

    {{-- Ключові цифри --}}
    <div class="row">
        @foreach([
            ['Дзвінків сьогодні', $totals['calls_today'] . ' / ' . $settings['max_calls_per_day'], 'mdi-phone'],
            ['Зараз на лінії', $totals['active'], 'mdi-phone-in-talk'],
            ['У черзі до дзвінка', $totals['due'], 'mdi-format-list-numbers'],
            ['Розмов за 30 днів', $totals['talked_30d'], 'mdi-account-voice'],
            ['Згод за 30 днів', $totals['agreed_30d'], 'mdi-check-circle'],
            ['Зареєструвалися', $totals['registered'], 'mdi-store'],
        ] as $s)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card">
                    <div class="card-body py-3">
                        <div class="caller-stat-label"><i class="mdi {{ $s[2] }}"></i> {{ $s[0] }}</div>
                        <div class="caller-stat">{{ $s[1] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($totals['emails_review'] || $totals['analysis_failed'])
        <div class="alert alert-info">
            @if($totals['emails_review'])
                <a href="{{ route('admin.caller.emails', ['status' => 'review']) }}">{{ $totals['emails_review'] }} лист(ів) чекають перевірки</a>.
            @endif
            @if($totals['analysis_failed'])
                {{ $totals['analysis_failed'] }} розмов(и) не вдалося проаналізувати — див. <code>storage/logs/caller.log</code>.
            @endif
        </div>
    @endif

    <div class="row">
        {{-- По днях --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Останні 14 днів</h5>
                    @if($daily->isEmpty())
                        <p class="text-muted mb-0">Дзвінків ще не було.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                <tr>
                                    <th>Дата</th>
                                    <th class="text-center">Набрано</th>
                                    <th class="text-center">Розмов</th>
                                    <th class="text-center">Згод</th>
                                    <th class="text-center d-none d-sm-table-cell">Надіслати інфо</th>
                                    <th class="text-center d-none d-md-table-cell">Хвилин</th>
                                    <th class="text-center d-none d-md-table-cell">Конверсія</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($daily as $d)
                                    <tr>
                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($d->call_date)->format('d.m') }}</td>
                                        <td class="text-center">{{ $d->total }}</td>
                                        <td class="text-center">{{ (int) $d->talked }}</td>
                                        <td class="text-center"><b>{{ (int) $d->agreed }}</b></td>
                                        <td class="text-center d-none d-sm-table-cell">{{ (int) $d->send_info }}</td>
                                        <td class="text-center d-none d-md-table-cell">{{ $d->minutes }}</td>
                                        <td class="text-center d-none d-md-table-cell">{{ $d->talked ? round($d->agreed / $d->talked * 100) . '%' : '—' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Результати й статуси --}}
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Результати розмов (30 днів)</h5>
                    @forelse($outcomes as $o => $n)
                        <div class="d-flex justify-content-between border-bottom py-1">
                            <a href="{{ route('admin.caller.leads', ['outcome' => $o]) }}">{{ \App\Models\Caller\Lead::OUTCOMES[$o] ?? $o }}</a>
                            <b>{{ $n }}</b>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Поки немає.</p>
                    @endforelse

                    <h5 class="card-title mt-4">Ліди за статусом</h5>
                    @foreach(\App\Models\Caller\Lead::STATUSES as $st => $label)
                        @if(!empty($byStatus[$st]))
                            <div class="d-flex justify-content-between border-bottom py-1">
                                <a href="{{ route('admin.caller.leads', ['status' => $st]) }}">{{ $label }}</a>
                                <b>{{ $byStatus[$st] }}</b>
                            </div>
                        @endif
                    @endforeach
                    @if(!$byStatus)
                        <p class="text-muted mb-0">Лідів немає — <a href="{{ route('admin.caller.leads') }}">імпортуйте CSV</a>.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($qualityIssues->isNotEmpty())
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Порушення сценарію ботом (останні)</h5>
                <p class="text-muted small">Claude позначає дзвінки, де асистентка не сказала, що вона AI, вигадала факти чи тиснула після відмови. Правте промпт у кабінеті ElevenLabs.</p>
                @foreach($qualityIssues as $c)
                    <div class="border-bottom py-2">
                        <a href="{{ route('admin.caller.leads.show', $c->lead_id) }}">{{ optional($c->lead)->store_name ?: $c->conversation_id }}</a>
                        <small class="text-muted">{{ optional($c->started_at)->format('d.m H:i') }}</small><br>
                        <span class="text-danger">{{ implode('; ', $c->analysis()['quality_flags'] ?? []) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-body small text-muted">
            URL вебхука для ElevenLabs (Post-call webhook, подія transcript): <code class="text-break">{{ $webhookUrl }}</code>
        </div>
    </div>
@endsection
