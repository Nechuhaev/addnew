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
                    <h1>Импорт/экспорт товаров</h1>

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
                                <h2 style="margin: 0 0 10px; font-size: 18px;">Импорт товаров</h2>
                                <p style="color: #727272; font-size: 14px; line-height: 1.5; margin-bottom: 22px;">Загрузите прайс-лист или CSV-файл с товарами для массового добавления на площадку.</p>
                                <span class="btn">Перейти</span>
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
                                <h2 style="margin: 0 0 10px; font-size: 18px;">Экспорт товаров</h2>
                                <p style="color: #727272; font-size: 14px; line-height: 1.5; margin-bottom: 22px;">Выгрузите данные о ваших товарах в формате CSV для использования в Google Merchant Center.</p>
                                <span class="btn">Перейти</span>
                            </a>
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
