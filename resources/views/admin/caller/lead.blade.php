@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title text-break">{{ $lead->store_name }}</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @include('admin.caller._nav')

    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title d-flex justify-content-between flex-wrap">
                        <span>Лід</span>
                        <span class="badge badge-{{ $lead->statusBadge() }}">{{ $lead->statusLabel() }}</span>
                    </h5>
                    <dl class="row mb-0">
                        <dt class="col-5">Телефон</dt><dd class="col-7"><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></dd>
                        <dt class="col-5">Сайт</dt>
                        <dd class="col-7 text-break">
                            @if($lead->store_site)
                                <a href="{{ preg_match('#^https?://#', $lead->store_site) ? $lead->store_site : 'https://' . $lead->store_site }}" target="_blank" rel="noopener">{{ $lead->store_site }}</a>
                            @else — @endif
                        </dd>
                        <dt class="col-5">Контакт</dt><dd class="col-7">{{ $lead->contact_name ?: '—' }}</dd>
                        <dt class="col-5">Email</dt><dd class="col-7 text-break">{{ $lead->email ?: '—' }}</dd>
                        <dt class="col-5">Фід Merchant</dt><dd class="col-7">{{ ['yes' => 'є', 'no' => 'немає', 'unknown' => 'невідомо'][$lead->has_merchant_feed] ?? '—' }}</dd>
                        <dt class="col-5">Результат</dt><dd class="col-7">{{ $lead->outcomeLabel() }}</dd>
                        <dt class="col-5">Спроб</dt><dd class="col-7">{{ $lead->attempts }}</dd>
                        <dt class="col-5">Наступний дзвінок</dt><dd class="col-7">{{ $lead->next_call_at ? $lead->next_call_at->format('d.m.Y H:i') : '—' }}</dd>
                        <dt class="col-5">На сайті</dt>
                        <dd class="col-7">
                            @if($lead->registeredUser)
                                <a href="{{ route('admin.shops.edit', $lead->registered_user_id) }}">зареєструвався {{ optional($lead->registered_at)->format('d.m.Y') }}</a>
                            @else ні @endif
                        </dd>
                    </dl>
                    @if($lead->notes)
                        <div class="alert alert-light mt-3 mb-0 small">{{ $lead->notes }}</div>
                    @endif
                    @if($dnc)
                        <div class="alert alert-dark mt-3 mb-0 small">У списку «не дзвонити» з {{ $dnc->created_at->format('d.m.Y') }}: {{ $dnc->reason }}</div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Дії</h5>
                    @unless($dnc)
                        <form method="post" action="{{ route('admin.caller.leads.callNow', $lead->id) }}" class="d-inline-block mb-2"
                              onsubmit="return confirm('Подзвонити на {{ $lead->phone }} зараз? (у тестовому режимі дзвінка не буде)')">
                            @csrf
                            <button class="btn btn-success btn-sm btn-block-xs"><i class="mdi mdi-phone"></i> Подзвонити зараз</button>
                        </form>
                        <form method="post" action="{{ route('admin.caller.leads.requeue', $lead->id) }}" class="d-inline-block mb-2">
                            @csrf
                            <button class="btn btn-outline-primary btn-sm btn-block-xs"><i class="mdi mdi-replay"></i> Повернути в чергу</button>
                        </form>
                        <form method="post" action="{{ route('admin.caller.leads.dnc', $lead->id) }}" class="d-inline-block mb-2"
                              onsubmit="return confirm('Більше ніколи не дзвонити й не писати цьому магазину?')">
                            @csrf
                            <button class="btn btn-outline-dark btn-sm btn-block-xs"><i class="mdi mdi-phone-off"></i> Не дзвонити</button>
                        </form>
                    @endunless
                    <form method="post" action="{{ route('admin.caller.leads.destroy', $lead->id) }}" class="d-inline-block mb-2"
                          onsubmit="return confirm('Видалити лід разом з історією?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm btn-block-xs"><i class="mdi mdi-delete"></i> Видалити</button>
                    </form>
                </div>
            </div>

            @if($lead->emails->isNotEmpty())
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Листи</h5>
                        @foreach($lead->emails as $e)
                            <div class="d-flex justify-content-between flex-wrap border-bottom py-1">
                                <span class="text-break">{{ $e->to_email }}</span>
                                <span>
                                    <span class="badge badge-{{ $e->statusBadge() }}">{{ \App\Models\Caller\InstructionEmail::STATUSES[$e->status] ?? $e->status }}</span>
                                    <a href="{{ route('admin.caller.emails.preview', $e->id) }}" target="_blank" class="small">перегляд</a>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Дзвінки ({{ $lead->calls->count() }})</h5>
                    @forelse($lead->calls as $call)
                        @php $a = $call->analysis(); @endphp
                        <div class="border rounded p-2 mb-3">
                            <div class="d-flex justify-content-between flex-wrap">
                                <b>{{ optional($call->started_at)->format('d.m.Y H:i') }}</b>
                                <span class="small text-muted">
                                    {{ $call->duration_secs ? gmdate('i:s', $call->duration_secs) : '' }}
                                    · {{ $call->outcome ? (\App\Models\Caller\Lead::OUTCOMES[$call->outcome] ?? $call->outcome) : $call->status }}
                                </span>
                            </div>
                            @if($a)
                                <p class="mb-1 mt-1">{{ $a['summary'] ?? '' }}</p>
                                @if(!empty($a['next_action']))<p class="mb-1 small"><b>Далі:</b> {{ $a['next_action'] }}</p>@endif
                                @if(!empty($a['objections']))<p class="mb-1 small"><b>Заперечення:</b> {{ implode('; ', $a['objections']) }}</p>@endif
                                @if(!empty($a['quality_flags']))<p class="mb-1 small text-danger"><b>Якість:</b> {{ implode('; ', $a['quality_flags']) }}</p>@endif
                            @endif
                            @if($call->last_error)
                                <p class="mb-1 small text-danger">{{ $call->last_error }}</p>
                            @endif
                            @if($call->transcript())
                                <details class="mt-1">
                                    <summary class="small">Транскрипт</summary>
                                    <div class="small mt-2" style="max-height:420px; overflow-y:auto;">
                                        @foreach($call->transcript() as $t)
                                            @if(!empty($t['message']))
                                                <p class="mb-1"><b class="{{ ($t['role'] ?? '') === 'agent' ? 'text-primary' : 'text-dark' }}">{{ ($t['role'] ?? '') === 'agent' ? 'Марія' : 'Співрозмовник' }}:</b> {{ $t['message'] }}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                </details>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Дзвінків ще не було.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
