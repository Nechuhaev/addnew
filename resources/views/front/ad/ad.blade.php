@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? $ad->name)

@section('meta_description', $meta['meta_description'] ?? $ad->content)

@section('load-scripts')
    <script type="text/javascript" charset="UTF-8" src="https://maps.googleapis.com/maps/api/js?language=ru&region=RU&key=AIzaSyCndp-qqA81hIeD45u32313k_MUY3um2Ds&ver=3.0"></script>
@endsection

@section('content')
    <main class="adv-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('ad.page', $ad) }}

            @if($ad->status == 'suspend')
            <div class="alert alert-success">
                {{ __('ad_page.suspended_notice') }}
            </div>
            @endif

            <div class="columns columns-nowrap" itemtype="http://schema.org/Product">
                <meta itemprop="name" content="{{ $ad->name }}" />
                <link itemprop="image" href="{{ $ad->image }}" />
                @if($ad->images && !empty($ad->images[0]))
                    @foreach($ad->images as $image)
                        <link itemprop="image" href="{{ $image }}" />
                    @endforeach
                @endif
                <meta itemprop="description" content="{{ $ad->content }}" />
                <div itemprop="offers" itemtype="http://schema.org/Offer" itemscope>
                    <link itemprop="url" href="{{ $ad->url }}" />
                    <meta itemprop="availability" content="https://schema.org/InStock" />
                    @foreach($prices as $price)
                        @if($price['selected'])
                            <meta itemprop="priceCurrency" content="{{ $price['currency'] }}" />
                            <meta itemprop="price" content="{{ $price['value'] }}" />
                        @endif
                    @endforeach
                    <div itemprop="seller" itemtype="http://schema.org/Organization" itemscope>
                        <meta itemprop="name" content="{{ $ad->user->username }}" />
                    </div>
                </div>

                <aside class="column-left hidden-xs">
                    <div class="adv-img">
                        <a href="{{ $ad->image }}" data-rel="colorbox" class="colorbox group1" title="{{ $ad->name }}">
                            <img class="img-responsive" src="{{ $ad->image }}" title="{{ $ad->name }}" alt="{{ $ad->name }}" style="opacity: 1;" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                        </a>

                        @if($ad->images && !empty($ad->images[0]))
                            <div class="adv-imgs">
                            @foreach($ad->images as $image)
                                    <a href="{{ $image }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $ad->name }} - {{ __('ad_create.image_number_suffix', ['n' => $loop->iteration]) }}">
                                        <img src="{{ $image }}" alt="{{ $ad->name . $loop->iteration }}" title="{{ $ad->name . $loop->iteration }}" width="50" height="50" style="opacity: 1;" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                                    </a>
                            @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="adv-info">
                        <h3>{{ __('ad_page.ad_number_label') }} <strong>№ {{ $ad->id }}</strong></h3>
                        <div class="adv-view">
                            <div><span>{{ __('ad_page.total_views_label') }}:</span> <strong>{{ $ad->total_views }}</strong></div>
                            <div><span>{{ __('ad_page.today_views_label') }}:</span> <strong>{{ $ad->today_views }}</strong></div>
                        </div>
                    </div>
                    <div class="adv-share">
                        <h3>Share &amp; Like</h3>
                        <ul class="share42init social-link">
                            <li><a href="https://twitter.com/intent/tweet?url={{ $ad->full_url }}&text={{ $ad->name }}" class="tw" target="_blank" rel="noreferrer"></a></li>
                            <li><a href="https://www.facebook.com/sharer/sharer.php?u={{ $ad->full_url }}" class="fb" target="_blank" rel="noreferrer" alt="Доска бесплатных объявлений Addnew.biz в социальной сети Facebook"></a></li>
                        </ul>
                    </div>
                    <div class="banner">
                        @include('front.adsense.ad-left')
                    </div>
                    @widget('front.adTags', ['tags' => $ad->tags()->get()])

                    @if(env('APP_ENV') == 'production')
                    <div class="adv-location">
                        <div class="adv-h">{{ __('ad_page.location_label') }}:</div>

                        <div id="map" style="display: block; position: relative; overflow: hidden; min-height: 300px"></div>
                    </div>
                    @endif
                    
                </aside>
                <div class="column-content">
                    <div class="adv-title">
                        <h1><span>{{ $ad->name }}</span></h1>
                        {{--<a href="#" class="adv-bookmark" title="Добавить в избранное"></a>--}}

                        <div class="adv-img visible-xs">
                            <a href="{{ $ad->image }}" data-rel="colorbox" class="colorbox group1" title="{{ $ad->name }}">
                                <img class="img-responsive" src="{{ $ad->image }}" title="{{ $ad->name }}" alt="{{ $ad->name }}" style="opacity: 1;" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                            </a>
                            @if($ad->images)
                                <div class="adv-imgs">
                                    @foreach($ad->images as $image)
                                        <a href="{{ $ad->image }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $ad->name }} - {{ __('ad_create.image_number_suffix', ['n' => $loop->iteration]) }}"><img src="{{ $ad->image }}" alt="{{ $ad->name . $loop->iteration }}" title="{{ $ad->name . $loop->iteration }}" width="50" height="50" style="opacity: 1;" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';"></a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="adv-price-block">
                            <div class="adv-price-h">
                                <span>{{ __('ad_create.price_label') }}:</span>
                                @if ($prices)
                                    <div class="adv-currency">
                                        @foreach($prices as $price)
                                            <span class="{{ ($price['selected']) ? 'active' : '' }}" data-currency="{{ $price['currency'] }}">{{ $price['currency'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="adv-price">
                                @if ($prices)
                                    @foreach($prices as $price)
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
                            <li><span>{{ __('ad_create.country_label') }}:</span><a href="{{ $ad->category->url }}">{{ $ad->city->region->country->name }}</a></li>
                            <li><span>{{ __('ad_create.author_short_label') }}:</span><a href="{{ route('author', ['id'=>$ad->user->id]) }}">{{ $ad->user->username }}</a></li>
                            <li><span>{{ __('ad_create.city_label') }}:</span><a href="{{ $ad->category->getFilteredUrl($ad->city->slug) }}">{{ $ad->city->name }}</a></li>
                            <li><span>{{ __('ad_create.date_created_label') }}:</span>{{ $ad->date_created}}</li>
                            <li><span>{{ __('ad_create.district_label') }}:</span><a href="{{ $ad->category->getFilteredUrl($ad->city->region->slug) }}">{{ $ad->city->region->name }}</a></li>
                            @if($ad->status == 'active')
                                <li><span>{{ __('ad_create.valid_until_label') }}:</span>{{ $ad->date_end }}</li>
                            @endif
                        </ul>
                        @if($ad->status == 'active')
                            <div class="adv-contacts">
                                <div class="adv-contacts-inner">
                                    <ul class="adv-contacts-list">
                                        @if($ad->is_product)
                                            <li><span>{{ __('ad_create.telephone_label') }}:</span> <a href="tel:{{ $ad->user->telephone }}">{{ $ad->user->telephone }}</a></li>
                                        @else
                                        <li><span>{{ __('ad_create.telephone_label') }}:</span> <a href="tel:{{ $ad->telephone }}">{{ $ad->telephone }}</a></li>
                                        @endif
                                        <li><span>{{ __('ad_create.email_label') }}:</span> <a href="mailto:{{ $ad->email }}">{{ $ad->email }}</a></li>
                                    </ul>
                                    <span class="btn-notice">{{ __('ad_create.show_contacts_button') }}</span>
                                </div>
                            </div>
                        @endif

                    </div>

                    @if( $ad->is_product )
                        <div class="shop_product">
                            <h2 class="display-inline-block">{{ __('ad_page.buy_prefix') }} <b>{{ $ad->name }}</b> {{ __('ad_page.buy_suffix') }}</h2>
                            <a href="{{ route('ad.trackShopLink', ['id' => $ad->id]) }}" target="_blank" rel="nofollow noopener" class="btn btn-success pull-right">{{ __('ad_page.goto_shop_button') }}</a>


                            <p style="padding-top: 20px">{{ __('ad_page.other_sellers_prefix') }} <b>{{ $ad->name }}</b></p>

                            @if($same_products->isNotEmpty())
                                <div class="other-sellers" style="margin-bottom: 30px">
                                    <div class="seller">
                                        <div class="product-name">
                                            {{ __('ad_page.product_col') }}
                                        </div>
                                        <div class="seller-name">
                                            {{ __('ad_page.seller_col') }}
                                        </div>
                                        <div class="product-price">
                                            {{ __('ad_create.price_label') }}
                                        </div>
                                        <div class="product-view text-right">
                                            {{ __('ad_page.view_col') }}
                                        </div>
                                    </div>
                                    @foreach($same_products as $same_product)
                                        <div class="seller">
                                            <div class="product-name">
                                                {{ $same_product->name }}
                                            </div>
                                            <div class="seller-name">
                                                {{ $same_product->user->username }}
                                            </div>
                                            <div class="product-price">
                                                {{ $same_product->price }} {{ $same_product->currency->symbol }}
                                            </div>
                                            <div class="product-view text-right">
                                                <a href="{{ route('ad.page', ['slug' => $same_product->slug]) }}" class="btn btn-success">{{ __('ad_page.view_button') }}</a>
                                            </div>
                                        </div>
                                        <div style="min-height: 30px"></div>
                                        @include('front.adsense.ad-after-product')
                                    @endforeach

                                </div>
                            @endif


                        </div>
                    @endif


                    <div class="adv-description">
                        <div class="adv-h">{{ __('ad_create.content_label') }}</div>
                        <p>{!! nl2br($ad->content)  !!}</p>
                    </div>



                    <div class="banner">
                        @include('front.adsense.ad-middle')
                    </div>

                    @if ($ad->email)
                    <div class="adv-callback">
                        <div class="adv-h">{{ __('ad_page.contact_heading') }}:</div>

                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
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

                        <form class="form-contact columns" action="{{ action('Front\Ad\Ad@message', ['slug' => $ad->slug]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-message">
                                <i class="icon icon-mail"></i>
                                <span>{{ __('ad_page.contact_form_intro') }}</span>
                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label>{{ __('ad_page.name_field_label') }}</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control required" aria-required="true">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>{{ __('ad_page.mail_field_label') }}</label>
                                    <input type="text" name="email" value="{{ old('email') }}" class="form-control required" aria-required="true">
                                </div>
                            </div>
                            <div class="col-1">
                                <div class="form-group">
                                    <label>{{ __('ad_page.message_field_label') }}</label>
                                    <textarea class="form-control required" name="message" aria-required="true">{{ old('message') }}</textarea>
                                </div>
                            </div>
                            <div class="col-1">
                                <div class="form-action">
                                    <button name="submit" type="submit" class="btn-form" value="{{ __('ad_page.send_request_button') }}">
                                        <i class="icon icon-plane"></i>
                                        <span class="btn-text">{{ __('ad_page.send_request_button') }}</span>
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
                    @endif

                </div> <!-- column-content -->
            </div> <!-- columns -->

            <div class="show-more">
                <section class="show-more__text">
                    {!! $meta['description']  !!}
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">{{ __('front.show_more') }}</span>
            </div>

            @if($related)
                <section>
                    <p class="section-heading">{{ __('ad_page.related_ads_heading') }}</p>
                    <div class="related-ads">
                        @foreach($related as $related_ad)
                        <div class="related-ad">
                            <div class="image">
                                <a href="{{ $related_ad->url }}" title="{{ $related_ad->name }}">
                                    <img src="{{ $related_ad->image }}" alt="{{ $related_ad->name }}" class="img-responsive" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                                </a>
                            </div>
                            <div class="price">100 грн <span class="city">Днепропетровск</span></div>
                            <a href="{{ $related_ad->url }}" title="{{ $related_ad->name }}" class="ad-heading">{{ $related_ad->name }}</a>
                        </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div> <!-- container -->
    </main>
@endsection

@section('script')
    @if (env('APP_ENV') == 'production')
    <script>
        window.addressFallback = '{{ $ad->city->region->country->name }}, {{ $ad->city->name }}';
        init_google_map('{{ $ad->city->region->country->name }}, {{ $ad->city->region->name }}, {{ $ad->city->name }}', '{{ $ad->name }}');
    </script>
    @endif
    <script>
        (function () {
            var adId = {{ $ad->id }};

            // Клік "Показати контакти" — трекінг, без впливу на існуючу
            // поведінку кнопки (просто одна додаткова fetch-подія поруч).
            var contactsBtn = document.querySelector('.btn-notice');
            if (contactsBtn) {
                contactsBtn.addEventListener('click', function () {
                    fetch('/api/track/product/' + adId, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ event: 'click_contacts' })
                    }).catch(function () {});
                });
            }

            // Орієнтовний час на сторінці — надсилається через sendBeacon
            // при виході (надійніше за звичайний AJAX, бо гарантовано
            // встигає відправитись навіть коли сторінка вже закривається).
            var startTime = Date.now();
            var sent = false;

            function sendDuration() {
                if (sent) return;
                sent = true;
                var seconds = Math.round((Date.now() - startTime) / 1000);
                if (seconds < 1) return;

                var data = JSON.stringify({ seconds: seconds });
                if (navigator.sendBeacon) {
                    var blob = new Blob([data], { type: 'application/json' });
                    navigator.sendBeacon('/api/track/product/' + adId + '/duration', blob);
                }
            }

            document.addEventListener('visibilitychange', function () {
                if (document.visibilityState === 'hidden') {
                    sendDuration();
                }
            });
            window.addEventListener('pagehide', sendDuration);
        })();
    </script>
@endsection