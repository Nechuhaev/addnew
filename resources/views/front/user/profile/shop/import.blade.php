@extends('front.layout')

@section('meta_title', "Импорт товаров | Доска объявлений addnew.biz")
@section('meta_description', "Импорт товаров | Доска объявлений addnew.biz")

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.import') }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>Импорт товаров</h1>

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

                    <p>Импорт формирует товары из файла <strong>CSV или XML</strong> в формате Google Merchant Center. При импорте новые товары добавляются на сайт, а существующие (определяются по полю <code>id</code>) обновляются: заменяются все поля и изображения. <a href="#how-it-works" class="scroll-link">Как это работает</a></p>

                    <div id="upload-area" class="upload-area">
                        <div id="upload-prompt">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 12px; color: #727272;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <p style="margin: 0 0 8px; font-size: 15px; color: #333;">Перетащите CSV или XML файл сюда</p>
                            <p style="margin: 0; font-size: 13px; color: #727272;">или нажмите в любое место области</p>
                            <input type="file" id="file-input" accept=".csv,.txt,.xml" style="display: none;">
                        </div>
                        <div id="upload-progress" style="display: none;">
                            <div class="spinner"></div>
                            <p style="margin-top: 12px; color: #333;">Обработка файла...</p>
                        </div>
                    </div>

                    <div id="upload-error" class="alert" style="display: none; margin-top: 16px;"></div>

                    <div id="import-progress-section" style="display: none; margin-top: 24px;">
                        <div id="import-complete-banner" style="display: none; background: #e8f7ee; border: 1px solid #27ae60; border-radius: 6px; padding: 16px 20px; margin-bottom: 12px;">
                            <p id="import-complete-text" style="margin: 0; font-weight: 600; color: #1e8449; font-size: 15px;"></p>
                            <p style="margin: 6px 0 0; font-size: 13px; color: #555;">Страница обновится автоматически через несколько секунд...</p>
                        </div>
                        <div id="import-running">
                            <div class="import-progress-bar">
                                <div class="progress-fill" id="progress-bar" style="width: 0%;"></div>
                                <span id="progress-text" class="progress-text">0%</span>
                            </div>
                            <div class="progress-info">
                                <span id="progress-details">Обработано 0 из 0</span>
                                <button id="cancel-import-btn" class="btn-link-cancel">Отменить</button>
                            </div>
                        </div>
                    </div>

                    <h2 id="how-it-works" style="margin-top: 30px; font-size: 18px;">Как это работает</h2>
                    <p>Процесс импорта состоит из двух этапов:</p>
                    <ol>
                        <li><strong>Загрузка и обработка файла</strong> — вы перетаскиваете CSV или XML файл в область выше (или выбираете вручную). Система обрабатывает файл и показывает предварительные результаты.</li>
                        <li><strong>Подтверждение импорта</strong> — в модальном окне отображается статистика (всего товаров, новых, на обновление) и список 20 случайных товаров. Вы можете подтвердить импорт или отменить его.</li>
                    </ol>

                    <h2 style="margin-top: 30px; font-size: 18px;">Требования к файлу</h2>
                    <ul>
                        <li>Форматы: <strong>CSV</strong> (разделитель — запятая) или <strong>XML</strong> (Google Merchant Center RSS)</li>
                        <li>Максимальный размер: <strong>25 МБ</strong></li>
                        <li>Кодировка: <strong>UTF-8</strong></li>
                        <li>Для CSV: первая строка файла должна содержать заголовки полей</li>
                    </ul>

                    <details class="format-details">
                        <summary>
                            <span class="format-details-title">Поля CSV файла</span>
                        </summary>
                        <div class="format-details-body">
                            <p style="margin-top: 0;">Для корректного импорта файл должен содержать следующие поля:</p>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">Поле</th>
                                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">Название</th>
                                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">Описание</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>id</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">ID товара</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Уникальный идентификатор товара. Используется для определения существующих товаров при обновлении.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>title</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Название</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Название товара (до 150 символов).</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>description</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Описание</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Текстовое описание товара. HTML-теги будут удалены.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>link</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Ссылка</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Внешняя ссылка на товар (опционально).</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>image_link</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Главное изображение</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">URL главного изображения товара.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>price</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Цена</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Цена товара с кодом валюты (напр. <code>100.00 UAH</code>).</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>availability</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Наличие</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;"><code>in_stock</code> (есть на складе) или <code>out_of_stock</code>.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>condition</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Состояние</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;"><code>new</code> (новый), <code>used</code> (б/у), <code>refurbished</code> (восстановленный).</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>brand</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Бренд</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Название бренда или производителя.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>mpn</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Артикул</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Артикул или код товара (MPN / EAN).</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>additional_image_link</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Дополнительные изображения</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">URL дополнительных изображений через запятую.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </details>

                    <details class="format-details">
                        <summary>
                            <span class="format-details-title">Поля XML файла (Google Merchant Center)</span>
                        </summary>
                        <div class="format-details-body">
                            <p style="margin-top: 0;">XML файл должен соответствовать формату <strong>Google Merchant Center RSS</strong> с пространством имён <code>g:</code>. Структура файла:</p>
                            <pre class="code-block">&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;rss xmlns:g="http://base.google.com/ns/1.0" version="2.0"&gt;
  &lt;channel&gt;
    &lt;item&gt;
      &lt;g:id&gt;123&lt;/g:id&gt;
      &lt;g:title&gt;Название товара&lt;/g:title&gt;
      &lt;g:description&gt;Описание&lt;/g:description&gt;
      &lt;g:link&gt;https://example.com/product&lt;/g:link&gt;
      &lt;g:image_link&gt;https://example.com/img.jpg&lt;/g:image_link&gt;
      &lt;g:price&gt;100.00 UAH&lt;/g:price&gt;
      &lt;g:availability&gt;in_stock&lt;/g:availability&gt;
      &lt;g:condition&gt;new&lt;/g:condition&gt;
      &lt;g:brand&gt;BrandName&lt;/g:brand&gt;
      &lt;g:mpn&gt;ART-001&lt;/g:mpn&gt;
    &lt;/item&gt;
  &lt;/channel&gt;
&lt;/rss&gt;</pre>
                            <table style="width: 100%; border-collapse: collapse; font-size: 14px; margin-top: 16px;">
                                <thead>
                                    <tr style="background: #f5f5f5;">
                                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">Поле</th>
                                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">Название</th>
                                        <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">Описание</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:id</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">ID товара</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Уникальный идентификатор. Используется для обновления существующих товаров.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:title</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Название</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Название товара.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:description</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Описание</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Текстовое описание. HTML-теги будут удалены.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:link</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Ссылка</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Внешняя ссылка на товар (опционально).</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:image_link</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Главное изображение</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">URL главного изображения товара.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:additional_image_link</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Доп. изображения</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">URL дополнительных изображений (можно повторять тег несколько раз).</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:price</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Цена</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Цена с кодом валюты: <code>100.00 UAH</code>.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:availability</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Наличие</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;"><code>in_stock</code> или <code>out_of_stock</code>.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:condition</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Состояние</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;"><code>new</code>, <code>used</code> или <code>refurbished</code>.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:brand</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Бренд</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Название бренда или производителя.</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>g:mpn</code></td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px;">Артикул</td>
                                        <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">Артикул или код товара.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </details>

                    @if(!empty($history))
                    <h2 style="margin-top: 40px; font-size: 18px;">История импортов</h2>
                    <div class="table-responsive" style="margin-top: 12px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                            <thead>
                                <tr style="background: #f5f5f5;">
                                    <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">Дата</th>
                                    <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: center;">Всего</th>
                                    <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: center;">Добавлено</th>
                                    <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: center;">Обновлено</th>
                                    <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: center;">Ошибок</th>
                                    <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: center;">Статус</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($history as $item)
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px; color: #555;">{{ $item['created_at'] }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: center;">{{ $item['total'] }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: center; color: #27ae60; font-weight: 600;">{{ $item['new_count'] }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: center; color: #f39c12; font-weight: 600;">{{ $item['update_count'] }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: center; color: {{ $item['error_count'] > 0 ? '#e74c3c' : '#727272' }};">{{ $item['error_count'] }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px; text-align: center;">
                                        @if($item['status'] === 'completed')
                                            <span class="badge-status badge-completed">Завершён</span>
                                        @elseif($item['status'] === 'cancelled')
                                            <span class="badge-status badge-cancelled">Отменён</span>
                                        @else
                                            <span class="badge-status badge-failed">Ошибка</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>

    <div id="import-confirm-modal" class="import-confirm-modal" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Подтверждение импорта</h5>
                    <button type="button" class="close" onclick="importModal.close()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="import-statistics" class="import-statistics"></div>

                    <h6 style="margin-top: 20px; margin-bottom: 10px;">20 товаров для примера:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm" id="preview-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Название</th>
                                    <th>Цена</th>
                                    <th>Бренд</th>
                                    <th>Статус</th>
                                </tr>
                            </thead>
                            <tbody id="preview-first-10"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="importModal.close()">Отменить</button>
                    <button type="button" id="confirm-import-btn" class="btn btn-primary">Подтвердить импорт</button>
                </div>
            </div>
        </div>
    </div>
    <div id="import-modal-backdrop" class="import-backdrop" style="display: none;"></div>
