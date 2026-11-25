@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">AI-дзвінки: тренажер сценарію</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @include('admin.caller._nav')
    <style>
        .sim-chat { height: 55vh; min-height: 320px; overflow-y: auto; background: #f7f8fa; border-radius: 8px; padding: 12px; }
        .sim-msg { max-width: 85%; padding: 8px 12px; border-radius: 12px; margin-bottom: 8px; white-space: pre-wrap; word-break: break-word; }
        .sim-agent { background: #fff; border: 1px solid #e3e6ea; }
        .sim-user { background: #1e88e5; color: #fff; margin-left: auto; }
        .sim-who { font-size: 11px; opacity: .7; display: block; }
        @media (max-width: 575.98px) { .sim-msg { max-width: 95%; } .sim-chat { height: 50vh; } }
    </style>

    @unless($configured)
        <div class="alert alert-warning">Не задано <code>ANTHROPIC_API_KEY</code> у .env — тренажер не працюватиме.</div>
    @endunless

    <div class="row">
        <div class="col-lg-4 order-lg-2">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Нова розмова</h5>
                    <p class="text-muted small">Ви граєте власника магазину. Claude говорить за Марію з тим самим промптом, що й голосовий агент.
                        Спробуйте: «скільки коштує?», «ви робот?», «звідки мій номер?», «не дзвоніть», email по буквах, відповідь російською.</p>
                    <form method="post" action="{{ route('admin.caller.simulator.reset') }}">
                        @csrf
                        <input name="store_name" class="form-control mb-2" placeholder="Назва магазину *" required
                               value="{{ $state['vars']['store_name'] ?? 'Квітковий Дім' }}">
                        <input name="store_site" class="form-control mb-2" placeholder="Сайт" value="{{ $state['vars']['store_site'] ?? 'kvitkovyi-dim.com.ua' }}">
                        <input name="contact_name" class="form-control mb-2" placeholder="Ім'я контакту" value="{{ $state['vars']['contact_name'] ?? '' }}">
                        <input name="category" class="form-control mb-2" placeholder="Категорія" value="{{ $state['vars']['category'] ?? '' }}">
                        <button class="btn btn-primary btn-block"><i class="mdi mdi-phone-plus"></i> Почати дзвінок</button>
                    </form>
                </div>
            </div>
            <div class="card d-none" id="sim-analysis-card">
                <div class="card-body">
                    <h5 class="card-title">Що витягне аналітика</h5>
                    <pre id="sim-analysis" class="small mb-0" style="white-space:pre-wrap; word-break:break-word;"></pre>
                </div>
            </div>
        </div>

        <div class="col-lg-8 order-lg-1">
            <div class="card">
                <div class="card-body">
                    @if(!$state)
                        <p class="text-muted mb-0">Заповніть дані магазину й натисніть «Почати дзвінок».</p>
                    @else
                        <div class="sim-chat mb-2" id="sim-chat">
                            @foreach($state['transcript'] as $t)
                                <div class="sim-msg {{ $t['role'] === 'agent' ? 'sim-agent' : 'sim-user' }}">
                                    <span class="sim-who">{{ $t['role'] === 'agent' ? 'Марія' : 'Ви' }}</span>{{ $t['message'] }}
                                </div>
                            @endforeach
                        </div>
                        <form id="sim-form" class="d-flex">
                            <input id="sim-input" class="form-control mr-2" placeholder="Ваша репліка…" autocomplete="off" maxlength="1000" required>
                            <button class="btn btn-primary" id="sim-send"><i class="mdi mdi-send"></i></button>
                        </form>
                        <button type="button" class="btn btn-outline-secondary btn-sm mt-2" id="sim-end">
                            <i class="mdi mdi-phone-hangup"></i> Завершити й проаналізувати
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer-scripts')
    @if($state)
    <script>
        (function () {
            var chat = document.getElementById('sim-chat');
            var form = document.getElementById('sim-form');
            var input = document.getElementById('sim-input');
            var send = document.getElementById('sim-send');
            var token = '{{ csrf_token() }}';
            chat.scrollTop = chat.scrollHeight;

            function add(who, text, cls) {
                var d = document.createElement('div');
                d.className = 'sim-msg ' + cls;
                var w = document.createElement('span');
                w.className = 'sim-who';
                w.textContent = who;
                d.appendChild(w);
                d.appendChild(document.createTextNode(text));
                chat.appendChild(d);
                chat.scrollTop = chat.scrollHeight;
                return d;
            }

            function post(url, data) {
                return fetch(url, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json'},
                    body: JSON.stringify(data || {})
                }).then(function (r) { return r.json(); });
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var text = input.value.trim();
                if (!text) return;
                add('Ви', text, 'sim-user');
                input.value = '';
                send.disabled = true;
                var typing = add('Марія', '…', 'sim-agent');
                post('{{ route('admin.caller.simulator.message') }}', {text: text}).then(function (res) {
                    typing.remove();
                    add('Марія', res.reply || ('Помилка: ' + (res.error || 'невідома')), 'sim-agent');
                }).catch(function (err) {
                    typing.remove();
                    add('Система', 'Помилка мережі: ' + err, 'sim-agent');
                }).finally(function () { send.disabled = false; input.focus(); });
            });

            document.getElementById('sim-end').addEventListener('click', function () {
                var card = document.getElementById('sim-analysis-card');
                var out = document.getElementById('sim-analysis');
                card.classList.remove('d-none');
                out.textContent = 'Аналізую…';
                post('{{ route('admin.caller.simulator.analyze') }}').then(function (res) {
                    out.textContent = res.analysis ? JSON.stringify(res.analysis, null, 2) : ('Помилка: ' + res.error);
                    card.scrollIntoView({behavior: 'smooth'});
                });
            });
        })();
    </script>
    @endif
@endsection
