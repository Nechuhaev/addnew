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
                    <h1>{{ __('front.my_shop') }}</h1>
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
                            <div class="shop-info-card__info">
                                <h2 class="shop-info-card__name">{{ $shop['name'] ?? __('shop.no_shop_name') }}</h2>
                                @if($shop['description'])
                                    <p class="shop-info-card__description">{{ Str::limit($shop['description'], 150) }}</p>
                                @endif
                                <ul class="shop-info-card__contacts">
                                    @if($shop['telephone'])
                                        <li>{{ $shop['telephone'] }}</li>
                                    @endif
                                    <li>{{ $shop['email'] }}</li>
                                </ul>
                                <a href="{{ route('profile.shop.info') }}" class="shop-info-card__edit-link">{{ __('shop.edit_shop_info_link') }}</a>
                            </div>
                            <div class="shop-info-card__logo">
                                <img src="{{ $shop['logo_url'] }}" alt="{{ $shop['name'] }}" class="shop-info-card__logo-img">
                            </div>
                        </div>
                    </div>
                    <h2 class="shop-products-heading">{{ __('shop.products_heading') }}</h2>
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
                                            <div class="shop-product-card__price-row">
                                                <span class="shop-product-card__price">{{ $product->formatted_price }}</span>
                                                <span class="shop-product-card__availability">{{ $product->stock == 'out_of_stock' ? __('shop.out_of_stock') : __('shop.in_stock') }}</span>
                                            </div>
                                        @endif
                                        <div class="shop-product-card__actions">
                                            <a href="{{ route('profile.shop.product.edit', ['id' => $product->id]) }}" class="shop-action shop-action--edit">{{ __('shop.edit_link') }}</a>
                                            <a href="{{ route('ad.delete', ['id' => $product->id]) }}" onclick="return confirm('{{ __('shop.delete_product_confirm') }}');" class="shop-action shop-action--delete">{{ __('shop.delete_link') }}</a>
                                            @if ($product->status == 'active')
                                                <a href="{{ route('ad.changeStatus', ['ad_id' => $product->id, 'status_id' => 0]) }}" class="shop-action shop-action--suspend">{{ __('shop.suspend_link') }}</a>
                                            @elseif ($product->status == 'suspend')
                                                <a href="{{ route('ad.changeStatus', ['ad_id' => $product->id, 'status_id' => 1]) }}" class="shop-action shop-action--resume">{{ __('shop.resume_link') }}</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{ $products->links('front.widgets.paginate') }}
                    @else
                        <div class="shop-empty-products">
                            <p>{{ __('shop.no_products_text') }}</p>
                            <a href="{{ route('profile.shop.import') }}" class="btn btn-success">{{ __('shop.import_products_button') }}</a>
                        </div>
                    @endif
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection