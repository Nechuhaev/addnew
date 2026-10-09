@php($ru = app()->getLocale() === 'ru')
@extends('front.layout')
@section('meta_title', ($ru ? 'Избранное' : 'Обране') . ' | addnew.biz')
@section('meta_description', ($ru ? 'Избранное' : 'Обране') . ' | addnew.biz')
@section('style')
    <meta name="robots" content="noindex, nofollow" />
@endsection
@section('content')
    <main class="account-page">
        <div class="container">
            {{ Breadcrumbs::render('profile.favorites') }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ $ru ? 'Избранное' : 'Обране' }}</h1>

                    <div class="fav-alerts {{ $alertsOn ? 'is-on' : '' }}">
                        <span>
                            @if($alertsOn)
                                {{ $ru ? 'Письма о снижении цены включены: сообщим, когда товар подешевеет на 3% и больше.' : 'Листи про зниження ціни увімкнено: повідомимо, коли товар подешевшає на 3% і більше.' }}
                            @else
                                {{ $ru ? 'Письма о снижении цены выключены.' : 'Листи про зниження ціни вимкнено.' }}
                            @endif
                        </span>
                        <form action="{{ route('profile.favorites.alerts') }}" method="post">
                            @csrf
                            <button type="submit" class="btn btn-small">
                                {{ $alertsOn ? ($ru ? 'Выключить' : 'Вимкнути') : ($ru ? 'Включить' : 'Увімкнути') }}
                            </button>
                        </form>
                    </div>

                    @if($favorites->isEmpty())
                        <p>{{ $ru ? 'В избранном пока пусто. Нажмите ♥ на карточке товара, чтобы следить за его ценой.' : 'В обраному поки порожньо. Натисніть ♥ на картці товару, щоб стежити за його ціною.' }}</p>
                    @else
                        <div class="fav-list">
                            @foreach($favorites as $favorite)
                                @php($ad = $favorite->ad)
                                @if(!$ad)
                                    @continue
                                @endif
                                @php($now = \App\Favorite::priceUah($ad))
                                @php($was = (float) $favorite->price_at_add_uah)
                                <div class="fav-item {{ $ad->status !== 'active' ? 'is-inactive' : '' }}">
                                    <a href="{{ route('ad.page', ['slug' => $ad->slug]) }}" class="fav-img">
                                        <img src="{{ $ad->image }}" alt="{{ $ad->name }}" width="90" height="90" loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                                    </a>
                                    <div class="fav-body">
                                        <a href="{{ route('ad.page', ['slug' => $ad->slug]) }}" class="fav-name">{{ $ad->name }}</a>
                                        <div class="fav-seller">{{ optional($ad->user)->username }}</div>
                                        @if($ad->status !== 'active')
                                            <div class="fav-note">{{ $ru ? 'Объявление неактивно' : 'Оголошення неактивне' }}</div>
                                        @endif
                                        <div class="fav-prices">
                                            @if($now > 0)
                                                <strong>{{ number_format($now, 0, '.', ' ') }} грн</strong>
                                            @else
                                                <strong>{{ $ru ? 'Цена не указана' : 'Ціну не вказано' }}</strong>
                                            @endif
                                            @if($was > 0 && $now > 0 && abs($now - $was) >= 1)
                                                <span class="fav-was {{ $now < $was ? 'down' : 'up' }}">
                                                    {{ $now < $was ? '↓' : '↑' }}
                                                    {{ $ru ? 'было' : 'було' }} {{ number_format($was, 0, '.', ' ') }} грн
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="fav-actions">
                                        <form action="{{ route('profile.favorites.notify', $favorite->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="fav-notify {{ $favorite->notify ? 'is-on' : '' }}"
                                                    title="{{ $ru ? 'Сообщать о снижении цены' : 'Сповіщати про зниження ціни' }}">
                                                {{ $favorite->notify ? ($ru ? '🔔 Сообщать' : '🔔 Сповіщати') : ($ru ? '🔕 Не сообщать' : '🔕 Не сповіщати') }}
                                            </button>
                                        </form>
                                        <form action="{{ route('profile.favorites.delete', $favorite->id) }}" method="post">
                                            @csrf
                                            <button type="submit" class="fav-remove">{{ $ru ? 'Убрать' : 'Прибрати' }}</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{ $favorites->links('front.widgets.paginate') }}
                    @endif
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection
