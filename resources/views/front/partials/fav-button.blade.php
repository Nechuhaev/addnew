{{-- Кнопка «в обране». Параметри: $adId, $favIds (масив [ad_id => true]), $withLabel (необов'язково) --}}
@php($favRu = app()->getLocale() === 'ru')
@php($favOn = isset($favIds[$adId]))
@php($favTitleOn = $favRu ? 'Убрать из избранного' : 'Прибрати з обраного')
@php($favTitleOff = $favRu ? 'В избранное — сообщим о снижении цены' : 'В обране — повідомимо про зниження ціни')
@auth
    <button type="button" class="fav-btn {{ $favOn ? 'is-active' : '' }} {{ !empty($withLabel) ? 'with-label' : '' }}"
            data-fav-url="{{ route('favorites.toggle', ['adId' => $adId]) }}"
            data-title-on="{{ $favTitleOn }}" data-title-off="{{ $favTitleOff }}"
            title="{{ $favOn ? $favTitleOn : $favTitleOff }}" aria-pressed="{{ $favOn ? 'true' : 'false' }}">
        <span class="fav-heart" aria-hidden="true">♥</span>@if(!empty($withLabel))<span class="fav-label">{{ $favRu ? 'В избранное' : 'В обране' }}</span>@endif
    </button>
@else
    <a href="{{ route('login') }}" rel="nofollow" class="fav-btn {{ !empty($withLabel) ? 'with-label' : '' }}" title="{{ $favRu ? 'Войдите, чтобы добавить в избранное' : 'Увійдіть, щоб додати в обране' }}">
        <span class="fav-heart" aria-hidden="true">♥</span>@if(!empty($withLabel))<span class="fav-label">{{ $favRu ? 'В избранное' : 'В обране' }}</span>@endif
    </a>
@endauth
