@extends('front.layout')

@section('meta_title', __('shop.edit_product_breadcrumb') . ' | ' . $product->name)
@section('meta_description', __('shop.edit_product_breadcrumb') . ' | ' . $product->name)

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.product.edit', $product) }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('shop.edit_product_breadcrumb') }}</h1>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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

                    <form action="{{ route('profile.shop.product.update', ['id' => $product->id]) }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="product-edit-form">
                            <div class="product-edit-section">
                                <h2 class="product-edit-section__title">{{ __('ad_create.image_label') }}</h2>

                                <div class="product-edit-images">
                                    @for($i = 0; $i < 5; $i++)
                                        @php
                                            $isMain = $i === 0;
                                            $hasImage = false;
                                            $imageUrl = '';

                                            if ($isMain && $product->image) {
                                                $hasImage = true;
                                                $imageUrl = $product->image;
                                            } elseif (!$isMain && !empty($product->images[$i - 1])) {
                                                $hasImage = true;
                                                $imageUrl = $product->images[$i - 1];
                                            }
                                        @endphp

                                        <div class="product-edit-image {{ $hasImage ? 'product-edit-image--has-image' : '' }} {{ $isMain ? 'product-edit-image--main' : '' }}">
                                            <label class="product-edit-image__trigger">
                                                <input type="file" name="image_slot[{{ $i }}]" accept="image/jpeg,image/png,image/bmp" multiple style="display: none;">
                                                @if($hasImage)
                                                    <img src="{{ $imageUrl }}" alt="" class="product-edit-image__img">
                                                @else
                                                    <span class="product-edit-image__placeholder">{{ __('shop_product_edit.add_image_placeholder') }}</span>
                                                @endif
                                            </label>

                                            @if(!$isMain && $hasImage)
                                                <label class="product-edit-image__delete">
                                                    <input type="checkbox" name="delete_image_slot[{{ $i }}]" value="1" style="display: none;">
                                                    <span class="product-edit-image__delete-icon" title="{{ __('shop.delete_link') }}">&times;</span>
                                                </label>
                                            @endif

                                            @if($isMain && $hasImage)
                                                <span class="product-edit-image__badge">{{ __('shop_product_edit.main_image_badge') }}</span>
                                            @endif
                                        </div>
                                    @endfor
                                </div>

                                <span class="form-help">{{ __('shop_product_edit.images_help') }}</span>
                            </div>

                            <div class="product-edit-section">
                                <h2>{{ __('shop_product_edit.basic_info_heading') }}</h2>

                                <div class="form-group">
                                    <label>{{ __('shop_product_edit.product_name_label') }} <span class="star">(*)</span></label>
                                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control required" maxlength="150">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('ad_create.price_label') }} <span class="star">(*)</span></label>
                                    <input type="text" name="price" value="{{ old('price', $product->price) }}" class="form-control required">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('ad_create.currency_label') }} <span class="star">(*)</span></label>
                                    <select name="currency_id" class="form-control required">
                                        <option value="">{{ __('ad_create.select_dash_placeholder') }}</option>
                                        @if($currencies)
                                            @foreach($currencies as $currency)
                                                <option {{ ($currency['id'] == $product->currency_id) ? 'selected' : '' }} value="{{ $currency['id'] }}">{{ $currency['code'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('shop_product_edit.product_description_label') }} <span class="star">(*)</span></label>
                                    <textarea name="content" class="form-control required" rows="10">{{ old('content', $product->content) }}</textarea>
                                </div>
                            </div>

                            <div class="product-edit-section">
                                <h2>{{ __('shop_product_edit.details_heading') }}</h2>

                                <div class="form-group">
                                    <label>{{ __('shop_import.field_brand_name') }}</label>
                                    <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="form-control" placeholder="{{ __('shop_product_edit.brand_placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('shop_product_edit.sku_label') }}</label>
                                    <input type="text" name="code" value="{{ old('code', $product->code) }}" class="form-control" placeholder="{{ __('shop_product_edit.sku_placeholder') }}">
                                </div>

                                <div class="form-group">
                                    <label>{{ __('shop_product_edit.condition_label') }} <span class="star">(*)</span></label>
                                    <select name="condition" class="form-control required">
                                        <option value="new" {{ (old('condition', $product->condition) == 'new') ? 'selected' : '' }}>{{ __('shop_product_edit.condition_new') }}</option>
                                        <option value="used" {{ (old('condition', $product->condition) == 'used') ? 'selected' : '' }}>{{ __('shop_product_edit.condition_used') }}</option>
                                        <option value="refurbished" {{ (old('condition', $product->condition) == 'refurbished') ? 'selected' : '' }}>{{ __('shop_product_edit.condition_refurbished') }}</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{ __('shop_product_edit.availability_label') }} <span class="star">(*)</span></label>
                                    <select name="stock" class="form-control required">
                                        <option value="in_stock" {{ (old('stock', $product->stock) == 'in_stock') ? 'selected' : '' }}>{{ __('shop_product_edit.stock_in') }}</option>
                                        <option value="out_of_stock" {{ (old('stock', $product->stock) == 'out_of_stock') ? 'selected' : '' }}>{{ __('shop_product_edit.stock_out') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="product-edit-section">
                                <h2>{{ __('shop_product_edit.competitor_monitoring_heading') }}</h2>

                                <div class="form-group">
                                    <label>{{ __('shop_product_edit.competitor_url_label') }}</label>
                                    <input type="url" name="competitor_url" value="{{ old('competitor_url', $product->competitor_url) }}" class="form-control" placeholder="https://...">
                                    <span class="form-help">
                                        {{ __('shop_product_edit.competitor_url_help') }}
                                    </span>
                                    @if($product->competitor_url)
                                        <span class="form-help">
                                            {{ __('shop_product_edit.last_check_label') }} {{ optional($product->priceChecks()->latest('checked_at')->first())->checked_at ?? __('shop_product_edit.never_checked_text') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-action">
                                <input type="submit" name="submit" class="btn btn-step" value="{{ __('shop.save_changes_button') }}">
                            </div>
                        </div>
                    </form>
                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.product-edit-image').forEach(function(slot) {
        var fileInput = slot.querySelector('input[type="file"]');
        var img = slot.querySelector('.product-edit-image__img');
        var placeholder = slot.querySelector('.product-edit-image__placeholder');
        var deleteLabel = slot.querySelector('.product-edit-image__delete');
        var deleteCheckbox = slot.querySelector('.product-edit-image__delete input[type="checkbox"]');

        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        if (!img) {
                            img = document.createElement('img');
                            img.className = 'product-edit-image__img';
                            var trigger = slot.querySelector('.product-edit-image__trigger');
                            trigger.insertBefore(img, trigger.firstChild);
                        }
                        img.src = ev.target.result;
                        img.style.opacity = '1';
                        slot.classList.add('product-edit-image--has-image');

                        if (placeholder) placeholder.style.display = 'none';

                        if (deleteCheckbox) {
                            deleteCheckbox.checked = false;
                            deleteCheckbox.disabled = false;
                        }
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        if (deleteLabel) {
            deleteLabel.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                deleteCheckbox.checked = !deleteCheckbox.checked;

                if (deleteCheckbox.checked) {
                    if (img) img.style.opacity = '0.3';
                } else {
                    if (img) img.style.opacity = '1';
                }
            });
        }
    });
});
</script>
@endsection