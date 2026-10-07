@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Моніторинг цін конкурентів</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        Товари, для яких магазин вказав посилання на аналогічний товар у конкурента.
                        Команда <code>products:monitor-prices</code> щогодини бере звідти <strong>лише ціну</strong>
                        (якщо збігається валюта). Наявність, 404 чи недоступність сайту конкурента на товар не впливають.
                        Ціни, що відрізняються від поточної більш ніж у {{ env('PRICE_MONITOR_MAX_RATIO', 2) }} рази, не застосовуються — позначаються як «Підозріла ціна».
                    </p>

                    <h5 class="mt-3">За останню добу</h5>
                    <p>
                        Перевірок: <strong>{{ $summary['total'] }}</strong> ·
                        <span class="badge badge-success">Ціну змінено: {{ $summary['changed'] }}</span>
                        <span class="badge badge-secondary">Без змін: {{ $summary['unchanged'] }}</span>
                        <span class="badge badge-warning">Підозрілих: {{ $summary['suspicious'] }}</span>
                        <span class="badge badge-warning">Інша валюта: {{ $summary['currency'] }}</span>
                        <span class="badge badge-danger">Помилок: {{ $summary['errors'] }}</span>
                    </p>

                    <div class="mb-3">
                        @foreach(['all' => "Усі ({$totalWithCompetitor})", 'problems' => 'Проблемні', 'changed' => 'Ціну змінено', 'never' => 'Ще не перевірялись'] as $key => $label)
                            <a href="{{ route('admin.shops.competitorMonitor', ['filter' => $key]) }}"
                               class="btn btn-sm {{ $filter === $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
                        @endforeach
                    </div>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Магазин</th>
                                    <th>Товар</th>
                                    <th>Наша ціна</th>
                                    <th>Конкурент</th>
                                    <th>Перевірено</th>
                                    <th>Результат</th>
                                    <th>Примітка</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    @php($check = $checks->get($product->id))
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td>
                                            @if($product->user)
                                                <a href="{{ route('admin.shops.products', $product->user_id) }}">{{ $product->user->username }}</a>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('admin.ad.edit', $product->id) }}">{{ $product->name }}</a></td>
                                        <td style="white-space:nowrap;">{{ $product->price }} {{ optional($product->currency)->code }}</td>
                                        <td>
                                            <a href="{{ $product->competitor_url }}" target="_blank" rel="noopener noreferrer nofollow" title="{{ $product->competitor_url }}">
                                                {{ parse_url($product->competitor_url, PHP_URL_HOST) ?: $product->competitor_url }} &#8599;
                                            </a>
                                        </td>
                                        <td style="white-space:nowrap;">{{ $check ? $check->checked_at->format('d.m.Y H:i') : '—' }}</td>
                                        <td>
                                            @if($check)
                                                @php([$label, $class] = $check->competitorLabel())
                                                <span class="badge badge-{{ $class }}">{{ $label }}</span>
                                                @if($check->found_price !== null)
                                                    <div class="text-muted" style="white-space:nowrap;">знайдено: {{ rtrim(rtrim($check->found_price, '0'), '.') }} {{ $check->found_currency }}</div>
                                                @endif
                                            @else
                                                <span class="badge badge-light" style="border:1px solid #ccc;">Ще не перевірявся</span>
                                            @endif
                                        </td>
                                        <td class="text-muted" style="font-size:12px;">{{ optional($check)->note }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">Немає товарів за цим фільтром</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
