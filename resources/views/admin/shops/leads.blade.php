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
                        «Написати» відкриває лист-запрошення у вашій пошті: перевірте й відредагуйте його перед відправкою.
                        Пишіть невеликими порціями (10–20 на день) і не надсилайте повторно тим, хто відмовився.
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
                                        <td class="text-right" style="white-space:nowrap;">
                                            @if($lead->email && !in_array($lead->status, ['joined', 'declined']) && !$existingShop)
                                                <a href="mailto:{{ $lead->email }}?subject={{ rawurlencode($subject) }}&body={{ rawurlencode($body) }}" class="btn btn-sm btn-primary">Написати</a>
                                            @endif
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="var t=this.nextElementSibling;t.style.display=t.style.display==='none'?'block':'none';">Текст листа</button>
                                            <textarea readonly class="form-control mt-1" rows="10" style="display:none;min-width:360px;font-size:12px;">{{ $subject }}

{{ $body }}</textarea>
                                            <form action="{{ route('admin.shops.leads.destroy', $lead->id) }}" method="post" style="display:inline;" onsubmit="return confirm('Видалити «{{ $lead->name }}» зі списку?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger">&times;</button>
                                            </form>
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
@endsection
