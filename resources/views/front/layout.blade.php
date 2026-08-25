<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-M89V7L');</script>
    <!-- End Google Tag Manager -->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>@yield('meta_title')</title>
    <!-- <base href=""> -->
    <meta name="description" content="@yield('meta_description')">

    <link href="{{ asset('assets/front/css/start.min.css') }}" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/style.min.css') }}" media="all">

    @yield('style')

    <link rel="icon" type="image/png" href="/favicon.png" />

    @if(request()->get('page'))
        @if(starts_with(request()->path(), 'r/ukraina/'))
            <link rel="canonical" href="{{ url(str_replace('r/ukraina/', '', request()->path())) }}" />
        @else
            <link rel="canonical" href="{{ url()->current() }}" />
        @endif
    @elseif(starts_with(request()->path(), 'r/ukraina/'))
        <link rel="canonical" href="{{ url(str_replace('r/ukraina/', '', request()->path())) }}" />
    @else
        <link rel="canonical" href="{{ url()->current() }}" />
    @endif

    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "url": "https://addnew.biz",
      "email": "info@addnew.biz",
      "name": "ADDNEW.BIZ",
      "logo": "https://addnew.biz/assets/front/img/logo.png",
      "potentialAction": [{
          "@type": "SearchAction",
          "target": "https://addnew.biz//search?s={search_term_string}&cat_id=0&sub_cat_id=0&city_id=0",
          "query-input": "required name=search_term_string"
      }]
    }
    </script>

</head>
<body class=""> <!-- fixed -->
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-M89V7L"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<div class="debugGrid">
    <div>
        <div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div>
