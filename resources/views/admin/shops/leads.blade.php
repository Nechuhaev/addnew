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
    @endphp
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        Інтернет-магазини, яких ви хочете запросити на addnew. Додавайте їх вручну (назва й сайт) —
                        контактний email система шукає на сайті <strong>самого магазину</strong> (головна й сторінки контактів).
                        «Надіслати лист» відкриває вже заповнене запрошення — перевірте й відредагуйте його, лист піде з сайту
                        (як листи магазинам в «Опис магазину») і збережеться в історії кандидата.
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
                                        [$subject, $body] = $lead->invitationLetter($sender);
                                        $existingShop = $lead->existingShop();
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
                                            @php($lastMessage = $lead->messages->first())
                                            @if($lead->email && !in_array($lead->status, ['joined', 'declined']) && !$existingShop)
                                                <button type="button" class="btn btn-sm btn-primary mb-1" onclick="var f=document.getElementById('lead-send-{{ $lead->id }}');f.style.display=f.style.display==='none'?'table-row':'none';">Надіслати лист</button>
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
                                    @if($lead->email && !in_array($lead->status, ['joined', 'declined']) && !$existingShop)
                                        <tr id="lead-send-{{ $lead->id }}" style="display:none;">
                                            <td colspan="4" style="border-top:0;">
                                                <form action="{{ route('admin.shops.leads.send', $lead->id) }}" method="post"
                                                      @if($lastMessage && $lastMessage->sent_successfully) onsubmit="return confirm('«{{ $lead->name }}» уже отримав лист {{ $lastMessage->created_at->format('d.m.Y') }}. Надіслати ще раз?');" @endif>
                                                    @csrf
                                                    <div class="form-group mb-2">
                                                        <label class="mb-1">Кому: <strong>{{ $lead->email }}</strong></label>
                                                        <input type="text" name="subject" value="{{ $subject }}" class="form-control" required>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <textarea name="body" rows="14" class="form-control" style="font-size:13px;" required>{{ $body }}</textarea>
                                                    </div>
                                                    <button class="btn btn-success">Надіслати</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endif
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
@endsection
