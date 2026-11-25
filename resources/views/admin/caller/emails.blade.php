@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">AI-дзвінки: листи з інструкцією</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @include('admin.caller._nav')

    @unless($settings['email_enabled'])
        <div class="alert alert-warning">Автоматичну розсилку вимкнено — листи не ставляться в чергу й не відправляються.
            Увімкніть у <a href="{{ route('admin.caller.settings') }}">налаштуваннях</a>.</div>
    @endunless

    <div class="card">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start">
                <ul class="nav nav-tabs mb-3 flex-nowrap" style="overflow-x:auto; overflow-y:hidden;">
                    @foreach(['review' => 'На перевірці', 'pending' => 'У черзі', 'failed' => 'Помилки', 'sent' => 'Надіслано', 'skipped' => 'Скасовано', 'all' => 'Усі'] as $k => $v)
                        <li class="nav-item">
                            <a class="nav-link text-nowrap {{ $status === $k ? 'active' : '' }}" href="{{ route('admin.caller.emails', ['status' => $k]) }}">
                                {{ $v }}
                                @if($k !== 'all' && !empty($counts[$k]))<span class="badge badge-pill badge-light">{{ $counts[$k] }}</span>@endif
                            </a>
                        </li>
                    @endforeach
                </ul>
                <form method="post" action="{{ route('admin.caller.emails.sendNow') }}" class="mb-3">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary btn-block-xs" {{ empty($counts['pending']) ? 'disabled' : '' }}>
                        <i class="mdi mdi-send"></i> Надіслати чергу зараз
                    </button>
                </form>
            </div>

            @if($status === 'review')
                <p class="text-muted small">Сюди потрапляють листи, де email розпізнано з сумнівом, адреса невалідна, поштовий сервіс у списку
                    заблокованих або ввімкнено ручне схвалення. Перевірте адресу (за потреби виправте) і схваліть.</p>
            @endif

            @forelse($emails as $e)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex flex-wrap justify-content-between">
                        <div class="mb-1">
                            <span class="badge badge-{{ $e->statusBadge() }}">{{ $statuses[$e->status] ?? $e->status }}</span>
                            @if($e->lead)
                                <a href="{{ route('admin.caller.leads.show', $e->lead_id) }}"><b>{{ $e->lead->store_name }}</b></a>
                                <small class="text-muted">{{ $e->lead->phone }}</small>
                            @endif
                        </div>
                        <small class="text-muted">
                            #{{ $e->id }} · {{ $e->created_at->format('d.m H:i') }}
                            @if($e->sent_at) · надіслано {{ $e->sent_at->format('d.m H:i') }} @endif
                        </small>
                    </div>
                    <div class="small text-break">
                        <b>{{ $e->to_email }}</b>
                        @if($e->contact_name) · {{ $e->contact_name }} @endif
                        · фід: {{ ['yes' => 'є', 'no' => 'немає', 'unknown' => '?'][$e->has_merchant_feed] ?? '?' }}
                        @if($e->reason)<br><span class="text-warning">{{ $e->reason }}</span>@endif
                        @if($e->last_error)<br><span class="text-danger">{{ $e->last_error }}</span>@endif
                    </div>

                    <div class="mt-2">
                        @if(in_array($e->status, ['review', 'failed']))
                            <form method="post" action="{{ route('admin.caller.emails.approve', $e->id) }}" class="form-inline d-inline-flex flex-wrap mb-1">
                                @csrf
                                <input type="email" name="to_email" value="{{ $e->to_email }}" class="form-control form-control-sm mr-1 mb-1" style="min-width:220px" required>
                                <button class="btn btn-sm btn-success mr-1 mb-1"><i class="mdi mdi-check"></i> Схвалити</button>
                            </form>
                        @endif
                        <a href="{{ route('admin.caller.emails.preview', $e->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary mr-1 mb-1"><i class="mdi mdi-eye"></i> Перегляд</a>
                        @if(in_array($e->status, ['review', 'pending', 'failed']))
                            <form method="post" action="{{ route('admin.caller.emails.skip', $e->id) }}" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-outline-danger mb-1"><i class="mdi mdi-close"></i> Скасувати</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-muted">Листів немає.</p>
            @endforelse
            {{ $emails->links() }}
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Відписка</h5>
            <p class="text-muted small">Хтось відповів «відписатися»? Вкажіть email або телефон — магазин потрапить у список «не дзвонити»,
                і йому більше не писатимуть і не телефонуватимуть.</p>
            <form method="post" action="{{ route('admin.caller.emails.unsubscribe') }}" class="form-inline flex-wrap">
                @csrf
                <input name="key" class="form-control mr-2 mb-2" placeholder="email або телефон" required style="min-width:240px">
                <button class="btn btn-outline-dark mb-2">Відписати</button>
            </form>
        </div>
    </div>
@endsection
