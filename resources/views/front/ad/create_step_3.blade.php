@extends('front.layout')

@section('meta_title', "Шаг 3. Подать бесплатное объявление | Доска объявлений AddNew.Biz Украина")

@section('meta_description', "Доска объявлений AddNew.biz предлагает разместить бесплатное объявление любой тематики в нашем каталоге. Подать объявление могут зарегистрированные и незарегистрированные пользователи")

@section('content')
    <main class="steps-page">
        <div class="container">
            {{--<div class="banner">--}}
                {{--@include('front.adsense.top')--}}
            {{--</div>--}}

            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">{{ __('front.home') }}</a></li>
                <li><span>{{ __('ad_create.step_preview') }}</span></li>
            </ul>

            <ul class="steps-row" data-steps="4">
                <li class="steps-done">{{ __('ad_create.step_category') }}</li>
                <li class="steps-done">{{ __('ad_create.step_details') }}</li>
                <li class="steps-done">{{ __('ad_create.step_preview') }}</li>
                <li class="steps-todo">{{ __('ad_create.step_thanks') }}</li>
            </ul>
            <div class="steps-content" id="step-3">
                <h2>{{ __('ad_create.step1_heading_prefix') }} <span>{{ __('ad_create.step_preview') }}</span></h2>
                <div class="steps-preview">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <p>{{ __('ad_create.preview_intro') }}</p>
                </div>
                <hr>
                <div class="columns">
                    <aside class="column-left hidden-xs">
                        <div class="adv-img">
                            <a href="{{ $preview['image'] }}" data-rel="colorbox" class="colorbox group1" title="{{ $preview['name'] }}">
                                <img class="img-responsive" src="{{ $preview['image'] }}" title="{{ $preview['name'] }}" alt="{{ $preview['name'] }}" style="opacity: 1;">
                            </a>

                            @if($preview['images'])
                                <div class="adv-imgs">
                                    @foreach($preview['images'] as $image)
                                        <a href="{{ $image }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $preview['name'] }} - {{ __('ad_create.image_number_suffix', ['n' => $loop->iteration]) }}">
                                            <img src="{{ $image }}" alt="{{ $preview['name'] }}" title="{{ $preview['name'] }}" width="50" height="50" style="opacity: 1;">
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </aside>
                    <div class="column-content">
                        <div class="adv-title">
                            <h1><span>{{ $preview['name'] }}</span></h1>

                            <div class="adv-img visible-xs">
                                <a href="{{ $preview['image'] }}" data-rel="colorbox" class="colorbox group1" title="{{ $preview['name'] }}">
                                    <img class="img-responsive" src="{{ $preview['image'] }}" title="{{ $preview['name'] }}" alt="{{ $preview['name'] }}" style="opacity: 1;">
                                </a>
                                @if($preview['images'])
                                    <div class="adv-imgs">
                                        @foreach($preview['images'] as $image)
                                            <a href="{{ $preview['image'] }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $preview['name'] }} - {{ __('ad_create.image_number_suffix', ['n' => $loop->iteration]) }}"><img src="{{ $preview['image'] }}" alt="{{ $preview['name'] }}" title="{{ $preview['name'] }}" width="50" height="50" style="opacity: 1;"></a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>


                            <div class="adv-price-block">
                                <div class="adv-price-h">
                                    <span>{{ __('ad_create.price_label') }}:</span>
                                    @if ($preview['prices'])
                                    <div class="adv-currency">
                                        @foreach($preview['prices'] as $price)
                                        <span class="{{ ($price['selected']) ? 'active' : '' }}" data-currency="{{ $price['currency'] }}">{{ $price['currency'] }}</span>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                <div class="adv-price">
                                    @if ($preview['prices'])
                                        @foreach($preview['prices'] as $price)
                                            <span class="{{ $price['currency'] }} {{ ($price['selected']) ? 'active' : '' }}">{{ $price['value'] }}</span>
                                            @if($price['selected'])
                                                <em class="currency-sign">{{ $price['currency'] }}</em>
                                            @endif
                                        @endforeach
                                    @else
                                        <span class="active">{{ __('ad_create.free_label') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="adv-meta">
                            <ul class="adv-meta-list">
                                <li><span>{{ __('ad_create.country_label') }}:</span><a href="#">{{ $preview['city']->region->country->name }}</a></li>
                                <li><span>{{ __('ad_create.author_short_label') }}:</span>{{ $preview['author'] }}</li>
                                <li><span>{{ __('ad_create.city_label') }}:</span><a href="#">{{ $preview['city']->name }}</a></li>
                                {{-- Дати нижче захардкоджені в оригіналі (28.08.2019/26.11.2019) —
                                     існуючий баг, не пов'язаний з перекладом, свідомо не чіпаю. --}}
                                <li><span>{{ __('ad_create.date_created_label') }}:</span>28.08.2019г.</li>
                                <li><span>{{ __('ad_create.district_label') }}:</span><a href="#">{{ $preview['city']->region->name }}</a></li>
                                <li><span>{{ __('ad_create.valid_until_label') }}:</span>26.11.2019г.</li>
                            </ul>
                            <div class="adv-contacts">
                                <div class="adv-contacts-inner">
                                    <ul class="adv-contacts-list">
                                        <li><span>{{ __('ad_create.telephone_label') }}:</span> <a href="tel:{{ str_replace([" ", "-", "(", ")"], "", $preview['telephone']) }}">{{ $preview['telephone'] }}</a></li>
                                        <li><span>{{ __('ad_create.email_label') }}:</span> <a href="mailto:{{ $preview['email'] }}">{{ $preview['email'] }}</a></li>
                                    </ul>
                                    <span class="btn-notice">{{ __('ad_create.show_contacts_button') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="adv-description">
                            <div class="adv-h">{{ __('ad_create.content_label') }}</div>
                            <p>{{ $preview['content'] }}</p>
                        </div>
                        <form action="#" class="form-steps">
                            <div class="form-action">
                                <a href="{{ route('ad.step.details') }}" id="step1" class="btn btn-step btn-change">{{ __('ad_create.change_link') }}</a>
                                <a onclick="return create_ad();" class="btn btn-step btn-publish">{{ __('ad_create.publish_button') }}</a>
                            </div>
                        </form>
                    </div> <!-- column-content -->
                </div>

            </div>
        </div> <!-- container -->
    </main>

    @if (!Auth::check())
    <div id="login-modal" class="modal visible">
        <div class="modal-wrap">
            <button class="close" onclick="modal.close()">
                <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/no-alt.svg') }}" />
            </button>

            <div class="form-subscribe">
                <p>{{ __('ad_create.existing_account_notice', ['email' => $preview['email']]) }}</p>
                <form method="POST" class="form-login" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label>{{ __('front.your_email') }} <span class="star">*</span></label>
                        <input type="email" name="email" value="{{ $preview['email'] }}" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ __('ad_create.password_label') }} <span class="star">*</span></label>
                        <input type="password" name="password" id="customer_password" class="form-control">
                    </div>
                    <button class="btn btn-submit" onclick="auth_and_create_ad();">{{ __('ad_create.authorize_button') }}</button>
                </form>
            </div>

        </div>
    </div>
    @endif
    
    <div id="errors-modal" class="modal visible">
        <div class="modal-wrap">
            <button class="close" onclick="modal.close()">
                <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/no-alt.svg') }}" />
            </button>
            <div class="modal-content">
                <ul></ul>
            </div>
            <a href="{{ route('ad.step.details') }}" class="btn btn-submit">{{ __('ad_create.edit_ad_button') }}</a>
        </div>
    </div>

@endsection

@section('script')
    <script>
        function create_ad() {
            $('.btn-publish').css('opacity', '0.7');
            $.getJSON('{{ route('ad.step.creating') }}', function(data) {
                console.log(data);
                if (data.auth == "required") {
                    modal.set('login-modal').show();
                } else if (data.errors) {
                    var errors_html = '';
                    $.each(data.errors, function (i, e) {
                        $.each(e, function (k, v) {
                            errors_html += "<li>" + v + "</li>";
                        })
                    })
                    $('#errors-modal .modal-content ul').html(errors_html);

                    modal.set('errors-modal').show();
                } else if (data.redirect) {
                    window.location = data.redirect;
                }

                $('.btn-publish').css('opacity', '1');
            })
            return false;
        }

    </script>
@endsection