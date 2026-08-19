@extends('front.layout')

@section('meta_title', 'Редактировать товар | ' . $product->name)
@section('meta_description', 'Редактировать товар | ' . $product->name)

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.product.edit', $product) }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>Редактировать товар</h1>

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
                                <h2 class="product-edit-section__title">Зображення</h2>

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
                                                    <span class="product-edit-image__placeholder">Додати</span>
                                                @endif
                                            </label>

                                            @if(!$isMain && $hasImage)
                                                <label class="product-edit-image__delete">
                                                    <input type="checkbox" name="delete_image_slot[{{ $i }}]" value="1" style="display: none;">
                                                    <span class="product-edit-image__delete-icon" title="Видалити">&times;</span>
                                                </label>
                                            @endif

                                            @if($isMain && $hasImage)
                                                <span class="product-edit-image__badge">Головне</span>
                                            @endif
                                        </div>
                                    @endfor
                                </div>

                                <span class="form-help">Натисніть на зображення для редагування. Максимальний розмір: 1024 КБ. Формати: JPG, JPEG, BMP, PNG.</span>
                            </div>

                            <div class="product-edit-section">
                                <h2>Основна інформація</h2>

                                <div class="form-group">
                                    <label>Назва товару <span class="star">(*)</span></label>
                                    <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control required" maxlength="150">
                                </div>

                                <div class="form-group">
                                    <label>Ціна <span class="star">(*)</span></label>
                                    <input type="text" name="price" value="{{ old('price', $product->price) }}" class="form-control required">
                                </div>

                                <div class="form-group">
                                    <label>Валюта <span class="star">(*)</span></label>
                                    <select name="currency_id" class="form-control required">
                                        <option value="">-- Виберіть --</option>
                                        @if($currencies)
                                            @foreach($currencies as $currency)
                                                <option {{ ($currency['id'] == $product->currency_id) ? 'selected' : '' }} value="{{ $currency['id'] }}">{{ $currency['code'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Опис товару <span class="star">(*)</span></label>
                                    <textarea name="content" class="form-control required" rows="10">{{ old('content', $product->content) }}</textarea>
                                </div>
                            </div>

                            <div class="product-edit-section">
                                <h2>Деталі товару</h2>

                                <div class="form-group">
                                    <label>Бренд</label>
                                    <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="form-control" placeholder="Наприклад: Samsung">
                                </div>

                                <div class="form-group">
                                    <label>Артикул (MPN)</label>
                                    <input type="text" name="code" value="{{ old('code', $product->code) }}" class="form-control" placeholder="Код виробника">
                                </div>

                                <div class="form-group">
                                    <label>Стан товару <span class="star">(*)</span></label>
                                    <select name="condition" class="form-control required">
                                        <option value="new" {{ (old('condition', $product->condition) == 'new') ? 'selected' : '' }}>Новий</option>
                                        <option value="used" {{ (old('condition', $product->condition) == 'used') ? 'selected' : '' }}>Б/у</option>
                                        <option value="refurbished" {{ (old('condition', $product->condition) == 'refurbished') ? 'selected' : '' }}>Відновлений</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Наявність <span class="star">(*)</span></label>
                                    <select name="stock" class="form-control required">
                                        <option value="in_stock" {{ (old('stock', $product->stock) == 'in_stock') ? 'selected' : '' }}>В наявності</option>
                                        <option value="out_of_stock" {{ (old('stock', $product->stock) == 'out_of_stock') ? 'selected' : '' }}>Немає в наявності</option>
                                    </select>
                                </div>
                            </div>

                            <div class="product-edit-section">
                                <h2>Моніторинг ціни конкурента</h2>

                                <div class="form-group">
                                    <label>Посилання на аналогічний товар в іншому магазині</label>
                                    <input type="url" name="competitor_url" value="{{ old('competitor_url', $product->competitor_url) }}" class="form-control" placeholder="https://...">
                                    <span class="form-help">
                                        Якщо вказано — система регулярно перевірятиме ціну за цим посиланням
                                        і автоматично оновлюватиме ціну вашого товару, якщо вона зміниться
                                        (тільки якщо валюта на сторінці конкурента збігається з валютою товару).
                                    </span>
                                    @if($product->competitor_url)
                                        <span class="form-help">
                                            Остання перевірка: {{ optional($product->priceChecks()->latest('checked_at')->first())->checked_at ?? 'ще не перевірялось' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-action">
                                <input type="submit" name="submit" class="btn btn-step" value="Зберегти зміни">
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