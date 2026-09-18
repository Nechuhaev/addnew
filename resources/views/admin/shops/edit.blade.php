@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Магазин: {{ $shop->username }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    @if (Breadcrumbs::exists('admin.shops.edit'))
                        {{ Breadcrumbs::render('admin.shops.edit', $shop) }}
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.shops') }}" class="btn btn-sm btn-secondary mb-3">&larr; До списку магазинів</a>
                    <a href="{{ route('admin.shops.products', $shop->id) }}" class="btn btn-sm btn-secondary mb-3">Товари цього магазину</a>
                    <a href="{{ route('admin.shops.impersonate', $shop->id) }}" class="btn btn-sm btn-warning mb-3"
                       onclick="return confirm('Увійти в акаунт магазину «{{ $shop->username }}»?');">
                        Увійти як магазин
                    </a>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $logoUrl = $shop->image;
                        if ($logoUrl && strpos($logoUrl, 'http') !== 0) {
                            $logoUrl = asset($logoUrl);
                        } elseif (!$logoUrl) {
                            $logoUrl = asset('assets/front/img/placeholder.png');
                        }
                        $bannerUrl = $shop->banner;
                        if ($bannerUrl && strpos($bannerUrl, 'http') !== 0) {
                            $bannerUrl = asset($bannerUrl);
                        }
                    @endphp

                    <form action="{{ route('admin.shops.update', $shop->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="logo">Логотип магазину</label>
                            <div class="logo-preview-wrapper">
                                <img src="{{ $logoUrl }}" alt="Логотип магазину" id="logo-preview" style="max-width: 100px; max-height: 100px; display:block; margin-bottom: 10px;">
                            </div>
                            <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                            <small class="form-text text-muted">Рекомендований розмір: 200×200px (квадратне зображення). Допустимі формати: JPG, PNG, GIF, WEBP. Максимальний розмір файлу: 2 МБ</small>
                        </div>

                        <div class="form-group">
                            <label for="banner">Баннер магазину</label>
                            <div class="banner-preview-wrapper">
                                <img src="{{ $bannerUrl ?: '' }}" alt="Баннер магазину" id="banner-preview" style="max-width: 100%; max-height: 200px; {{ $bannerUrl ? '' : 'display:none;' }} margin-bottom: 10px;">
                            </div>
                            <input type="file" name="banner" id="banner" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                            @if($bannerUrl)
                                <div class="form-check" style="margin-top: 8px;">
                                    <input type="checkbox" name="delete_banner" value="1" id="delete_banner" class="form-check-input">
                                    <label for="delete_banner" class="form-check-label">Видалити поточний банер</label>
                                </div>
                            @endif
                            <small class="form-text text-muted">Рекомендований розмір: 1200×300px (широкий формат, показується на всю ширину сторінки /author/{id}). Максимальний розмір файлу: 3 МБ.</small>
                        </div>

                        <div class="form-group">
                            <label for="firstname">Назва магазину *</label>
                            <input type="text" name="firstname" id="firstname" class="form-control required" value="{{ old('firstname', $shop->firstname) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" class="form-control required" value="{{ old('email', $shop->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Телефон</label>
                            <input type="text" name="telephone" id="telephone" class="form-control" value="{{ old('telephone', $shop->telephone) }}">
                        </div>

                        <div class="form-group">
                            <label for="country">Страна</label>
                            <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $shop->country) }}" placeholder="Наприклад: Україна">
                        </div>

                        <div class="form-group">
                            <label for="site_url">Посилання на сайт магазину</label>
                            <input type="url" name="site_url" id="site_url" class="form-control" value="{{ old('site_url', $shop->site_url) }}" placeholder="https://...">
                            @if($shop->site_url)
                                <small class="form-text">
                                    <a href="{{ $shop->site_url }}" target="_blank" rel="noopener noreferrer">{{ $shop->site_url }} &#8599;</a>
                                </small>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="info">Опис магазину (оригінал)</label>
                            <textarea name="info" id="info" class="form-control" rows="6">{{ old('info', $shop->getOriginal('info')) }}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="info_uk">Опис магазину (українською)</label>
                            <textarea name="info_uk" id="info_uk" class="form-control" rows="6">{{ old('info_uk', $shop->getOriginal('info_uk')) }}</textarea>
                            <small class="form-text text-muted">
                                Якщо заповнено — відвідувачі з українською локаллю побачать цей варіант
                                замість оригіналу. Можна заповнити вручну або автоматично командою
                                <code>php artisan shops:translate-info</code>.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success">Зберегти зміни</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2>Написати магазину</h2>
                    <p class="text-muted">Лист буде надіслано на {{ $shop->email }}</p>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('admin.shops.message', $shop->id) }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="subject">Тема</label>
                            <input type="text" name="subject" id="subject" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="body">Текст листа</label>
                            <textarea name="body" id="body" class="form-control content" rows="10" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">Надіслати</button>
                    </form>

                    @if($messages->isNotEmpty())
                        <hr>
                        <h4>Історія листування</h4>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Тема</th>
                                        <th>Статус</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($messages as $message)
                                        <tr>
                                            <td>{{ $message->created_at->format('d.m.Y H:i') }}</td>
                                            <td>
                                                {{ $message->subject }}
                                                <br>
                                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($message->body, 100) }}</small>
                                            </td>
                                            <td>
                                                @if($message->sent_successfully)
                                                    <span class="badge badge-success">Надіслано</span>
                                                @else
                                                    <span class="badge badge-danger" title="{{ $message->error_message }}">Помилка</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logo-preview');
    if (logoInput) {
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
    }

    const bannerInput = document.getElementById('banner');
    const bannerPreview = document.getElementById('banner-preview');
    if (bannerInput) {
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
    }
});
</script>
@endsection
