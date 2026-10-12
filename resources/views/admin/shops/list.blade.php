@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Магазини</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    @if (Breadcrumbs::exists('admin.shops'))
                        {{ Breadcrumbs::render('admin.shops') }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('admin.shops') }}" method="get" class="form-inline mb-3">
                        <input type="text" name="search" class="form-control mr-2" style="min-width: 300px;"
                               placeholder="Пошук за назвою, email, телефоном, країною, ID"
                               value="{{ $search }}">
                        <button class="btn btn-secondary">Знайти</button>
                        @if($search)
                            <a href="{{ route('admin.shops') }}" class="btn btn-link">Скинути</a>
                        @endif
                    </form>

                    <a href="{{ route('admin.shops.leads') }}" class="btn btn-sm btn-secondary mb-3">Кандидати в магазини</a>
                    <a href="{{ route('admin.shops.competitorMonitor') }}" class="btn btn-sm btn-secondary mb-3">Моніторинг конкурентів</a>
                    <a href="{{ route('admin.shops.skippedDomains') }}" class="btn btn-sm btn-secondary mb-3">Виключені домени (моніторинг)</a>
                    <a href="{{ route('admin.shops.stats') }}" class="btn btn-sm btn-secondary mb-3">Статистика магазинів</a>
                    <a href="{{ route('admin.shops.feeds') }}" class="btn btn-sm btn-secondary mb-3">Фіди (автооновлення)</a>

                    <style>
                        .shops-table { table-layout: auto; width: 100%; }
                        .shops-table td, .shops-table th { vertical-align: top; padding: .55rem .5rem; }
                        .shops-table .st-name { min-width: 140px; }
                        .shops-table .st-contacts { min-width: 170px; word-break: break-word; overflow-wrap: anywhere; }
                        .shops-table .st-sub { display: block; font-size: 12px; color: #6c757d; overflow-wrap: anywhere; }
                        .shops-table .st-actions { width: 1%; }
                        .shops-table .st-actions .st-btns { display: flex; flex-direction: column; gap: 4px; min-width: 120px; }
                        .shops-table .st-actions .btn, .shops-table .st-actions form, .shops-table .st-actions form .btn { width: 100%; }
                        .shops-table .st-actions form { margin: 0; }
                        @media (max-width: 991px) {
                            .shops-table thead { display: none; }
                            .shops-table, .shops-table tbody, .shops-table tr, .shops-table td { display: block; width: 100%; }
                            .shops-table tr { border: 1px solid #e9ecef; border-radius: 6px; margin-bottom: 12px; padding: 6px 4px; }
                            .shops-table td { border: 0; padding: 4px 8px; }
                            .shops-table td[data-label]::before { content: attr(data-label) ": "; color: #6c757d; font-size: 12px; }
                            .shops-table .st-actions { width: 100%; }
                            .shops-table .st-actions .st-btns { flex-direction: row; flex-wrap: wrap; }
                            .shops-table .st-actions .btn, .shops-table .st-actions form { width: auto; }
                            .shops-table .st-actions form .btn { width: auto; }
                        }
                    </style>
                    <table class="table shops-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Магазин / домен</th>
                                <th>Email / телефон</th>
                                <th>Товарів</th>
                                <th>Країна</th>
                                <th>Реєстрація / запрошення</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($shops as $shop)
                                <tr>
                                    <td data-label="ID">{{ $shop->id }}</td>
                                    <td class="st-name">
                                        <strong>{{ $shop->username }}</strong>
                                        @if($shop->site_url)
                                            <a href="{{ $shop->site_url }}" target="_blank" rel="noopener noreferrer" class="st-sub">
                                                {{ parse_url($shop->site_url, PHP_URL_HOST) ?: $shop->site_url }}
                                            </a>
                                        @else
                                            <span class="st-sub">—</span>
                                        @endif
                                    </td>
                                    <td class="st-contacts">
                                        @if($shop->email_check_status === 'domain_unreachable')
                                            <span class="text-muted">{{ $shop->email }}</span>
                                            <span class="badge badge-secondary" title="Сайт магазину не відповідає (DNS-помилка, timeout або відмова з'єднання) під час останньої перевірки">
                                                домен недоступний
                                            </span>
                                        @elseif($shop->email_matched === true)
                                            <span class="text-success">{{ $shop->email }}</span>
                                        @elseif($shop->email_matched === false)
                                            <span class="text-warning">{{ $shop->email }}</span>
                                            <span class="text-warning" role="img" aria-label="Email автоматично оновлено" title="Email автоматично оновлено після звірки з сайтом магазину" style="cursor:help;">&#9888;</span>
                                        @else
                                            {{ $shop->email }}
                                        @endif
                                        <span class="st-sub" style="white-space: nowrap;">{{ $shop->telephone ?? '—' }}</span>
                                    </td>
                                    <td data-label="Товарів">{{ $shop->products_count }}</td>
                                    <td data-label="Країна">{{ $shop->countries_display }}</td>
                                    <td data-label="Реєстрація">
                                        <span style="white-space: nowrap;">{{ $shop->created_date }}</span>
                                        <span class="st-sub">
                                            @if($shop->invite_sent_at)
                                                <span class="badge badge-success" title="Дата останнього надісланого запрошення">
                                                    запрошення {{ \Illuminate\Support\Carbon::parse($shop->invite_sent_at)->format('d.m.Y') }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">запрошення не надіслано</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="st-actions">
                                        <div class="st-btns">
                                            <a href="{{ route('admin.shops.edit', $shop->id) }}" class="btn btn-sm btn-secondary">Опис</a>
                                            <a href="{{ route('admin.shops.products', $shop->id) }}" class="btn btn-sm btn-secondary">Товари</a>
                                            <a href="{{ route('admin.shops.impersonate', $shop->id) }}" class="btn btn-sm btn-warning"
                                               onclick="return confirm('Увійти в акаунт магазину «{{ $shop->username }}»?');">
                                                Увійти як магазин
                                            </a>
                                            <form action="{{ route('admin.shops.destroy', $shop->id) }}" method="post"
                                                  onsubmit="return confirm('УВАГА: буде видалено акаунт магазину «{{ $shop->username }}» і ВСІ його товари ({{ $shop->products_count }} шт.) НАЗАВЖДИ. Цю дію не можна скасувати. Продовжити?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Видалити</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">Магазинів поки немає</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ $shops->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
