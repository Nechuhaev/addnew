{{-- Магазини, чия назва збігається із запитом (App\Services\StoreSearch::shops) --}}
@if($shops->isNotEmpty())
    @php($ru = app()->getLocale() === 'ru')
    <section class="search-shops" aria-label="{{ $ru ? 'Магазины' : 'Магазини' }}">
        <div class="search-shops__h">{{ $ru ? 'Магазины' : 'Магазини' }}</div>
        <div class="search-shops__list">
            @foreach($shops as $shop)
                <a href="{{ route('author', $shop->id) }}" class="search-shops__item">
                    <img src="{{ $shop->image ?? asset('assets/front/img/placeholder.png') }}" alt="" width="44" height="44" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                    <span class="search-shops__text">
                        <strong>{{ $shop->username }}</strong>
                        <small>{{ number_format($shop->ads_count, 0, '', ' ') }} {{ $ru ? 'товаров' : 'товарів' }}</small>
                    </span>
                </a>
            @endforeach
        </div>
        <a href="{{ route($ru ? 'ru.stores' : 'stores', ['q' => $term]) }}" class="search-shops__all">{{ $ru ? 'Все магазины по запросу' : 'Усі магазини за запитом' }} →</a>
    </section>
@endif
