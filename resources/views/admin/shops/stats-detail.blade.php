@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Статистика: {{ $shop->firstname ?: $shop->email }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <a href="{{ route('admin.shops.stats') }}" class="btn btn-sm btn-secondary">&larr; До списку магазинів</a>
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
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-3">
                            <div style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['shop_views'] }}</div>
                                <div class="text-muted" style="font-size:13px;">Переглядів сторінки магазину</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['product_views'] }}</div>
                                <div class="text-muted" style="font-size:13px;">Переглядів товарів</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['click_contacts'] }}</div>
                                <div class="text-muted" style="font-size:13px;">Показів контактів</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['click_shop_link'] }}</div>
                                <div class="text-muted" style="font-size:13px;">Переходів на сайт-джерело</div>
                            </div>
                        </div>
                    </div>

                    <h4>Статистика по товарах</h4>

                    @if($products->isEmpty())
                        <p>У цього магазину поки немає товарів.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Товар</th>
                                        <th class="text-center">Перегляди</th>
                                        <th class="text-center">Показ контактів</th>
                                        <th class="text-center">Перехід на сайт</th>
                                        <th class="text-center">Сер. час на сторінці</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <a href="{{ $product['url'] }}" target="_blank" style="display:flex; align-items:center; gap:10px;">
                                                    <img src="{{ $product['image'] ?: asset('assets/front/img/placeholder.png') }}" alt="" width="40" height="40" style="object-fit:cover; border-radius:4px;">
                                                    <span>{{ $product['name'] }}</span>
                                                </a>
                                            </td>
                                            <td class="text-center">{{ $product['views'] }}</td>
                                            <td class="text-center">{{ $product['click_contacts'] }}</td>
                                            <td class="text-center">{{ $product['click_shop_link'] }}</td>
                                            <td class="text-center">
                                                @if($product['avg_duration'])
                                                    {{ gmdate('i:s', $product['avg_duration']) }}
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection