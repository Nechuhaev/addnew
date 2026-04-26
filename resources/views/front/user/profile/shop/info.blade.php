@extends('front.layout')

@section('meta_title', "Информация о магазине | Доска объявлений addnew.biz")
@section('meta_description', "Информация о магазине | Доска объявлений addnew биз")

@php
    $logoUrl = $user->image;
    if ($logoUrl && strpos($logoUrl, 'http') !== 0) {
        $logoUrl = asset($logoUrl);
    } elseif (!$logoUrl) {
        $logoUrl = asset('assets/front/img/placeholder.png');
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
                    <h1>Информация о магазине</h1>

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
                            <label for="logo">Логотип магазина</label>
                            <div class="logo-preview-wrapper">
                                <img src="{{ $logoUrl }}" alt="Логотип магазина" id="logo-preview" class="logo-preview" style="max-width: 100px; max-height: 100px;">
                            </div>
                            <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control-file">
                            <small class="form-text text-muted">Допустимые форматы: JPG, PNG, GIF, WEBP. Максимальный размер: 2 МБ</small>
                        </div>

                        <div class="form-group">
                            <label for="firstname">Название магазина *</label>
                            <input type="text" name="firstname" id="firstname" class="form-control required" value="{{ old('firstname', $user->firstname) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" name="email" id="email" class="form-control required" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="telephone">Телефон</label>
                            <input type="text" name="telephone" id="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}">
                        </div>

                        <div class="form-group">
                            <label for="info">Описание магазина</label>
                            <textarea name="info" id="info" class="form-control" rows="6">{{ old('info', $user->info) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-success">Сохранить изменения</button>
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
        });
    </script>
@endsection
