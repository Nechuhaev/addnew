{{-- Компактний рейтинг магазину: ★ 4.7 · 12 відгуків (посилання на відгуки). Нічого, якщо відгуків немає. --}}
@if(!empty($rating))
    @php
        $isRu = app()->getLocale() === 'ru';
        $n = (int) $rating['count'];
        $mod10 = $n % 10; $mod100 = $n % 100;
        $form = ($mod10 === 1 && $mod100 !== 11) ? 0 : (($mod10 >= 2 && $mod10 <= 4 && ($mod100 < 12 || $mod100 > 14)) ? 1 : 2);
        $word = $isRu ? ['отзыв', 'отзыва', 'отзывов'][$form] : ['відгук', 'відгуки', 'відгуків'][$form];
        $url = route($isRu ? 'ru.author' : 'author', $userId) . '#reviews';
        $title = $isRu ? 'Рейтинг магазина' : 'Рейтинг магазину';
    @endphp
    <a href="{{ $url }}" class="shop-rating" title="{{ $title }}" style="text-decoration:none; {{ !empty($stacked) ? 'display:block; margin-top:3px; font-size:13px; white-space:nowrap;' : 'white-space:nowrap; margin-left:6px;' }}"><span aria-hidden="true" style="display:inline; color:#f5a623;">★</span> <strong style="display:inline;">{{ number_format($rating['avg'], 1, ',', '') }}</strong> <span style="display:inline; color:#777; font-weight:400;">· {{ $n }} {{ $word }}</span></a>
@endif
