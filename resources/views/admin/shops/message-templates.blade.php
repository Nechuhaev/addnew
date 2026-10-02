@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Чат: {{ $shop->username }}</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.shops.edit', $shop->id) }}" class="btn btn-sm btn-secondary mb-3">&larr; До магазину</a>

                    @if($conversations->isEmpty())
                        <p>У цього магазину поки немає жодного діалогу.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Покупець</th>
                                        <th>Оголошення</th>
                                        <th>Повідомлень</th>
                                        <th>Останнє повідомлення</th>
                                        <th>Дата</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($conversations as $c)
                                        <tr>
                                            <td>
                                                {{ optional($c->buyer)->username ?? '— (видалений акаунт)' }}
                                                @if($c->buyer_user_id)
                                                    <br><small class="text-muted">ID={{ $c->buyer_user_id }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($c->ad)
                                                    <a href="{{ $c->ad->url }}" target="_blank">{{ \Illuminate\Support\Str::limit($c->ad->name, 40) }}</a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $c->messagesCount }}</td>
                                            <td>{{ $c->lastMessage ? \Illuminate\Support\Str::limit($c->lastMessage->body, 60) : '—' }}</td>
                                            <td style="white-space: nowrap;">{{ $c->last_message_at ? $c->last_message_at->format('d.m.Y H:i') : '—' }}</td>
                                            <td>
                                                <a href="{{ route('admin.shops.messages.show', [$shop->id, $c->id]) }}" class="btn btn-sm btn-primary">Переглянути</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $conversations->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