<div class="nav-mobile"> <!-- open -->
    <div class="nav-top">
        <div class="header-logo">
            <a href="{{ route('index') }}"><img src='{{ asset('assets/front/img/logo.png') }}' alt="{{ __('front.logo_alt') }}"></a>
        </div>
        <div class="btn-bars">
            <i class="icon-arrow-left">&nbsp;</i>
        </div>
    </div>
    <div class="nav-inner">
        <div class="nav-account">
            <span class="nav-h">{!! __('front.welcome_guest') !!}</span><br>
            <a href="{{ route('ad.step.category') }}" class="header-link">{{ __('front.post_ad') }}</a><br>
            <a href="/?s=&scat=0&loc_search=&sa=search" class="header-link">{{ __('front.search_ads') }}</a><br>
            <a href="{{ route('register') }}" rel="nofollow" class="header-link link-register">{{ __('front.register') }}</a><br>
            @php
                $mobileCurrentRouteName = \Route::currentRouteName();
                $mobileRouteParams = request()->route() ? request()->route()->parameters() : [];
                $mobileIsRu = $mobileCurrentRouteName && starts_with($mobileCurrentRouteName, 'ru.');
                $mobileUkRouteName = $mobileIsRu ? substr($mobileCurrentRouteName, 3) : $mobileCurrentRouteName;
                $mobileRuRouteName = $mobileIsRu ? $mobileCurrentRouteName : 'ru.' . $mobileCurrentRouteName;
            @endphp
            <div style="margin-top:10px;">
                @if($mobileUkRouteName && \Route::has($mobileUkRouteName))
                    <a href="{{ route($mobileUkRouteName, $mobileRouteParams) }}" class="header-link" style="{{ !$mobileIsRu ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.6;' }}">UA</a>
                @endif
                &nbsp;/&nbsp;
                @if($mobileRuRouteName && \Route::has($mobileRuRouteName))
                    <a href="{{ route($mobileRuRouteName, $mobileRouteParams) }}" class="header-link" style="{{ $mobileIsRu ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.6;' }}">RU</a>
                @endif
            </div>
        </div>
        <div class="nav-countries">
            <p class="nav-h">{{ __('front.search_by_countries') }}</p>
            <div class="country-wrap">
                <a href="https://addnew.biz/regions/ukraina" class="country-name"><img src="{{ asset('assets/front/img/flags/ukrane.png') }}"> <span>{{ __('front.country_ukraine') }}</span> <span class="btn-toggle"><i class="icon icon-plus"></i></span></a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/regions/ukraina/kievskaya-obl/kiev">{{ __('sidebar_cities.kiev') }} <span class="city-rate">2384</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/harkovskaya-obl/harkov">{{ __('sidebar_cities.harkov') }} <span class="city-rate">959</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/dnepropetrovskaya-obl/dnepropetrovsk">{{ __('sidebar_cities.dnepr') }} <span class="city-rate">520</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/odesskaya-obl/odessa">{{ __('sidebar_cities.odessa') }} <span class="city-rate">506</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/zaporozhskaya-obl/zaporozhe">{{ __('sidebar_cities.zaporozhe') }} <span class="city-rate">276</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/lvovskaya-obl/lvov">{{ __('sidebar_cities.lvov') }} <span class="city-rate">244</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/sumskaya-obl/sumi">{{ __('sidebar_cities.sumy') }} <span class="city-rate">158</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/hmelnitckaya-obl/hmelnitckij">{{ __('sidebar_cities.hmelnitskiy') }} <span class="city-rate">143</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/nikolaevskaya-obl/nikolaev">{{ __('sidebar_cities.nikolaev') }} <span class="city-rate">137</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/vinnitckaya-obl/vinnitca">{{ __('sidebar_cities.vinnitsa') }} <span class="city-rate">113</span></a></li>
                </ul>
            </div>
            <div class="country-wrap">
                <a href="https://addnew.biz/regions/rossiya" class="country-name"> <!-- active -->
                    <img src="{{ asset('assets/front/img/flags/russia.png') }}">
                    <span>{{ __('front.country_russia') }}</span>
                    <span class="btn-toggle"><i class="icon icon-plus"></i></span>
                </a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/regions/rossiya/moskva-i-moskovskaya-obl/moskva">{{ __('sidebar_cities.moskva') }} <span class="city-rate">81</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/lipetckaya-obl/lipetck">{{ __('sidebar_cities.lipetsk') }} <span class="city-rate">68</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/rostovskaya-obl/rostov-na-donu">{{ __('sidebar_cities.rostov') }} <span class="city-rate">41</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/sankt-peterburg-i-oblast/sankt-peterburg">{{ __('sidebar_cities.spb') }} <span class="city-rate">27</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/novosibirskaya-obl/novosibirsk">{{ __('sidebar_cities.novosibirsk') }} <span class="city-rate">14</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/krasnodarskij-kraj/sochi">{{ __('sidebar_cities.sochi') }} <span class="city-rate">14</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/ryazanskaya-obl/ryazan">{{ __('sidebar_cities.ryazan') }} <span class="city-rate">10</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/sverdlovskaya-obl/ekaterinburg">{{ __('sidebar_cities.ekaterinburg') }} <span class="city-rate">10</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/moskva-i-moskovskaya-obl/noginsk">{{ __('sidebar_cities.noginsk') }} <span class="city-rate">8</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/tatarstan/naberezhnie-chelni">{{ __('sidebar_cities.chelny') }} <span class="city-rate">8</span></a></li>
                </ul>
            </div>
            <div class="country-wrap">
                <a href="https://addnew.biz/regions/kitaj" class="country-name">
                    <img src="{{ asset('assets/front/img/flags/china.png') }}">
                    <span>{{ __('front.country_china') }}</span>
                    <span class="btn-toggle"><i class="icon icon-plus"></i></span>
                </a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/regions/kitaj/hejluntczyan/harbin">{{ __('sidebar_cities.harbin') }} <span class="city-rate">214</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/gansu/lanchzhou">{{ __('sidebar_cities.lanchzhou') }} <span class="city-rate">9</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/pekin/pekin">{{ __('sidebar_cities.pekin') }} <span class="city-rate">3</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/hebej/shihajkvang">{{ __('sidebar_cities.shihajkvang') }} <span class="city-rate">2</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/guandon/zhenzhen">{{ __('sidebar_cities.zhenzhen') }} <span class="city-rate">2</span></a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<header class="header">
    <div class="header-top">
        <div class="container">
            <div class="header-row">
                <div class="btn-bars">
                    <i class="icon-bars">&nbsp;</i>
                </div>
                <div class="header-logo">
                    <a href="{{ route('index') }}"><img src='{{ asset('assets/front/img/logo.png') }}' alt="{{ __('front.logo_alt') }}"></a>
                </div>

                @php
                    $currentRouteName = \Route::currentRouteName();
                    $routeParams = request()->route() ? request()->route()->parameters() : [];
                    $isRu = $currentRouteName && starts_with($currentRouteName, 'ru.');
                    $ukRouteName = $isRu ? substr($currentRouteName, 3) : $currentRouteName;
                    $ruRouteName = $isRu ? $currentRouteName : 'ru.' . $currentRouteName;
                @endphp
                <style>
                    /* Перемикач мов у десктопній шапці — ховаємо на мобільних,
                       щоб не ламати flex-розкладку .header-row (там на мобільних
                       вже своя логіка через .nav-mobile/.btn-bars). */
                    @media (max-width: 767px) {
                        .lang-switcher-desktop { display: none !important; }
                    }
                </style>
                <div class="lang-switcher lang-switcher-desktop" style="display:flex; align-items:center; gap:6px; margin-left:10px; margin-right:15px; font-size:13px;">
                    @if($ukRouteName && \Route::has($ukRouteName))
                        <a href="{{ route($ukRouteName, $routeParams) }}" style="color:#fff; {{ !$isRu ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.6;' }}">UA</a>
                    @endif
                    @if($ruRouteName && \Route::has($ruRouteName))
                        <a href="{{ route($ruRouteName, $routeParams) }}" style="color:#fff; {{ $isRu ? 'font-weight:700; text-decoration:underline;' : 'opacity:0.6;' }}">RU</a>
                    @endif
                </div>

                <div class="header-account">
                    @if(Auth::check())
                        <span class="header-welcome">{!! __('front.welcome_user', ['email' => e(Auth::user()->email)]) !!}</span>
                        @if(Auth::user()->is_shop_owner)
                            <a href="{{ route('profile.shop.dashboard') }}" rel="nofollow" class="header-link link-register">{{ __('front.my_shop') }}</a>
                        @else
                            <a href="{{ route('profile.ads') }}" rel="nofollow" class="header-link link-register">{{ __('front.cabinet') }}</a>
                        @endif

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @else
                        <span class="header-welcome">{!! __('front.welcome_guest') !!}</span>
                        <a href="{{ route('register') }}" rel="nofollow" class="header-link link-register">{{ __('front.register') }}</a>
                        <a href="{{ route('login') }}" rel="nofollow" class="header-link link-login">{{ __('front.login') }}</a>
                    @endif
                        <a href="{{ route('ad.step.category') }}" class="btn btn-advert"><i class="icon icon-plus"></i> {{ __('front.post_ad') }}</a>

                </div>

            </div>
        </div>
    </div>

    @widget('front.search')

