@extends('front.layout')

@section('meta_title', "Экспорт товаров | Доска объявлений addnew.biz")
@section('meta_description', "Экспорт товаров | Доска объявлений addnew.biz")

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.shop.export') }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>{{ __('shop.export_heading') }}</h1>

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

                    <p>{!! __('shop_export.intro_text') !!}</p>

                    <div class="form-group">
                        @if($fileUrl)
                            <p style="margin-bottom: 8px; color: #727272; font-size: 13px;">
                                {{ __('shop_export.last_export_label') }} <strong>{{ $lastGeneratedAt }}</strong>
                            </p>
                            <div style="position: relative;">
                                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                    <input type="text"
                                           id="export-url"
                                           class="form-control"
                                           value="{{ $fileUrl }}"
                                           readonly
                                           style="flex: 1; min-width: 200px; cursor: pointer;"
                                           title="{{ __('shop_export.copy_link_title') }}">
                                    <a href="{{ $fileUrl }}" class="btn" download style="white-space: nowrap;">
                                        {{ __('shop_export.download_csv_button') }}
                                    </a>
                                    @if($hasChanges)
                                        <form action="{{ route('profile.shop.export.generate') }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn" style="white-space: nowrap;">
                                                {{ __('shop_export.regenerate_button') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <span id="copy-feedback" style="display: none; position: absolute; top: 100%; left: 0; margin-top: 4px; color: #27ae60; font-size: 13px;">
                                    {{ __('shop_export.copied_feedback') }}
                                </span>
                            </div>
                        @else
                            <p style="margin-bottom: 10px; color: #727272;">
                                {{ __('shop_export.not_generated_yet') }}
                            </p>
                            <form action="{{ route('profile.shop.export.generate') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn">{{ __('shop_export.run_export_button') }}</button>
                            </form>
                        @endif
                    </div>

                    <h2 style="margin-top: 30px; font-size: 18px;">{{ __('shop_export.fields_heading') }}</h2>
                    <p>{{ __('shop_export.fields_intro') }}</p>

                    <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">{{ __('shop_import.th_field') }}</th>
                                <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">{{ __('shop_import.th_field_name') }}</th>
                                <th style="border: 1px solid #ddd; padding: 8px 12px; text-align: left;">{{ __('shop_import.th_field_description') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($fields as $field)
                                <tr>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px;"><code>{{ $field['header'] }}</code></td>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px;">{{ $field['label'] }}</td>
                                    <td style="border: 1px solid #ddd; padding: 8px 12px; color: #727272;">{{ $field['description'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.getElementById('export-url');
            if (!input) return;

            input.addEventListener('click', function () {
                input.select();
                input.setSelectionRange(0, 99999);

                navigator.clipboard.writeText(input.value).then(function () {
                    showFeedback();
                }).catch(function () {
                    document.execCommand('copy');
                    showFeedback();
                });
            });

            function showFeedback() {
                var feedback = document.getElementById('copy-feedback');
                feedback.style.display = 'inline';
                setTimeout(function () { feedback.style.display = 'none'; }, 3000);
            }
        });
    </script>
@endsection