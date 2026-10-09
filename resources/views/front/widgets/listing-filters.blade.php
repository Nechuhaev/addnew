{{-- Фільтри й сортування списку (App\Services\ListingFilters). Параметри — у GET. --}}
@php
    $LF = \App\Services\ListingFilters::class;
    $ru = app()->getLocale() === 'ru';
    $t = function ($uk, $ruText) use ($ru) { return $ru ? $ruText : $uk; };
    $f = $listingFilters->values;
    $keep = array_diff_key(request()->except('page'), $f); // s, cat_id… — не губимо
    $chipLabel = function ($key) use ($f, $t, $LF) {
        if ($key === 'price') {
            $n = function ($v) { return number_format($v, 0, '', ' '); };
            if ($f['price_from'] !== null && $f['price_to'] !== null) return $n($f['price_from']) . '–' . $n($f['price_to']) . ' грн';
            return $f['price_from'] !== null ? $t('від ', 'от ') . $n($f['price_from']) . ' грн' : $t('до ', 'до ') . $n($f['price_to']) . ' грн';
        }
        if ($key === 'seller') return $LF::label($LF::SELLERS, $f['seller']);
        if ($key === 'condition') return $LF::label($LF::CONDITIONS, $f['condition']);
        return $t('В наявності', 'В наличии');
    };
    $chips = $listingFilters->chips();
@endphp
<form class="listing-filters{{ count($chips) ? ' listing-filters--active' : '' }}" method="get" action="{{ url()->current() }}">
    @foreach($keep as $name => $value)
        @if(is_scalar($value))
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endif
    @endforeach

    <div class="listing-filters__top">
        <button type="button" class="listing-filters__toggle" onclick="this.closest('form').classList.toggle('listing-filters--open');">
            {{ $t('Фільтри', 'Фильтры') }}@if(count($chips)) ({{ count($chips) }})@endif
        </button>
        <label class="listing-filters__sort">
            <span>{{ $t('Сортування', 'Сортировка') }}:</span>
            <select name="sort" onchange="this.form.submit()">
                @foreach($LF::SORTS as $key => $labels)
                    <option value="{{ $key }}" {{ ($f['sort'] ?? 'new') === $key ? 'selected' : '' }}>{{ $LF::label($LF::SORTS, $key) }}</option>
                @endforeach
            </select>
        </label>
        @isset($total)
            <span class="listing-filters__total">{{ $t('Знайдено', 'Найдено') }}: {{ number_format($total, 0, '', ' ') }}</span>
        @endisset
    </div>

    <div class="listing-filters__panel">
        <div class="listing-filters__field listing-filters__price">
            <span>{{ $t('Ціна, грн', 'Цена, грн') }}</span>
            <input type="number" min="0" name="price_from" value="{{ $f['price_from'] }}" placeholder="{{ $t('від', 'от') }}" inputmode="numeric">
            <input type="number" min="0" name="price_to" value="{{ $f['price_to'] }}" placeholder="{{ $t('до', 'до') }}" inputmode="numeric">
        </div>
        <label class="listing-filters__field">
            <span>{{ $t('Продавець', 'Продавец') }}</span>
            <select name="seller">
                <option value="">{{ $t('Усі', 'Все') }}</option>
                @foreach($LF::SELLERS as $key => $labels)
                    <option value="{{ $key }}" {{ $f['seller'] === $key ? 'selected' : '' }}>{{ $LF::label($LF::SELLERS, $key) }}</option>
                @endforeach
            </select>
        </label>
        <label class="listing-filters__field">
            <span>{{ $t('Стан', 'Состояние') }}</span>
            <select name="condition">
                <option value="">{{ $t('Будь-який', 'Любое') }}</option>
                @foreach($LF::CONDITIONS as $key => $labels)
                    <option value="{{ $key }}" {{ $f['condition'] === $key ? 'selected' : '' }}>{{ $LF::label($LF::CONDITIONS, $key) }}</option>
                @endforeach
            </select>
        </label>
        <label class="listing-filters__check">
            <input type="checkbox" name="in_stock" value="1" {{ $f['in_stock'] ? 'checked' : '' }}>
            {{ $t('Тільки в наявності', 'Только в наличии') }}
        </label>
        <button type="submit" class="btn listing-filters__apply">{{ $t('Показати', 'Показать') }}</button>
    </div>

    @if(count($chips))
        <div class="listing-filters__chips">
            @foreach($chips as $chip)
                <a href="{{ $chip['url'] }}" class="listing-filters__chip" title="{{ $t('Прибрати', 'Убрать') }}">{{ $chipLabel($chip['key']) }} &times;</a>
            @endforeach
            <a href="{{ $listingFilters->resetUrl() }}" class="listing-filters__reset">{{ $t('Скинути фільтри', 'Сбросить фильтры') }}</a>
        </div>
    @endif
</form>
