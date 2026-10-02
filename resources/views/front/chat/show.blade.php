@extends('front.layout')

@section('meta_title', 'Діалог з ' . (optional($other)->username ?? 'користувачем'))

@section('content')
<main class="category-page chat-page">
    <div class="container">
        <a href="{{ route('chat.index') }}" style="display:inline-block; margin-bottom:12px;">&larr; До всіх діалогів</a>

        <div class="chat-window" style="border:1px solid #e0e0e0; border-radius:8px; overflow:hidden; display:flex; flex-direction:column; height:65vh; max-height:700px;">
            <div class="chat-window__header" style="padding:12px 16px; border-bottom:1px solid #e0e0e0; background:#f9f9f9; display:flex; align-items:center; gap:10px; flex-shrink:0;">
                <img src="{{ optional($other)->image ?? asset('assets/front/img/placeholder.png') }}" alt="" style="width:36px; height:36px; border-radius:50%; object-fit:cover;">
                <strong>
                    @if($other)
                        <a href="{{ route('author', $other->id) }}" style="color:inherit;">{{ $other->username }}</a>
                    @else
                        Користувач
                    @endif
                </strong>
            </div>

            <div id="chat-messages" class="chat-window__messages" style="flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; gap:10px; background:#fff;">
                @foreach($messages as $m)
                    @php $isMine = $m->sender_id == auth()->id(); @endphp
                    <div class="chat-bubble {{ $isMine ? 'chat-bubble--mine' : '' }}"
                         style="max-width:75%; padding:10px 14px; border-radius:14px; word-break:break-word;
                                {{ $isMine ? 'align-self:flex-end; background:#dcf8c6;' : 'align-self:flex-start; background:#f1f0f0;' }}">
                        <div>{{ $m->body }}</div>
                        <div style="font-size:11px; color:#888; margin-top:4px; text-align:right;">{{ $m->created_at->format('H:i') }}</div>
                    </div>
                @endforeach
            </div>

            <form id="chat-form" style="display:flex; gap:8px; padding:12px; border-top:1px solid #e0e0e0; background:#f9f9f9; flex-shrink:0;">
                @csrf
                <textarea id="chat-input" rows="1" placeholder="Напишіть повідомлення..."
                          style="flex:1; resize:none; border:1px solid #ddd; border-radius:20px; padding:10px 16px; font-size:15px; box-sizing:border-box;"></textarea>
                <button type="submit" class="btn btn-success" style="border-radius:20px; padding:0 20px; flex-shrink:0;">Надіслати</button>
            </form>
        </div>
    </div>
</main>

<style>
    /* Мобільна адаптація: чат на весь екран, інпут завжди видний знизу */
    @media (max-width: 768px) {
        .chat-page .chat-window {
            height: calc(100vh - 160px) !important;
            max-height: none !important;
            border-radius: 0 !important;
            border-left: none !important;
            border-right: none !important;
            margin: 0 -15px;
        }
        .chat-bubble { max-width: 85% !important; }
        #chat-input { font-size: 16px; } /* 16px — щоб iOS Safari не зумив при фокусі */
    }
</style>

<script>
(function () {
    var conversationId = {{ $conversation->id }};
    var currentUserId = {{ auth()->id() }};
    var pollUrl = '{{ route('chat.poll', $conversation->id) }}';
    var sendUrl = '{{ route('chat.send', $conversation->id) }}';
    var csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').content
        : '{{ csrf_token() }}';

    var messagesBox = document.getElementById('chat-messages');
    var form = document.getElementById('chat-form');
    var input = document.getElementById('chat-input');

    var lastId = 0;
    var allBubbles = messagesBox.querySelectorAll('.chat-bubble');
    // Визначаємо lastId з уже відрендерених повідомлень (з сервера)
    @if($messages->isNotEmpty())
        lastId = {{ $messages->last()->id }};
    @endif

    function scrollToBottom() {
        messagesBox.scrollTop = messagesBox.scrollHeight;
    }
    scrollToBottom();

    function appendMessage(m) {
        var div = document.createElement('div');
        div.className = 'chat-bubble' + (m.is_mine ? ' chat-bubble--mine' : '');
        div.style.cssText = 'max-width:75%; padding:10px 14px; border-radius:14px; word-break:break-word;' +
            (m.is_mine ? 'align-self:flex-end; background:#dcf8c6;' : 'align-self:flex-start; background:#f1f0f0;');

        var textDiv = document.createElement('div');
        textDiv.textContent = m.body;
        div.appendChild(textDiv);

        var timeDiv = document.createElement('div');
        timeDiv.style.cssText = 'font-size:11px; color:#888; margin-top:4px; text-align:right;';
        timeDiv.textContent = m.created_at;
        div.appendChild(timeDiv);

        messagesBox.appendChild(div);
    }

    function poll() {
        fetch(pollUrl + '?after_id=' + lastId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.length > 0) {
                    data.forEach(function (m) {
                        appendMessage(m);
                        lastId = m.id;
                    });
                    scrollToBottom();
                }
            })
            .catch(function () {});
    }

    setInterval(poll, 4000);

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var text = input.value.trim();
        if (!text) return;

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ body: text })
        })
            .then(function (r) { return r.json(); })
            .then(function (m) {
                appendMessage({
                    id: m.id,
                    body: m.body,
                    is_mine: true,
                    created_at: m.created_at
                });
                lastId = m.id;
                input.value = '';
                scrollToBottom();
            })
            .catch(function () {});
    });

    // Enter надсилає, Shift+Enter — новий рядок
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });
})();
</script>
@endsection