@endsection

@section('style')
<style>
    .upload-area {
        border: 2px dashed #ccc;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        background: #fafafa;
        transition: all 0.2s ease;
        margin-top: 16px;
        cursor: pointer;
    }
    .upload-area:hover {
        border-color: #3498db;
        background: #f0f7fc;
    }
    .upload-area.dragover {
        border-color: #3498db;
        background: #e8f4fd;
    }
    .upload-area.error {
        border-color: #e74c3c;
        background: #fde8e8;
    }
    #upload-prompt p {
        margin: 0 0 8px;
    }
    .spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .import-statistics {
        display: flex;
        gap: 20px;
        padding: 16px;
        background: #f5f5f5;
        border-radius: 6px;
    }
    .stat-item {
        text-align: center;
        flex: 1;
    }
    .stat-item .stat-number {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }
    .stat-item .stat-label {
        font-size: 13px;
        color: #727272;
        margin-top: 4px;
    }
    .import-confirm-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }
    .import-confirm-modal .modal-dialog {
        pointer-events: auto;
        margin: 30px auto;
        max-width: 900px;
        width: 90%;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    .import-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        pointer-events: auto;
    }
    .modal-content {
        border-radius: 8px;
        overflow: hidden;
    }
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #eee;
    }
    .modal-header .modal-title {
        margin: 0;
        font-size: 18px;
    }
    .modal-header .close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #727272;
    }
    .modal-body {
        padding: 20px;
        max-height: 60vh;
        overflow-y: auto;
    }
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 20px;
        border-top: 1px solid #eee;
    }
    .btn-secondary {
        background: #6c757d;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-secondary:hover {
        background: #5a6268;
    }
    .btn-primary {
        background: #3498db;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary:hover {
        background: #2980b9;
    }
    .table-sm {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    .table-sm th, .table-sm td {
        padding: 8px 12px;
        border-bottom: 1px solid #eee;
        text-align: left;
    }
    .table-sm th {
        background: #f5f5f5;
        font-weight: 600;
    }
    .badge-new {
        background: #27ae60;
        color: #fff;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
    .badge-update {
        background: #f39c12;
        color: #fff;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
    }
    .badge-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-completed {
        background: #e8f7ee;
        color: #1e8449;
    }
    .badge-cancelled {
        background: #f5f5f5;
        color: #727272;
    }
    .badge-failed {
        background: #fde8e8;
        color: #c0392b;
    }
    .scroll-link {
        color: #3498db;
        text-decoration: underline;
        cursor: pointer;
    }
    .scroll-link:hover {
        color: #2980b9;
    }
    .import-progress-bar {
        position: relative;
        width: 100%;
        height: 6px;
        background: #e8e8e8;
        border-radius: 3px;
        overflow: visible;
    }
    .progress-fill {
        height: 100%;
        background: #3498db;
        border-radius: 3px;
        transition: width 0.5s ease;
    }
    .progress-text {
        position: absolute;
        right: 0;
        top: -22px;
        font-size: 13px;
        color: #727272;
    }
    .progress-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 8px;
        font-size: 13px;
        color: #727272;
    }
    .btn-link-cancel {
        background: none;
        border: none;
        color: #e74c3c;
        font-size: 13px;
        cursor: pointer;
        padding: 0;
        text-decoration: underline;
    }
    .btn-link-cancel:hover {
        color: #c0392b;
    }
    .format-details {
        margin-top: 24px;
        border: 1px solid #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
    }
    .format-details summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #f5f5f5;
        cursor: pointer;
        user-select: none;
        list-style: none;
    }
    .format-details summary::-webkit-details-marker {
        display: none;
    }
    .format-details summary::after {
        content: '▸';
        font-size: 14px;
        color: #727272;
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }
    .format-details[open] summary::after {
        transform: rotate(90deg);
    }
    .format-details-title {
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }
    .format-details-hint {
        font-size: 12px;
        color: #999;
        margin-left: 10px;
        font-weight: normal;
    }
    .format-details[open] .format-details-hint {
        display: none;
    }
    .format-details-body {
        padding: 16px;
    }
    .code-block {
        background: #f8f8f8;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        padding: 12px 16px;
        font-size: 13px;
        line-height: 1.6;
        overflow-x: auto;
        white-space: pre;
    }
