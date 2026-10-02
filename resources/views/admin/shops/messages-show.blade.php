@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Діалог: {{ $shop->username }} &harr; {{ optional($conversation->buyer)->username ?? 'видалений акаунт' }}</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.shops.messages', $shop->id) }}" class="btn btn-sm btn-secondary mb-3">&larr; До списку діалогів</a>

                    @if($conversation->ad)
                        <p><strong>Оголошення:</strong> <a href="{{ $conversation->ad->url }}" target="_blank">{{ $conversation->ad->name }}</a></p>
                    @endif

                    <div style="max-width:700px; display:flex; flex-direction:column; gap:10px; border:1px solid #e0e0e0; border-radius:8px; padding:16px; background:#fff;">
                        @forelse($messages as $m)
                            @php $isShop = $m->sender_id == $shop->id; @endphp
                            <div style="max-width:75%; padding:10px 14px; border-radius:14px; word-break:break-word;
                                        {{ $isShop ? 'align-self:flex-end; background:#dcf8c6;' : 'align-self:flex-start; background:#f1f0f0;' }}">
                                <div><strong>{{ optional($m->sender)->username ?? '—' }}:</strong> {{ $m->body }}</div>
                                <div style="font-size:11px; color:#888; margin-top:4px; text-align:right;">{{ $m->created_at->format('d.m.Y H:i') }}</div>
                            </div>
                        @empty
                            <p class="text-muted">Повідомлень немає.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
