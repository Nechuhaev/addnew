@extends('front.layout')

@section('meta_title', $meta['meta_title'])

@section('meta_description', $meta['meta_description'])

@section('style')
    @include('front.ad.partials.prices-style')
@endsection

@section('content')
@php
    $ru = app()->getLocale() === 'ru';
    $uah = function ($v) { return number_format((float) $v, 0, ',', ' ') . ' грн'; };
@endphp
<main class="category-page prices-page">
    <div class="container">
        {{ Breadcrumbs::render($breadcrumbs, $entity) }}
        <h1>{{ $h1 }}</h1>

        <p class="prices-summary">{{ $summary }}</p>

        <div class="prices-tiles">
            <div class="prices-tile prices-tile--main">
                <span class="prices-tile__label">{{ $ru ? 'Медианная цена' : 'Медіанна ціна' }}</span>
                <span class="prices-tile__value">{{ $uah($stats['median']) }}</span>
            </div>
            <div class="prices-tile">
                <span class="prices-tile__label">{{ $ru ? 'Половина объявлений' : 'Половина оголошень' }}</span>
                <span class="prices-tile__value">{{ $uah($stats['p25']) }} – {{ $uah($stats['p75']) }}</span>
            </div>
            <div class="prices-tile">
                <span class="prices-tile__label">{{ $ru ? '90% объявлений' : '90% оголошень' }}</span>
                <span class="prices-tile__value">{{ $uah($stats['p5']) }} – {{ $uah($stats['p95']) }}</span>
            </div>
            <div class="prices-tile">
                <span class="prices-tile__label">{{ $ru ? 'Объявлений с ценой' : 'Оголошень з ціною' }}</span>
                <span class="prices-tile__value">{{ number_format($stats['count'], 0, ',', ' ') }}</span>
            </div>
        </div>
        <p class="prices-note">{{ $ru ? 'Обновлено' : 'Оновлено' }}: {{ $date }}. <a href="{{ $entity->url }}">{{ $ru ? 'Все объявления раздела' : 'Усі оголошення розділу' }} «{{ $name }}» →</a></p>

        @if($conditionText || $sellerText)
            <h2>{{ $ru ? 'Новое и б/у, магазины и частные продавцы' : 'Нове й б/в, магазини й приватні продавці' }}</h2>
            @if($conditionText)<p>{{ $conditionText }}</p>@endif
            @if($sellerText)<p>{{ $sellerText }}</p>@endif
        @endif

        @if($cities)
            <h2>{{ $ru ? 'Цены по городам' : 'Ціни за містами' }}</h2>
            <div class="prices-table-wrap">
                <table class="prices-table">
                    <thead><tr><th>{{ $ru ? 'Город' : 'Місто' }}</th><th>{{ $ru ? 'Объявлений' : 'Оголошень' }}</th><th>{{ $ru ? 'Медиана' : 'Медіана' }}</th></tr></thead>
                    <tbody>
                    @foreach($cities as $c)
                        <tr><td><a href="{{ $c['url'] }}">{{ $c['name'] }}</a></td><td>{{ $c['count'] }}</td><td>{{ $uah($c['median']) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($children)
            <h2>{{ $ru ? 'Цены в подразделах' : 'Ціни в підрозділах' }}</h2>
            <div class="prices-table-wrap">
                <table class="prices-table">
                    <thead><tr><th>{{ $ru ? 'Подраздел' : 'Підрозділ' }}</th><th>{{ $ru ? 'Объявлений' : 'Оголошень' }}</th><th>{{ $ru ? 'Медиана' : 'Медіана' }}</th></tr></thead>
                    <tbody>
                    @foreach($children as $c)
                        <tr><td><a href="{{ $c['url'] }}">{{ $c['name'] }}</a></td><td>{{ $c['count'] }}</td><td>{{ $uah($c['median']) }}</td></tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($ads)
            <h2>{{ $ru ? 'Свежие объявления с ценой' : 'Свіжі оголошення з ціною' }}</h2>
            @include('front.loop.ads', ['ads' => $ads])
        @endif

        @include('front.partials.faq', ['faq' => $faq])

        <p class="prices-note">
            @if($parentLink)
                <a href="{{ $parentLink['url'] }}">{{ $ru ? 'Цены в разделе' : 'Ціни в розділі' }} «{{ $parentLink['name'] }}»</a> ·
            @endif
            <a href="{{ $indexUrl }}">{{ $ru ? 'Цены по всем разделам' : 'Ціни за всіма розділами' }}</a>
        </p>
    </div>
</main>
<script type="application/ld+json">{!! json_encode($dataset, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endsection
