{{-- Доставка й оплата магазину. $delivery — App\Services\ShopDelivery::forShop(); $compact — коротко (сторінка товару) --}}
@php($ru = app()->getLocale() === 'ru')
<div class="shop-delivery {{ !empty($compact) ? 'shop-delivery--compact' : '' }}">
    @if(empty($compact))
        <h3 class="shop-delivery__h">{{ $ru ? 'Доставка и оплата' : 'Доставка й оплата' }}</h3>
    @endif
    @if($delivery['delivery'])
        <div class="shop-delivery__row">
            <span class="shop-delivery__icon" aria-hidden="true">🚚</span>
            <span class="shop-delivery__label">{{ $ru ? 'Доставка:' : 'Доставка:' }}</span>
            <span class="shop-delivery__items">
                @foreach($delivery['delivery'] as $item)<span class="shop-delivery__chip">{{ $item }}</span>@endforeach
            </span>
        </div>
    @endif
    @if($delivery['payment'])
        <div class="shop-delivery__row">
            <span class="shop-delivery__icon" aria-hidden="true">💳</span>
            <span class="shop-delivery__label">{{ $ru ? 'Оплата:' : 'Оплата:' }}</span>
            <span class="shop-delivery__items">
                @foreach($delivery['payment'] as $item)<span class="shop-delivery__chip">{{ $item }}</span>@endforeach
            </span>
        </div>
    @endif
    @if($delivery['free_from'])
        <div class="shop-delivery__row">
            <span class="shop-delivery__icon" aria-hidden="true">🎁</span>
            <span class="shop-delivery__free">{{ $ru ? 'Бесплатная доставка от' : 'Безкоштовна доставка від' }} {{ number_format($delivery['free_from'], 0, '.', ' ') }} грн</span>
        </div>
    @endif
    @if($delivery['note'] !== '')
        <p class="shop-delivery__note">{!! nl2br(e($delivery['note'])) !!}</p>
    @endif
</div>
