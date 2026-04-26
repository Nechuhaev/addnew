@extends('front.layout')

@section('meta_title', "Мой магазин | Доска объявлений addnew.biz")
@section('meta_description', "Мой магазин | Доска объявлений addnew.biz")

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.dashboard') }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>Мой магазин</h1>

                    @if(session()->has('success'))
                        <div class="alert success">
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert">
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('error') }}
                        </div>
                    @endif

                    <div class="shop-info-card">
                        <div class="shop-info-card__header">
                            <div class="shop-info-card__logo">
                                <img src="{{ $shop['logo_url'] }}" alt="{{ $shop['name'] }}" class="shop-info-card__logo-img">
                            </div>
                            <div class="shop-info-card__info">
                                <h2 class="shop-info-card__name">{{ $shop['name'] ?? 'Название магазина не указано' }}</h2>
                                <a href="{{ route('profile.shop.info') }}" class="shop-info-card__edit-link">Редактировать информацию о магазине</a>
                            </div>
                        </div>
                    </div>

                    <h2 class="shop-products-heading">Товары магазина</h2>

                    @if($products->count() > 0)
                        <div class="shop-products-list">
                            @foreach($products as $product)
                                <div class="shop-product-card">
                                    <div class="shop-product-card__image">
                                        @if($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="shop-product-card__img">
                                        @else
                                            <img src="{{ asset('assets/front/img/placeholder.png') }}" alt="{{ $product->name }}" class="shop-product-card__img">
                                        @endif
                                    </div>
                                    <div class="shop-product-card__info">
                                        <a href="{{ $product->url }}" class="shop-product-card__name">{{ $product->name }}</a>
                                        @if($product->price)
                                            <span class="shop-product-card__price">{{ $product->formatted_price }}</span>
                                        @endif
                                        <div class="shop-product-card__actions">
                                            <a href="{{ route('ad.edit', ['id' => $product->id]) }}" class="shop-action shop-action--edit">Редактировать</a>
                                            <a href="{{ route('ad.delete', ['id' => $product->id]) }}" onclick="return confirm('Вы действительно хотите удалить этот товар? Отменить это действие будет невозможно.');" class="shop-action shop-action--delete">Удалить</a>
                                            @if ($product->status == 'active')
                                                <a href="{{ route('ad.changeStatus', ['ad_id' => $product->id, 'status_id' => 0]) }}" class="shop-action shop-action--suspend">Приостановить</a>
                                            @elseif ($product->status == 'suspend')
                                                <a href="{{ route('ad.changeStatus', ['ad_id' => $product->id, 'status_id' => 1]) }}" class="shop-action shop-action--resume">Возобновить</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <table class="table-account shop-products-table">
                            <thead>
                            <tr>
                                <th class="th-number">&nbsp;</th>
                                <th class="th-img">Изображение</th>
                                <th class="th-name">Название</th>
                                <th class="hidden-xs">Просмотры</th>
                                <th class="hidden-xs">Статус</th>
                                <th class="hidden-xs">Опции</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $product)
                                <tr>
                                    <td class="td-number">{{ $loop->iteration }}.</td>
                                    <td class="td-img">
                                        @if($product->image)
                                            <img src="{{ $product->image }}" alt="{{ $product->name }}" class="shop-product-img">
                                        @else
                                            <img src="{{ asset('assets/front/img/placeholder.png') }}" alt="{{ $product->name }}" class="shop-product-img">
                                        @endif
                                    </td>
                                    <td class="td-name">
                                        <a href="{{ $product->url }}">{{ $product->name }}</a>
                                        @if($product->price)
                                            <p class="td-price">{{ $product->formatted_price }}</p>
                                        @endif
                                    </td>
                                    <td class="hidden-xs">{{ $product->total_views }}</td>
                                    <td class="hidden-xs">
                                        <span class="status status-{{ $product->status }}">{{ __('user/ads.status_' . $product->status) }}</span>
                                    </td>
                                    <td class="hidden-xs">
                                        <ul class="td-actions shop-td-actions">
                                            <li><a href="{{ route('ad.edit', ['id' => $product->id]) }}" class="shop-action shop-action--edit">Редактировать</a></li>
                                            <li><a href="{{ route('ad.delete', ['id' => $product->id]) }}" onclick="return confirm('Вы действительно хотите удалить этот товар? Отменить это действие будет невозможно.');" class="shop-action shop-action--delete">Удалить</a></li>
                                            @if ($product->status == 'active')
                                                <li><a href="{{ route('ad.changeStatus', ['ad_id' => $product->id, 'status_id' => 0]) }}" class="shop-action shop-action--suspend">Приостановить</a></li>
                                            @elseif ($product->status == 'suspend')
                                                <li><a href="{{ route('ad.changeStatus', ['ad_id' => $product->id, 'status_id' => 1]) }}" class="shop-action shop-action--resume">Возобновить</a></li>
                                            @endif
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        {{ $products->links('front.widgets.paginate') }}
                    @else
                        <div class="shop-empty-products">
                            <p>У вас пока нет добавленных товаров. Вы можете импортировать товары из прайс-листа.</p>
                            <a href="{{ route('profile.shop.import') }}" class="btn btn-success">Импорт товаров</a>
                        </div>
                    @endif

                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection
