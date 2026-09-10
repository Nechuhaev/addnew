@extends('front.layout')
@section('meta_title', "Статистика магазину | Доска объявлений addnew.biz")
@section('meta_description', "Статистика магазину | Доска объявлений addnew.biz")
@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render('profile.shop.stats') }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('shop_stats.heading') }}</h1>

                    <div class="columns" style="margin: 20px 0;">
                        <div class="col-3">
                            <div class="shop-stat-card" style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['shop_views'] }}</div>
                                <div style="color:#727272; font-size:13px;">{{ __('shop_stats.shop_views_label') }}</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="shop-stat-card" style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['product_views'] }}</div>
                                <div style="color:#727272; font-size:13px;">{{ __('shop_stats.product_views_label') }}</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="shop-stat-card" style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['click_contacts'] }}</div>
                                <div style="color:#727272; font-size:13px;">{{ __('shop_stats.click_contacts_label') }}</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="shop-stat-card" style="border:1px solid #eee; padding:16px; text-align:center;">
                                <div style="font-size:28px; font-weight:700; color:#19346c;">{{ $totals['click_shop_link'] }}</div>
                                <div style="color:#727272; font-size:13px;">{{ __('shop_stats.click_shop_link_label') }}</div>
                            </div>
                        </div>
                    </div>

                    <h2 class="shop-products-heading">{{ __('shop_stats.by_product_heading') }}</h2>

                    @if($products->isEmpty())
                        <p>{{ __('shop_stats.no_data') }}</p>
                    @else
                        <div class="table-responsive">
                            <table class="table" style="width:100%; border-collapse:collapse;">
                                <thead>
                                    <tr style="border-bottom:2px solid #eee; text-align:left;">
                                        <th style="padding:8px;">{{ __('shop_stats.th_product') }}</th>
                                        <th style="padding:8px; text-align:center;">{{ __('shop_stats.th_views') }}</th>
                                        <th style="padding:8px; text-align:center;">{{ __('shop_stats.th_contacts_clicks') }}</th>
                                        <th style="padding:8px; text-align:center;">{{ __('shop_stats.th_shop_clicks') }}</th>
                                        <th style="padding:8px; text-align:center;">{{ __('shop_stats.th_avg_duration') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr style="border-bottom:1px solid #f0f0f0;">
                                            <td style="padding:8px;">
                                                <a href="{{ $product['edit_url'] }}" style="display:flex; align-items:center; gap:10px;">
                                                    <img src="{{ $product['image'] ?: asset('assets/front/img/placeholder.png') }}" alt="" width="40" height="40" style="object-fit:cover; border-radius:4px;">
                                                    <span>{{ $product['name'] }}</span>
                                                </a>
                                            </td>
                                            <td style="padding:8px; text-align:center;">{{ $product['views'] }}</td>
                                            <td style="padding:8px; text-align:center;">{{ $product['click_contacts'] }}</td>
                                            <td style="padding:8px; text-align:center;">{{ $product['click_shop_link'] }}</td>
                                            <td style="padding:8px; text-align:center;">
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
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection
