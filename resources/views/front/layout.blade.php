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
    <link rel="stylesheet" href="{{ asset('assets/front/css/style.min.css') }}?v={{ filemtime(public_path('assets/front/css/style.min.css')) }}" media="all">

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
      "name": "Addnew.biz",
      "logo": "https://addnew.biz/assets/front/img/logo.png"
    }
    </script>

    @include('front.partials.og')
    @include('front.partials.hreflang')
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
            @if(Auth::check())
                <span class="nav-h">{!! __('front.welcome_user', ['email' => e(Auth::user()->email)]) !!}</span>
                <ul class="nav-mobile-links">
                    @if(Auth::user()->is_shop_owner)
                        <li><a href="{{ route('profile.shop.dashboard') }}" rel="nofollow" class="header-link">{{ __('front.my_shop') }}</a></li>
                    @else
                        <li><a href="{{ route('profile.ads') }}" rel="nofollow" class="header-link">{{ __('front.cabinet') }}</a></li>
                    @endif
                    <li><a href="{{ route('profile.favorites') }}" rel="nofollow" class="header-link">{{ app()->getLocale() === 'ru' ? 'Избранное' : 'Обране' }} <span class="fav-count" data-count="{{ count(\App\Favorite::idsFor(Auth::id())) }}">{{ count(\App\Favorite::idsFor(Auth::id())) ?: '' }}</span></a></li>
                    <li><a href="{{ route('chat.index') }}" rel="nofollow" class="header-link">{{ app()->getLocale() === 'ru' ? 'Сообщения' : 'Повідомлення' }} <span class="chat-unread-badge-mobile" style="display:none;"></span></a></li>
                    <li><a href="{{ route('ad.step.category') }}" class="header-link">{{ __('front.post_ad') }}</a></li>
                    <li><a href="{{ route('stores') }}" class="header-link">{{ __('front.all_shops_link') }}</a></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="header-link nav-mobile-logout">{{ __('sidebar.logout_button') }}</button>
                        </form>
                    </li>
                </ul>
            @else
                <span class="nav-h">{!! __('front.welcome_guest') !!}</span>
                <ul class="nav-mobile-links">
                    <li><a href="{{ route('login') }}" rel="nofollow" class="header-link">{{ __('front.login') }}</a></li>
                    <li><a href="{{ route('register') }}" rel="nofollow" class="header-link">{{ __('front.register') }}</a></li>
                    <li><a href="{{ route('ad.step.category') }}" class="header-link">{{ __('front.post_ad') }}</a></li>
                    <li><a href="{{ route('stores') }}" class="header-link">{{ __('front.all_shops_link') }}</a></li>
                </ul>
            @endif
            @php
                $mobileCurrentRouteName = \Route::currentRouteName();
                $mobileRouteParams = request()->route() ? request()->route()->parameters() : [];
                $mobileIsRu = $mobileCurrentRouteName && starts_with($mobileCurrentRouteName, 'ru.');
                $mobileUkRouteName = $mobileIsRu ? substr($mobileCurrentRouteName, 3) : $mobileCurrentRouteName;
                $mobileRuRouteName = $mobileIsRu ? $mobileCurrentRouteName : ($mobileCurrentRouteName ? 'ru.' . $mobileCurrentRouteName : null);
            @endphp
            <div class="nav-mobile-lang">
                @if($mobileUkRouteName && \Route::has($mobileUkRouteName))
                    <a href="{{ route($mobileUkRouteName, $mobileRouteParams) }}" class="header-link{{ !$mobileIsRu ? ' is-active' : '' }}">UA</a>
                @endif
                &nbsp;/&nbsp;
                @if($mobileRuRouteName && \Route::has($mobileRuRouteName))
                    <a href="{{ route($mobileRuRouteName, $mobileRouteParams) }}" class="header-link{{ $mobileIsRu ? ' is-active' : '' }}">RU</a>
                @endif
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
                    $ruRouteName = $isRu ? $currentRouteName : ($currentRouteName ? 'ru.' . $currentRouteName : null);
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
                        <a href="{{ route('profile.favorites') }}" rel="nofollow" class="header-link link-register header-fav" title="{{ app()->getLocale() === 'ru' ? 'Избранное' : 'Обране' }}">&#9829; <span class="fav-count" data-count="{{ count(\App\Favorite::idsFor(Auth::id())) }}">{{ count(\App\Favorite::idsFor(Auth::id())) ?: '' }}</span></a>
                        <a href="{{ route('chat.index') }}" rel="nofollow" class="header-link link-register" style="position:relative;">
                            {{ app()->getLocale() === 'ru' ? 'Сообщения' : 'Повідомлення' }}
                            <span id="chat-unread-badge" style="display:none; background:#e74c3c; color:#fff; border-radius:10px; font-size:11px; font-weight:700; padding:1px 6px; margin-left:4px; vertical-align:top;"></span>
                        </a>
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
<script src="{{ asset('assets/front/js/global.js') }}?v={{ filemtime(public_path('assets/front/js/global.js')) }}"></script>
<script src="{{ asset('assets/front/js/maps.js') }}"></script>
@yield('load-scripts')

@yield('script')

@auth
<script>
// Обране: перемикання сердечка без перезавантаження сторінки
(function () {
    var token = '{{ csrf_token() }}';
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.fav-btn[data-fav-url]') : null;
        if (!btn || btn.disabled) return;
        e.preventDefault();
        btn.disabled = true;
        fetch(btn.getAttribute('data-fav-url'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
            .then(function (r) {
                if (r.status === 401 || r.status === 419) { window.location.reload(); throw new Error('auth'); }
                return r.json();
            })
            .then(function (data) {
                document.querySelectorAll('.fav-btn[data-fav-url="' + btn.getAttribute('data-fav-url') + '"]').forEach(function (b) {
                    b.classList.toggle('is-active', data.active);
                    b.setAttribute('aria-pressed', data.active ? 'true' : 'false');
                    b.title = b.getAttribute(data.active ? 'data-title-on' : 'data-title-off');
                });
                document.querySelectorAll('.fav-count').forEach(function (c) {
                    c.setAttribute('data-count', data.count);
                    c.textContent = data.count > 0 ? data.count : '';
                });
            })
            .catch(function () {})
            .then(function () { btn.disabled = false; });
    });
})();
</script>
<script>
(function () {
    var badges = [document.getElementById('chat-unread-badge')]
        .concat([].slice.call(document.querySelectorAll('.chat-unread-badge-mobile')))
        .filter(Boolean);
    if (!badges.length) return;

    function updateUnread() {
        fetch('{{ route('chat.unread') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                badges.forEach(function (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            })
            .catch(function () {});
    }

    updateUnread();
    setInterval(updateUnread, 15000);
})();
</script>
@endauth
</body>
</html>