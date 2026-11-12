{{-- Зведення індексації Google ($type: ad / category / tag) — дані seo:index-status; плашки — фільтри ?index=... --}}
@php
    $s = \App\Services\Seo\IndexStatus::summary($type);
    $idxActive = request('index');
    $idxBase = request()->except(['index', 'page']);
    $idxBadges = [
        'indexed' => ['success', $s['indexed'], 'Есть показы в поиске или Google подтвердил индексацию'],
        'refused' => ['danger', $s['refused'], 'Страница открыта для индексации, но Google её не взял (просканирована или найдена, но не проиндексирована)'],
        'ours' => ['light', $s['ours'] + $s['ours_unchecked'], 'Мы сами закрыли noindex (мало объявлений, стоп-слово) — отсутствие в индексе ожидаемо'],
        'excluded' => ['warning', $s['excluded'], 'canonical на другую страницу, редирект, 404, robots'],
        'unchecked' => ['secondary', $s['unchecked'], 'Ещё не проверялось'],
    ];
@endphp
<div class="card">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center">
            <strong class="mr-3">Индексация Google:</strong>
            @foreach ($idxBadges as $key => [$cls, $n, $hint])
                <a href="{{ url()->current() . '?' . http_build_query($idxActive === $key ? $idxBase : $idxBase + ['index' => $key]) }}"
                   class="badge badge-{{ $cls }} p-2 mr-2 mb-1" title="{{ $hint }}"
                   style="{{ $idxActive === $key ? 'box-shadow:0 0 0 3px #3b5998;' : '' }}{{ $idxActive && $idxActive !== $key ? 'opacity:.55;' : '' }}{{ $cls === 'light' ? 'border:1px solid #ccc;' : '' }}">
                    {{ \App\Services\Seo\IndexStatus::FILTERS[$key] }}: {{ number_format($n, 0, ',', ' ') }}
                </a>
            @endforeach
            @if ($idxActive)
                <a href="{{ url()->current() . ($idxBase ? '?' . http_build_query($idxBase) : '') }}" class="small ml-1 mb-1">× сбросить фильтр</a>
            @endif
            <span class="text-muted ml-1">
                из {{ number_format($s['total'], 0, ',', ' ') }}
                @if($s['open_checked'] > 0)
                    · в индексе {{ round($s['indexed'] * 100 / max(1, $s['open_checked'])) }}% проверенных открытых страниц
                @endif
                @if($s['last_checked'])
                    · обновлено {{ date('d.m.Y H:i', strtotime($s['last_checked'])) }}
                @else
                    · данных ещё нет: запустите <code>php artisan seo:index-status</code>
                @endif
            </span>
        </div>
        @php($bingLine = \App\Console\Commands\BingStats::summaryLine())
        @if($bingLine)
            <div class="small text-muted mt-2"><strong>Bing Webmaster</strong>: {{ $bingLine }}</div>
        @endif
        @php($sub = \App\Services\Seo\IndexSubmissions::report())
        @if(!empty($sub))
            <div class="small text-muted mt-2">
                <strong>Отправлено на индексацию</strong> (все типы страниц):
                IndexNow — за сутки {{ $sub['indexnow']['day'] }}, за 7 дней {{ $sub['indexnow']['week'] }}, всего {{ $sub['indexnow']['total'] }}, из них в индексе {{ $sub['indexnow']['indexed'] }}
                · Google — за сутки {{ $sub['google']['day'] }}, за 7 дней {{ $sub['google']['week'] }}, всего {{ $sub['google']['total'] }}, из них в индексе {{ $sub['google']['indexed'] }}
            </div>
        @endif
    </div>
</div>
