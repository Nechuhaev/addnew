@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title">Кандидати в магазини</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @php
        $sender = Auth::user()->firstname ?: 'команда addnew.biz';
        $sendable = [];
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        Інтернет-магазини, яких ви хочете запросити на addnew. Додавайте їх вручну (назва й сайт) —
                        контактний email система шукає на сайті <strong>самого магазину</strong> (головна й сторінки контактів).
                        «Надіслати лист» відкриває форму з шаблоном «{{ optional($defaultTemplate)->name ?? 'Запрошення для кандидатів' }}»
                        (його текст редагується в <a href="{{ route('admin.shopMessageTemplates') }}">Шаблони листів</a>) — перевірте лист,
                        він піде з сайту (як листи магазинам в «Опис магазину») і збережеться в історії кандидата.
                        Надсилайте невеликими порціями (10–20 на день) і не пишіть повторно тим, хто відмовився.
                    </p>

                    <h5>Додати магазин</h5>
                    <form action="{{ route('admin.shops.leads.store') }}" method="post" class="form-inline mb-4">
                        @csrf
                        <input type="text" name="name" value="{{ old('name') }}" class="form-control mr-2 mb-2" placeholder="Назва магазину" required>
                        <input type="text" name="site_url" value="{{ old('site_url') }}" class="form-control mr-2 mb-2" placeholder="Сайт: shop.com.ua" style="min-width:220px;" required>
                        <input type="text" name="category" value="{{ old('category') }}" class="form-control mr-2 mb-2" placeholder="Категорія (для листа)">
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control mr-2 mb-2" placeholder="Email (необов'язково)">
                        <button class="btn btn-success mb-2">Додати</button>
                    </form>

                    <div id="lead-send-card" class="card border mb-4" style="display:none;">
                        <div class="card-body">
                            <h5>Лист кандидату: <span id="lead-send-name"></span></h5>
                            <form id="lead-send-form" method="post">
                                @csrf
                                <p class="mb-2">Кому: <strong id="lead-send-email"></strong></p>
                                <div class="form-group">
                                    <label>Шаблон</label>
                                    <select id="lead-template-select" class="form-control">
                                        <option value="">— Без шаблону —</option>
                                        @foreach($messageTemplates as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="subject">Тема</label>
                                    <input type="text" name="subject" id="subject" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="body">Текст листа</label>
                                    <textarea name="body" id="body" class="form-control content" rows="12"></textarea>
                                </div>
                                <button type="submit" class="btn btn-success">Надіслати</button>
                                <button type="button" class="btn btn-link" onclick="document.getElementById('lead-send-card').style.display='none';">Скасувати</button>
                            </form>
                        </div>
                    </div>

                    <div class="mb-3">
                        <a href="{{ route('admin.shops.leads') }}" class="btn btn-sm {{ !$status ? 'btn-primary' : 'btn-outline-secondary' }}">Усі ({{ $counts->sum() }})</a>
                        @foreach(\App\ShopLead::STATUSES as $key => $label)
                            <a href="{{ route('admin.shops.leads', ['status' => $key]) }}"
                               class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }} ({{ $counts[$key] ?? 0 }})</a>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Магазин</th>
                                    <th>Email</th>
                                    <th>Статус і примітка</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    @php
                                        $existingShop = $lead->existingShop();
                                        $lastMessage = $lead->messages->first();
                                        $canSend = $lead->email && !in_array($lead->status, ['joined', 'declined']) && !$existingShop;
                                        if ($canSend) {
                                            $sendable[$lead->id] = [
                                                'name' => $lead->name,
                                                'email' => $lead->email,
                                                'action' => route('admin.shops.leads.send', $lead->id),
                                                'vars' => $lead->templateVars($sender),
                                                'last_sent' => ($lastMessage && $lastMessage->sent_successfully) ? $lastMessage->created_at->format('d.m.Y') : null,
                                            ];
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $lead->name }}</strong>
                                            @if($lead->category)<div class="text-muted" style="font-size:12px;">{{ $lead->category }}</div>@endif
                                            <a href="{{ $lead->site_url }}" target="_blank" rel="noopener noreferrer nofollow">{{ $lead->domain }} &#8599;</a>
                                            @if($existingShop)
                                                <div><a href="{{ route('admin.shops.products', $existingShop->id) }}" class="badge badge-success">уже на addnew (ID {{ $existingShop->id }})</a></div>
                                            @endif
                                        </td>
                                        <td style="font-size:13px;">
                                            @if($lead->email)
                                                {{ $lead->email }}
                                                <div class="text-muted" style="font-size:11px;">{{ $lead->email_source === 'site' ? 'знайдено на сайті' : 'вказано вручну' }}</div>
                                            @else
                                                <span class="text-muted">{{ $lead->email_lookup_status === 'unreachable' ? 'сайт не відповідає' : 'не знайдено' }}</span>
                                            @endif
                                            <form action="{{ route('admin.shops.leads.lookup', $lead->id) }}" method="post" style="display:inline;">
                                                @csrf
                                                <button class="btn btn-link btn-sm p-0">шукати на сайті</button>
                                            </form>
                                        </td>
                                        <td style="min-width:280px;">
                                            <form action="{{ route('admin.shops.leads.update', $lead->id) }}" method="post">
                                                @csrf
                                                <input type="hidden" name="return_status" value="{{ $status }}">
                                                <div class="d-flex mb-1">
                                                    <select name="status" class="form-control form-control-sm mr-1">
                                                        @foreach(\App\ShopLead::STATUSES as $key => $label)
                                                            <option value="{{ $key }}" {{ $lead->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <input type="email" name="email" value="{{ $lead->email }}" class="form-control form-control-sm mr-1" placeholder="email">
                                                </div>
                                                <div class="d-flex">
                                                    <input type="text" name="note" value="{{ $lead->note }}" class="form-control form-control-sm mr-1" placeholder="Примітка">
                                                    <button class="btn btn-sm btn-secondary">Зберегти</button>
                                                </div>
                                            </form>
                                            <span class="badge badge-{{ \App\ShopLead::STATUS_BADGES[$lead->status] ?? 'light' }}">{{ \App\ShopLead::STATUSES[$lead->status] ?? $lead->status }}</span>
                                            @if($lead->contacted_at)
                                                <span class="text-muted" style="font-size:11px;">написали {{ $lead->contacted_at->format('d.m.Y') }}</span>
                                            @endif
                                        </td>
                                        <td class="text-right" style="min-width:150px;">
                                            @if($canSend)
                                                <button type="button" class="btn btn-sm btn-primary mb-1" onclick="openLeadSend({{ $lead->id }});">Надіслати лист</button>
                                            @endif
                                            <form action="{{ route('admin.shops.leads.destroy', $lead->id) }}" method="post" style="display:inline;" onsubmit="return confirm('Видалити «{{ $lead->name }}» зі списку?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger mb-1">&times;</button>
                                            </form>
                                            @if($lastMessage)
                                                <div style="font-size:11px;" class="{{ $lastMessage->sent_successfully ? 'text-success' : 'text-danger' }}"
                                                     title="{{ $lastMessage->error_message ?: $lastMessage->subject }}">
                                                    {{ $lastMessage->sent_successfully ? 'надіслано' : 'помилка' }} {{ $lastMessage->created_at->format('d.m.Y H:i') }}
                                                    @if($lead->messages->count() > 1) (усього {{ $lead->messages->count() }}) @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">Поки порожньо — додайте перший магазин формою вище.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $leads->links() }}
                </div>
            </div>
        </div>
    </div>
<script>
var leadTemplates = {!! $messageTemplates->map(function ($t) {
    return ['id' => $t->id, 'subject' => $t->subject, 'body' => $t->body];
})->values()->toJson() !!};
var leadSendable = {!! json_encode($sendable, JSON_UNESCAPED_UNICODE) !!};
var leadDefaultTemplateId = {!! json_encode(optional($defaultTemplate)->id) !!};
var currentLead = null;

function fillTemplate(text, vars) {
    Object.keys(vars).forEach(function (key) {
        text = text.replace(new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g'), vars[key]);
    });
    return text;
}

function applyLeadTemplate(id) {
    if (!currentLead) return;
    var tpl = leadTemplates.filter(function (t) { return t.id == id; })[0];
    if (!tpl) return;
    document.getElementById('subject').value = fillTemplate(tpl.subject, currentLead.vars);
    var body = fillTemplate(tpl.body, currentLead.vars);
    if (typeof tinymce !== 'undefined' && tinymce.get('body')) {
        tinymce.get('body').setContent(body);
    } else {
        document.getElementById('body').value = body;
    }
}

function openLeadSend(id) {
    currentLead = leadSendable[id];
    if (!currentLead) return;
    document.getElementById('lead-send-form').action = currentLead.action;
    document.getElementById('lead-send-name').textContent = currentLead.name;
    document.getElementById('lead-send-email').textContent = currentLead.email;
    var select = document.getElementById('lead-template-select');
    select.value = leadDefaultTemplateId || '';
    applyLeadTemplate(select.value);
    var card = document.getElementById('lead-send-card');
    card.style.display = 'block';
    card.scrollIntoView({ behavior: 'smooth' });
}

// Повернення після 419: відкрити форму того ж кандидата з уже набраним текстом
@if(session('reopen_lead') && old('subject') !== null)
document.addEventListener('DOMContentLoaded', function () {
    openLeadSend({{ (int) session('reopen_lead') }});
    document.getElementById('lead-template-select').value = '';
    document.getElementById('subject').value = {!! json_encode(old('subject'), JSON_UNESCAPED_UNICODE) !!};
    var oldBody = {!! json_encode(old('body'), JSON_UNESCAPED_UNICODE) !!};
    var setBody = function () {
        if (typeof tinymce !== 'undefined' && tinymce.get('body')) {
            tinymce.get('body').setContent(oldBody);
        } else {
            document.getElementById('body').value = oldBody;
        }
    };
    setBody();
    setTimeout(setBody, 1500); // TinyMCE може ініціалізуватись пізніше
});
@endif

document.getElementById('lead-template-select').addEventListener('change', function () {
    applyLeadTemplate(this.value);
});

document.getElementById('lead-send-form').addEventListener('submit', function (e) {
    if (typeof tinymce !== 'undefined' && tinymce.get('body')) {
        tinymce.triggerSave();
    }
    if (currentLead && currentLead.last_sent && !confirm('«' + currentLead.name + '» уже отримав лист ' + currentLead.last_sent + '. Надіслати ще раз?')) {
        e.preventDefault();
    }
});
</script>
@endsection
