@extends('front.layout')

@section('meta_title', "Информация о магазине | Доска объявлений addnew.biz")
@section('meta_description', "Информация о магазине | Доска объявлений addnew.biz")

@php
    $logoUrl = $user->image;
    if ($logoUrl && strpos($logoUrl, 'http') !== 0) {
        $logoUrl = asset($logoUrl);
    } elseif (!$logoUrl) {
        $logoUrl = asset('assets/front/img/placeholder.png');
    }
    $bannerUrl = $user->banner;
    if ($bannerUrl && strpos($bannerUrl, 'http') !== 0) {
        $bannerUrl = asset($bannerUrl);
    }
@endphp

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.info') }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('shop.info_heading') }}</h1>

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

                    @if ($errors->any())
                        <div class="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('profile.shop.info.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="logo">{{ __('shop.logo_label') }}</label>
                            <div class="logo-preview-wrapper">
                                <img src="{{ $logoUrl }}" alt="{{ __('shop.logo_label') }}" id="logo-preview" class="logo-preview" style="max-width: 100px; max-height: 100px;">
                            </div>
                            <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                            <small class="form-text text-muted">{{ __('shop.logo_recommended_size') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="banner">{{ __('shop.banner_label') }}</label>
                            <div class="banner-preview-wrapper">
                                @if($bannerUrl)
                                    <img src="{{ $bannerUrl }}" alt="{{ __('shop.banner_label') }}" id="banner-preview" class="banner-preview" style="max-width: 100%; max-height: 200px; display:block; margin-bottom: 10px;">
                                @else
                                    <img src="" alt="" id="banner-preview" class="banner-preview" style="max-width: 100%; max-height: 200px; display:none; margin-bottom: 10px;">
                                @endif
                            </div>
                            <input type="file" name="banner" id="banner" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                            <small class="form-text text-muted">{{ __('shop.banner_recommended_size') }}</small>
                        </div>

                        <div class="form-group">
                            <label for="firstname">{{ __('shop.shop_name_label') }} *</label>
                            <input type="text" name="firstname" id="firstname" class="form-control required" value="{{ old('firstname', $user->firstname) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" class="form-control required" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="telephone">{{ __('ad_create.telephone_label') }}</label>
                            <input type="text" name="telephone" id="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}">
                        </div>

                        <div class="form-group">
                            <label for="info">{{ __('shop.shop_description_label') }}</label>
                            <textarea name="info" id="info" class="form-control" rows="6">{{ old('info', $user->info) }}</textarea>
                        </div>

                        @php($ru = app()->getLocale() === 'ru')
                        @php($SD = \App\Services\ShopDelivery::class)
                        @php($selDelivery = old('delivery_methods', $user->delivery_methods ?? []))
                        @php($selPayment = old('payment_methods', $user->payment_methods ?? []))
                        <fieldset class="shop-delivery-form">
                            <legend>{{ $ru ? 'Доставка и оплата' : 'Доставка й оплата' }}</legend>
                            <p class="shop-delivery-form__hint">{{ $ru ? 'Покупатели увидят это на странице магазина и на страницах ваших товаров.' : 'Покупці побачать це на сторінці магазину і на сторінках ваших товарів.' }}</p>

                            <div class="shop-delivery-form__group">
                                <div class="shop-delivery-form__title">{{ $ru ? 'Способы доставки' : 'Способи доставки' }}</div>
                                @foreach($SD::DELIVERY as $key => $pair)
                                    <label class="shop-delivery-form__check">
                                        <input type="checkbox" name="delivery_methods[]" value="{{ $key }}" {{ in_array($key, (array) $selDelivery, true) ? 'checked' : '' }}>
                                        <span>{{ $SD::label($SD::DELIVERY, $key) }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="shop-delivery-form__group">
                                <div class="shop-delivery-form__title">{{ $ru ? 'Способы оплаты' : 'Способи оплати' }}</div>
                                @foreach($SD::PAYMENT as $key => $pair)
                                    <label class="shop-delivery-form__check">
                                        <input type="checkbox" name="payment_methods[]" value="{{ $key }}" {{ in_array($key, (array) $selPayment, true) ? 'checked' : '' }}>
                                        <span>{{ $SD::label($SD::PAYMENT, $key) }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <div class="form-group">
                                <label for="free_delivery_from">{{ $ru ? 'Бесплатная доставка от, грн' : 'Безкоштовна доставка від, грн' }}</label>
                                <input type="number" name="free_delivery_from" id="free_delivery_from" class="form-control shop-delivery-form__sum" min="1" step="1" inputmode="numeric"
                                       value="{{ old('free_delivery_from', $user->free_delivery_from) }}" placeholder="{{ $ru ? 'не обязательно' : 'необов\'язково' }}">
                            </div>

                            <div class="form-group">
                                <label for="delivery_note">{{ $ru ? 'Примечание (сроки отправки, возврат и т. п.)' : 'Примітка (терміни відправлення, повернення тощо)' }}</label>
                                <textarea name="delivery_note" id="delivery_note" class="form-control" rows="3" maxlength="500"
                                          placeholder="{{ $ru ? 'Например: отправляем в день заказа до 15:00, возврат 14 дней' : 'Наприклад: відправляємо в день замовлення до 15:00, повернення 14 днів' }}">{{ old('delivery_note', $user->delivery_note) }}</textarea>
                            </div>
                        </fieldset>

                        <button type="submit" class="btn btn-success">{{ __('shop.save_changes_button') }}</button>
                    </form>
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoInput = document.getElementById('logo');
            const logoPreview = document.getElementById('logo-preview');

            logoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        logoPreview.src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });

            const bannerInput = document.getElementById('banner');
            const bannerPreview = document.getElementById('banner-preview');

            bannerInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        bannerPreview.src = e.target.result;
                        bannerPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endsection