</style>
@endsection

@section('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var uploadArea = document.getElementById('upload-area');
    var fileInput = document.getElementById('file-input');
    var uploadPrompt = document.getElementById('upload-prompt');
    var uploadProgress = document.getElementById('upload-progress');
    var uploadError = document.getElementById('upload-error');
    var isUploading = false;
    var pendingFilePath = '';
    var pendingStats = {};
    var progressPollInterval = null;

    var importModal = {
        show: function () {
            document.getElementById('import-confirm-modal').style.display = 'flex';
            document.getElementById('import-modal-backdrop').style.display = 'block';
            document.body.style.overflow = 'hidden';
        },
        close: function () {
            document.getElementById('import-confirm-modal').style.display = 'none';
            document.getElementById('import-modal-backdrop').style.display = 'none';
            document.body.style.overflow = '';
            pendingFilePath = '';
            pendingStats = {};
        }
    };

    uploadArea.addEventListener('click', function (e) {
        if (!isUploading) {
            fileInput.click();
        }
    });

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function (eventName) {
        uploadArea.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
        });
    });

    ['dragenter', 'dragover'].forEach(function (eventName) {
        uploadArea.addEventListener(eventName, function () {
            uploadArea.classList.add('dragover');
        });
    });

    ['dragleave', 'drop'].forEach(function (eventName) {
        uploadArea.addEventListener(eventName, function () {
            uploadArea.classList.remove('dragover');
        });
    });

    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var files = e.dataTransfer.files;
        if (files.length && !isUploading) {
            handleFile(files[0]);
        }
    });

    fileInput.addEventListener('change', function (e) {
        e.preventDefault();
        if (fileInput.files.length && !isUploading) {
            handleFile(fileInput.files[0]);
            fileInput.value = '';
        }
    });

    function handleFile(file) {
        if (isUploading) return;
        isUploading = true;

        var ext = file.name.split('.').pop().toLowerCase();

        if (ext !== 'csv' && ext !== 'txt' && ext !== 'xml') {
            showError('Пожалуйста, выберите CSV или XML файл.');
            isUploading = false;
            return;
        }

        if (file.size > 25 * 1024 * 1024) {
            showError('Размер файла не должен превышать 25 МБ.');
            isUploading = false;
            return;
        }

        uploadPrompt.style.display = 'none';
        uploadProgress.style.display = 'block';
        uploadError.style.display = 'none';
        uploadArea.classList.remove('error');

        var formData = new FormData();
        formData.append('file', file);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('profile.shop.import.upload') }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

        xhr.onload = function () {
            isUploading = false;
            uploadProgress.style.display = 'none';
            uploadPrompt.style.display = 'block';

            if (xhr.status === 200) {
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        pendingFilePath = response.file_path;
                        pendingStats = response.data;
                        showConfirmationModal(response.data);
                    } else {
                        showError(response.message || 'Произошла ошибка при обработке файла.');
                    }
                } catch (e) {
                    showError('Неожиданный ответ сервера.');
                }
            } else if (xhr.status === 422) {
                try {
                    var errorResponse = JSON.parse(xhr.responseText);
                    var messages = [];
                    if (errorResponse.errors) {
                        for (var field in errorResponse.errors) {
                            messages = messages.concat(errorResponse.errors[field]);
                        }
                    }
                    showError(messages.join('<br>') || 'Ошибка валидации файла.');
                } catch (e) {
                    showError('Ошибка валидации файла.');
                }
            } else if (xhr.status === 413) {
                showError('Файл слишком большой. Максимальный размер: 25 МБ.');
            } else if (xhr.status === 419) {
                showError('Сессия истекла. Обновите страницу и попробуйте снова.');
            } else if (xhr.status === 403) {
                showError('Доступ запрещен. Только владельцы магазинов могут импортировать товары.');
            } else {
                var statusText = xhr.status ? ' (код: ' + xhr.status + ')' : '';
                showError('Ошибка сервера' + statusText + '. Попробуйте еще раз.');
            }
        };

        xhr.onerror = function () {
            isUploading = false;
            uploadProgress.style.display = 'none';
            uploadPrompt.style.display = 'block';
            showError('Ошибка сети. Проверьте подключение и попробуйте еще раз.');
        };

        xhr.onabort = function () {
            isUploading = false;
            uploadProgress.style.display = 'none';
            uploadPrompt.style.display = 'block';
        };

        xhr.send(formData);
    }

    function showError(message) {
        isUploading = false;
        uploadError.innerHTML = message;
        uploadError.style.display = 'block';
        uploadArea.classList.add('error');
    }

    function showConfirmationModal(data) {
        var statsHtml = '<div class="stat-item">' +
            '<div class="stat-number">' + data.total + '</div>' +
            '<div class="stat-label">Всего товаров</div>' +
            '</div>' +
            '<div class="stat-item">' +
            '<div class="stat-number">' + data.new_count + '</div>' +
            '<div class="stat-label">Новых</div>' +
            '</div>' +
            '<div class="stat-item">' +
            '<div class="stat-number">' + data.update_count + '</div>' +
            '<div class="stat-label">На обновление</div>' +
            '</div>';

        document.getElementById('import-statistics').innerHTML = statsHtml;

        var first10Html = '';
        data.preview.forEach(function (product, index) {
            var badgeClass = product.status === 'new' ? 'badge-new' : 'badge-update';
            var badgeText = product.status === 'new' ? 'Новый' : 'Обновление';
            first10Html += '<tr>' +
                '<td>' + (index + 1) + '</td>' +
                '<td>' + escapeHtml(product.title) + '</td>' +
                '<td>' + product.price + ' ' + product.currency_code + '</td>' +
                '<td>' + escapeHtml(product.brand) + '</td>' +
                '<td><span class="' + badgeClass + '">' + badgeText + '</span></td>' +
                '</tr>';
        });
        document.getElementById('preview-first-10').innerHTML = first10Html;

        importModal.show();
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    window.importModal = importModal;

    var scrollLink = document.querySelector('.scroll-link');
    if (scrollLink) {
        scrollLink.addEventListener('click', function (e) {
            e.preventDefault();
            var target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    document.getElementById('confirm-import-btn').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Запуск...';

        var formData = new FormData();
        formData.append('file_path', pendingFilePath);
        formData.append('total', pendingStats.total);
        formData.append('new_count', pendingStats.new_count);
        formData.append('update_count', pendingStats.update_count);

        var xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('profile.shop.import.confirm') }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

        xhr.onload = function () {
            importModal.close();
            btn.disabled = false;
            btn.textContent = 'Подтвердить импорт';

            if (xhr.status === 200) {
                showProgressSection();
                startProgressPolling();
            } else {
                showError('Ошибка запуска импорта.');
            }
        };

        xhr.onerror = function () {
            btn.disabled = false;
            btn.textContent = 'Подтвердить импорт';
            showError('Ошибка сети.');
        };

        xhr.send(formData);
    });

    function showProgressSection() {
        document.getElementById('import-progress-section').style.display = 'block';
        uploadArea.style.display = 'none';
    }

    function hideProgressSection() {
        document.getElementById('import-progress-section').style.display = 'none';
        uploadArea.style.display = 'block';
    }

    function showCompleteBanner(data, importId, autoReload) {
        var added = data.new_count || 0;
        var updated = data.update_count || 0;
        var errors = data.error_count || 0;
        var msg;
        if (added > 0 && updated > 0) {
            msg = 'Импорт завершён! Добавлено ' + added + ', обновлено ' + updated + ' товаров.';
        } else if (added > 0) {
            msg = 'Импорт завершён! Добавлено ' + added + ' ' + pluralize(added, 'товар', 'товара', 'товаров') + '.';
        } else if (updated > 0) {
            msg = 'Импорт завершён! Обновлено ' + updated + ' ' + pluralize(updated, 'товар', 'товара', 'товаров') + '.';
        } else {
            msg = 'Импорт завершён!';
        }
        if (errors > 0) {
            msg += ' Ошибок: ' + errors + '.';
        }
        if (importId) {
            localStorage.setItem('importShown_' + importId, '1');
        }
        document.getElementById('import-complete-text').textContent = msg;
        document.getElementById('import-complete-banner').style.display = 'block';
        document.getElementById('import-running').style.display = 'none';
        if (autoReload !== false) {
            setTimeout(function () { window.location.reload(); }, 4000);
        }
    }

    function pluralize(n, one, few, many) {
        var mod10 = n % 10;
        var mod100 = n % 100;
        if (mod10 === 1 && mod100 !== 11) return one;
        if (mod10 >= 2 && mod10 <= 4 && (mod100 < 10 || mod100 >= 20)) return few;
        return many;
    }

    function startProgressPolling() {
        if (progressPollInterval) {
            clearInterval(progressPollInterval);
        }

        progressPollInterval = setInterval(function () {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', '{{ route('profile.shop.import.progress') }}', true);
            xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

            xhr.onload = function () {
                if (xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        updateProgress(response.data);

                        if (response.data.status === 'completed') {
                            clearInterval(progressPollInterval);
                            progressPollInterval = null;
                            showCompleteBanner(response.data, response.data.id, true);
                        } else if (response.data.status === 'cancelled' || response.data.status === 'failed') {
                            clearInterval(progressPollInterval);
                            progressPollInterval = null;
                            window.location.reload();
                        }
                    }
                } else if (xhr.status === 404) {
                    clearInterval(progressPollInterval);
                    progressPollInterval = null;
                    hideProgressSection();
                }
            };

            xhr.send();
        }, 1500);
    }

    function updateProgress(data) {
        var pct = Math.min(Math.round(data.progress), 100);
        var displayed = Math.min(data.processed, data.total);
        document.getElementById('progress-text').textContent = pct + '%';
        document.getElementById('progress-bar').style.width = pct + '%';
        document.getElementById('progress-details').textContent = 'Обработано ' + displayed + ' из ' + data.total;

        if (data.status === 'cancelled') {
            document.getElementById('progress-text').textContent = 'Отменено';
            document.getElementById('progress-bar').style.background = '#e74c3c';
            document.getElementById('cancel-import-btn').style.display = 'none';
        }
    }

    document.getElementById('cancel-import-btn').addEventListener('click', function () {
        if (!confirm('Вы уверены, что хотите отменить импорт?')) {
            return;
        }

        var btn = this;
        btn.disabled = true;
        btn.textContent = 'Отмена...';

        var formData = new FormData();
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('profile.shop.import.cancel') }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

        xhr.onload = function () {
            if (xhr.status === 200) {
                if (progressPollInterval) {
                    clearInterval(progressPollInterval);
                    progressPollInterval = null;
                }
                window.location.reload();
            } else {
                btn.disabled = false;
                btn.textContent = 'Отменить';
            }
        };

        xhr.onerror = function () {
            btn.disabled = false;
            btn.textContent = 'Отменить';
        };

        xhr.send(formData);
    });

    @if($progress)
        @if($progress['status'] === 'completed' || $progress['status'] === 'failed')
            if (!localStorage.getItem('importShown_{{ $progress['id'] }}')) {
                showProgressSection();
                showCompleteBanner({
                    new_count: {{ $progress['new_count'] }},
                    update_count: {{ $progress['update_count'] }},
                    error_count: {{ $progress['error_count'] }}
                }, {{ $progress['id'] }}, false);
            }
        @else
            showProgressSection();
            updateProgress({
                status: '{{ $progress['status'] }}',
                progress: {{ $progress['progress'] }},
                processed: {{ $progress['processed'] }},
                total: {{ $progress['total'] }}
            });
            startProgressPolling();
        @endif
    @endif
});
</script>
@endsection