</header>


@yield('content')



<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <ul class="footer-menu">
                <li><a href="/">{{ __('front.home') }}</a></li>
                <li><a href="{{ route('blog.index') }}">{{ __('front.blog') }}</a></li>
                <li><a href="{{ route('country.regions') }}">{{ __('front.regions') }}</a></li>
{{--                <li><a href="{{ route('countries') }}">Страны</a></li>--}}
                <li><a href="{{ route('contacts') }}">{{ __('front.contacts') }}</a></li>
                @if($pages)
                    @foreach($pages as $page)
                        <li><a href="{{ $page->url }}">{{ $page->name }}</a></li>
                    @endforeach
                @endif
            </ul>
            <div class="btn btn-subscribe" onclick="modal.set('subscribe-modal').show();">{{ __('front.subscribe') }}</div>
        </div>
        <div class="copyright">© {{ date("Y") }} {{ __('front.copyright_text') }}</div>
    </div>
</footer>
<div class="toTop"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/arrow-up-alt2.svg') }}" /></div>

<div id="subscribe-modal" class="modal">
    <div class="modal-wrap">
        <button class="close" onclick="modal.close()">
            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/no-alt.svg') }}" />
        </button>

        <div class="form-subscribe">
            <div class="form-group">
                <label>{{ __('front.your_email') }} <span class="star">*</span></label>
                <input type="text" name="subscriber_email" id="subscriber_email" class="form-control">
            </div>
            <p class="error-holder"></p>
            <button class="btn btn-subscribe btn-subscribe-trigger">{{ __('front.subscribe') }}</button>
        </div>
    </div>
</div>


<div class="backdrop"></div>

<style>
    .start-page{
        min-height: calc(100% - 87px);
    }
    .start-page h1{
        font-size: 27px;
        margin-top: 0;
        padding-top: 50px;
        color: #000;
        display: block;
    }
    .markup-menu li{
        margin: 10px 0;
    }
    .markup-menu li a{
        font-size: 18px;
    }
</style>
<script src="{{ asset('assets/front/js/common.js') }}"></script>
<script src="{{ asset('assets/front/js/global.js') }}"></script>
<script src="{{ asset('assets/front/js/maps.js') }}"></script>
@yield('load-scripts')

@yield('script')

</body>
</html>