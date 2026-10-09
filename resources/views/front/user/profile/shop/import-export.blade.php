@extends('front.layout')

@section('meta_title', "Импорт/экспорт товаров")
@section('meta_description', "Импорт/экспорт товаров")

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.import-export') }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('shop.import_export_heading') }}</h1>

                    @if(session()->has('success'))
                        <div class="alert success">
                            {{ session()->get('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="columns" style="margin-top: 10px;">
                        <div class="col-2">
                            <a href="{{ route('profile.shop.import') }}" class="ie-card">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; border-radius: 50%; background: #eef2fa; margin-bottom: 18px;">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="7" y="4" width="20" height="26" rx="2" stroke="#19346c" stroke-width="2" fill="#fff"/>
                                        <line x1="11" y1="11" x2="23" y2="11" stroke="#19346c" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="11" y1="15" x2="23" y2="15" stroke="#19346c" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="11" y1="19" x2="18" y2="19" stroke="#19346c" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="30" y1="22" x2="30" y2="35" stroke="#3b5998" stroke-width="2.2" stroke-linecap="round"/>
                                        <polyline points="25,30 30,36 35,30" stroke="#3b5998" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                    </svg>
                                </div>
                                <h2 style="margin: 0 0 10px; font-size: 18px;">{{ __('shop_import.heading') }}</h2>
                                <p style="color: #727272; font-size: 14px; line-height: 1.5; margin-bottom: 22px;">{{ __('shop.import_card_text') }}</p>
                                <span class="btn">{{ __('shop.go_link') }}</span>
                            </a>
                        </div>

                        <div class="col-2">
                            <a href="{{ route('profile.shop.export') }}" class="ie-card">
                                <div style="display: inline-flex; align-items: center; justify-content: center; width: 80px; height: 80px; border-radius: 50%; background: #eef2fa; margin-bottom: 18px;">
                                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="7" y="10" width="20" height="26" rx="2" stroke="#19346c" stroke-width="2" fill="#fff"/>
                                        <line x1="11" y1="17" x2="23" y2="17" stroke="#19346c" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="11" y1="21" x2="23" y2="21" stroke="#19346c" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="11" y1="25" x2="18" y2="25" stroke="#19346c" stroke-width="1.8" stroke-linecap="round"/>
                                        <line x1="30" y1="18" x2="30" y2="5" stroke="#3b5998" stroke-width="2.2" stroke-linecap="round"/>
                                        <polyline points="25,10 30,4 35,10" stroke="#3b5998" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                    </svg>
                                </div>
                                <h2 style="margin: 0 0 10px; font-size: 18px;">{{ __('shop.export_heading') }}</h2>
                                <p style="color: #727272; font-size: 14px; line-height: 1.5; margin-bottom: 22px;">{{ __('shop.export_card_text') }}</p>
                                <span class="btn">{{ __('shop.go_link') }}</span>
                            </a>
                        </div>
                    </div>

                    @php($ru = app()->getLocale() === 'ru')
                    <div class="ie-info">
                        <h2>{{ $ru ? 'Какие файлы можно загрузить' : 'Які файли можна завантажити' }}</h2>
                        <ul>
                            <li><strong>YML</strong> (.xml, .yml) — {{ $ru ? 'файл выгрузки товаров для Prom.ua, Rozetka, Hotline' : 'файл вивантаження товарів для Prom.ua, Rozetka, Hotline' }}</li>
                            <li><strong>XML Google Merchant Center</strong> (RSS) — {{ $ru ? 'фид для Google Покупок' : 'фід для Google Покупок' }}</li>
                            <li><strong>CSV</strong> — {{ $ru ? 'первая строка — названия колонок:' : 'перший рядок — назви колонок:' }}
                                <code>id, title, description, link, image_link, price, availability, condition, brand, mpn, gtin</code></li>
                        </ul>
                        <p>{{ $ru ? 'Размер файла — до 25 МБ. Повторная загрузка обновляет товары с тем же id (цена, наличие, фото) и добавляет новые.' : 'Розмір файлу — до 25 МБ. Повторне завантаження оновлює товари з тим самим id (ціна, наявність, фото) і додає нові.' }}</p>

                        <h2>{{ $ru ? 'Заполняйте бренд, артикул и штрихкод' : 'Заповнюйте бренд, артикул і штрихкод' }}</h2>
                        <p>{{ $ru ? 'По ним мы находим такой же товар у других магазинов: ваше предложение появится в блоке «Другие продавцы» на их страницах, и покупатель увидит вашу цену рядом.' : 'За ними ми знаходимо такий самий товар в інших магазинах: ваша пропозиція з\'явиться в блоці «Інші продавці» на їхніх сторінках, і покупець побачить вашу ціну поруч.' }}</p>
                        <div class="ie-table-wrap">
                            <table class="ie-table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>YML</th>
                                        <th>Google Merchant</th>
                                        <th>CSV</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr><td>{{ $ru ? 'Бренд' : 'Бренд' }}</td><td><code>vendor</code></td><td><code>g:brand</code></td><td><code>brand</code></td></tr>
                                    <tr><td>{{ $ru ? 'Артикул производителя' : 'Артикул виробника' }}</td><td><code>vendorCode</code></td><td><code>g:mpn</code></td><td><code>mpn</code></td></tr>
                                    <tr><td>{{ $ru ? 'Штрихкод (EAN/GTIN)' : 'Штрихкод (EAN/GTIN)' }}</td><td><code>barcode</code></td><td><code>g:gtin</code></td><td><code>gtin</code> / <code>ean</code></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <style>
                        .ie-card {
                            display: block;
                            text-align: center;
                            padding: 36px 24px 28px;
                            border: 1px solid #eee;
                            box-shadow: 3px 3px 9px rgba(0,0,0,.1);
                            text-decoration: none;
                            color: inherit;
                            transition: box-shadow .2s, border-color .2s;
                        }
                        .ie-card:hover {
                            box-shadow: 3px 3px 16px rgba(0,0,0,.18);
                            border-color: #c5cfe8;
                            text-decoration: none;
                            color: inherit;
                        }
                        .ie-info {
                            margin-top: 30px;
                            padding: 20px 24px;
                            border: 1px solid #e1e6ef;
                            border-radius: 6px;
                            background: #f8f9fc;
                            font-size: 14px;
                            line-height: 1.55;
                        }
                        .ie-info h2 { font-size: 17px; margin: 0 0 10px; }
                        .ie-info h2 + ul, .ie-info p { margin: 0 0 16px; }
                        .ie-info ul { padding-left: 18px; }
                        .ie-info li { margin-bottom: 6px; }
                        .ie-info code { background: #eef2fa; padding: 1px 5px; border-radius: 3px; font-size: 13px; word-break: break-word; }
                        .ie-table-wrap { overflow-x: auto; }
                        .ie-table { border-collapse: collapse; width: 100%; min-width: 420px; background: #fff; }
                        .ie-table th, .ie-table td { border: 1px solid #e1e6ef; padding: 7px 10px; text-align: left; }
                        .ie-table th { background: #eef2fa; }
                        .ie-table code { white-space: nowrap; word-break: normal; }
                        .ie-card:hover .btn {
                            background-color: #3b5998;
                            border-color: #3b5998;
                        }
                    </style>
                </div>
                @include('front.sidebars.user')
            </div>

        </div>
    </main>
@endsection