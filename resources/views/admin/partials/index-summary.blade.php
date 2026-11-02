{{-- Зведення індексації Google ($type: ad / category / tag) — дані seo:index-status --}}
@php($s = \App\Services\Seo\IndexStatus::summary($type))
<div class="card">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center">
            <strong class="mr-3">Индексация Google:</strong>
            <span class="badge badge-success p-2 mr-2" title="Есть показы в поиске или Google подтвердил индексацию">В индексе: {{ number_format($s['indexed'], 0, ',', ' ') }}</span>
            <span class="badge badge-danger p-2 mr-2" title="Просканировано или найдено, но не проиндексировано">Не в индексе: {{ number_format($s['not_indexed'], 0, ',', ' ') }}</span>
            <span class="badge badge-warning p-2 mr-2" title="noindex, canonical на другую страницу, редирект, 404">Исключено: {{ number_format($s['excluded'], 0, ',', ' ') }}</span>
            <span class="badge badge-secondary p-2 mr-2" title="Ещё не проверялось">Не проверено: {{ number_format($s['unchecked'], 0, ',', ' ') }}</span>
            <span class="text-muted ml-1">
                из {{ number_format($s['total'], 0, ',', ' ') }}
                @if($s['checked'] > 0)
                    · в индексе {{ round($s['indexed'] * 100 / max(1, $s['checked'])) }}% проверенных
                @endif
                @if($s['last_checked'])
                    · обновлено {{ date('d.m.Y H:i', strtotime($s['last_checked'])) }}
                @else
                    · данных ещё нет: запустите <code>php artisan seo:index-status</code>
                @endif
            </span>
        </div>
    </div>
</div>
