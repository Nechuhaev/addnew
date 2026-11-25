@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-12 align-self-center">
                <h4 class="page-title">AI-дзвінки: налаштування</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @include('admin.caller._nav')

    <div class="row">
        <div class="col-xl-7">
            <form method="post" action="{{ route('admin.caller.settings.update') }}">
                @csrf
                @foreach($schema as $group => $fields)
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $group }}</h5>
                            @foreach($fields as $key => $def)
                                @php
                                    list($type, $label, $hint) = $def;
                                    $val = old($key, $values[$key]);
                                @endphp
                                @if($type === 'bool')
                                    <div class="form-check mb-3">
                                        <input type="hidden" name="{{ $key }}" value="0">
                                        <input type="checkbox" class="form-check-input" id="s-{{ $key }}" name="{{ $key }}" value="1" {{ $val ? 'checked' : '' }}>
                                        <label class="form-check-label font-weight-bold" for="s-{{ $key }}">{{ $label }}</label>
                                        @if($hint)<small class="form-text text-muted">{{ $hint }}</small>@endif
                                    </div>
                                @elseif($type === 'days')
                                    <div class="form-group">
                                        <label class="d-block">{{ $label }}</label>
                                        @foreach([1 => 'Пн', 2 => 'Вт', 3 => 'Ср', 4 => 'Чт', 5 => 'Пт', 6 => 'Сб', 7 => 'Нд'] as $d => $dl)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="call_days[]" id="d{{ $d }}" value="{{ $d }}"
                                                    {{ in_array($d, (array) $val) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="d{{ $d }}">{{ $dl }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label for="s-{{ $key }}">{{ $label }}</label>
                                        <input id="s-{{ $key }}" name="{{ $key }}" class="form-control"
                                               type="{{ $type === 'int' ? 'number' : ($type === 'email' ? 'email' : 'text') }}"
                                               @if($type === 'int') min="{{ $def[3] }}" max="{{ $def[4] }}" inputmode="numeric" @endif
                                               value="{{ $val }}">
                                        @if($hint)<small class="form-text text-muted">{{ $hint }}</small>@endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <button class="btn btn-primary mb-4 btn-block-xs"><i class="mdi mdi-content-save"></i> Зберегти</button>
            </form>
        </div>

        <div class="col-xl-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Ключі та інтеграції (.env)</h5>
                    <p class="text-muted small">Секрети зберігаються лише в <code>.env</code> на сервері й тут не показуються.
                        Після зміни <code>.env</code>: <code>php7.4 artisan config:clear</code>.</p>
                    <ul class="list-unstyled mb-3">
                        @foreach($health as $k => $ok)
                            <li class="mb-1">
                                <i class="mdi {{ $ok ? 'mdi-check-circle text-success' : 'mdi-alert-circle text-danger' }}"></i>
                                <code>{{ $k }}</code> {{ $ok ? 'задано' : 'не задано' }}
                            </li>
                        @endforeach
                    </ul>
                    <dl class="row small mb-0">
                        <dt class="col-sm-5">Модель аналізу</dt><dd class="col-sm-7"><code>{{ $model }}</code> (CALLER_CLAUDE_MODEL)</dd>
                        <dt class="col-sm-5">URL вебхука</dt><dd class="col-sm-7"><code class="text-break">{{ $webhookUrl }}</code></dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Промпт для агента ElevenLabs</h5>
                    <p class="text-muted small">Скопіюйте в налаштування агента (Agents → ваш агент). Файли: <code>resources/caller/</code>.
                        Перш ніж змінювати, перевірте зміни в <a href="{{ route('admin.caller.simulator') }}">тренажері</a>.</p>

                    <label class="small font-weight-bold">First message</label>
                    <textarea class="form-control mb-2" rows="3" readonly id="first-message">{{ $firstMessage }}</textarea>
                    <button type="button" class="btn btn-sm btn-outline-secondary mb-3 js-copy" data-target="first-message"><i class="mdi mdi-content-copy"></i> Копіювати</button>

                    <label class="small font-weight-bold d-block">System prompt</label>
                    <textarea class="form-control mb-2" rows="12" readonly id="system-prompt" style="font-size:12px">{{ $systemPrompt }}</textarea>
                    <button type="button" class="btn btn-sm btn-outline-secondary js-copy" data-target="system-prompt"><i class="mdi mdi-content-copy"></i> Копіювати</button>

                    <p class="small text-muted mt-3 mb-0">Dynamic variables агента: <code>store_name</code>, <code>store_site</code>, <code>contact_name</code>, <code>category</code>.</p>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer-scripts')
    <script>
        document.querySelectorAll('.js-copy').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var el = document.getElementById(btn.dataset.target);
                el.select();
                try { navigator.clipboard.writeText(el.value); } catch (e) { document.execCommand('copy'); }
                btn.innerHTML = '<i class="mdi mdi-check"></i> Скопійовано';
            });
        });
    </script>
@endsection
