@extends('front.layout')

@section('meta_title', 'Мої повідомлення')

@section('content')
<main class="category-page">
    <div class="container">
        <h1 style="font-size: 22px; margin-bottom: 20px;">Мої повідомлення</h1>

        @if($conversations->isEmpty())
            <p>У вас поки немає жодного діалогу.</p>
        @else
            <div class="chat-list">
                @foreach($conversations as $c)
                    <a href="{{ route('chat.show', $c->id) }}" class="chat-list__item" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom:1px solid #eee; text-decoration:none; color:inherit;">
                        <img src="{{ optional($c->other)->image ?? asset('assets/front/img/placeholder.png') }}" alt="" style="width:48px; height:48px; border-radius:50%; object-fit:cover; flex-shrink:0;">
                        <div style="flex:1; min-width:0;">
                            <div style="display:flex; justify-content:space-between; align-items:baseline; gap:8px;">
                                <strong style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ optional($c->other)->username ?? 'Користувач' }}</strong>
                                @if($c->lastMessage)
                                    <span style="font-size:12px; color:#999; flex-shrink:0;">{{ $c->lastMessage->created_at->format('d.m H:i') }}</span>
                                @endif
                            </div>
                            <div style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; color:#666; font-size:14px; margin-top:2px;">
                                {{ $c->lastMessage ? \Illuminate\Support\Str::limit($c->lastMessage->body, 60) : 'Немає повідомлень' }}
                            </div>
                        </div>
                        @if($c->unread > 0)
                            <span style="background:#28a745; color:#fff; border-radius:12px; min-width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; padding:0 6px; flex-shrink:0;">{{ $c->unread }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</main>

<style>
    /* Мобільна адаптація: трохи більші touch-зони, менші відступи контейнера */
    @media (max-width: 768px) {
        .chat-list__item { padding: 16px 8px !important; }
        .chat-list__item img { width: 44px !important; height: 44px !important; }
    }
</style>
@endsection
