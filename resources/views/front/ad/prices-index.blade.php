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
        {{ Breadcrumbs::render($breadcrumbs) }}
        <h1>{{ $h1 }}</h1>
        <p class="prices-summary">
            {{ $ru
                ? 'Медианные цены и типичный диапазон по разделам доски объявлений Addnew.biz. Статистика построена по активным объявлениям в Украине с ценой в гривнах и обновляется ежедневно; раздел попадает в список, если в нём не меньше ' . \App\Services\PriceStats::MIN_ADS . ' объявлений с ценой.'
                : 'Медіанні ціни й типовий діапазон за розділами дошки оголошень Addnew.biz. Статистику побудовано за активними оголошеннями в Україні з ціною в гривнях, вона оновлюється щодня; розділ потрапляє до списку, якщо в ньому щонайменше ' . \App\Services\PriceStats::MIN_ADS . ' оголошень з ціною.' }}
        </p>
        <p class="prices-note">{{ $ru ? 'Обновлено' : 'Оновлено' }}: {{ $date }}</p>

        @forelse($groups as $g)
            <h2>
                @if($g['root']['url'])<a href="{{ $g['root']['url'] }}">{{ $g['root']['name'] }}</a>@else{{ $g['root']['name'] }}@endif
            </h2>
            <div class="prices-table-wrap">
                <table class="prices-table">
                    <thead><tr><th>{{ $ru ? 'Раздел' : 'Розділ' }}</th><th>{{ $ru ? 'Объявлений' : 'Оголошень' }}</th><th>{{ $ru ? 'Медиана' : 'Медіана' }}</th><th>{{ $ru ? 'Половина объявлений' : 'Половина оголошень' }}</th></tr></thead>
                    <tbody>
                    @foreach(array_merge($g['root']['stats'] ? [$g['root']] : [], $g['children'] ?? []) as $row)
                        <tr>
                            <td><a href="{{ $row['url'] }}">{{ $row['name'] }}</a></td>
                            <td>{{ number_format($row['stats']['count'], 0, ',', ' ') }}</td>
                            <td>{{ $uah($row['stats']['median']) }}</td>
                            <td>{{ $uah($row['stats']['p25']) }} – {{ $uah($row['stats']['p75']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <p>{{ $ru ? 'Статистика пока собирается.' : 'Статистика поки збирається.' }}</p>
        @endforelse
    </div>
</main>
@endsection
