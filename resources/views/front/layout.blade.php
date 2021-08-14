<!DOCTYPE html>
<html lang="ru">
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
            <a href="{{ route('index') }}"><img src='{{ asset('assets/front/img/logo.png') }}' alt=""></a>
        </div>
        <div class="btn-bars">
            <i class="icon-arrow-left">&nbsp;</i>
        </div>
    </div>
    <div class="nav-inner">
        <div class="nav-account">
            <span class="nav-h">Добро пожаловать, <strong>гость</strong>!</span><br>
            <a href="{{ route('ad.step.category') }}" class="header-link">Подать объявление</a><br>
            <a href="/?s=&scat=0&loc_search=&sa=search" class="header-link">Поиск по объявлениям</a><br>
            <a href="{{ route('register') }}" rel="nofollow" class="header-link link-register">Регистрация</a>
        </div>
        <div class="nav-countries">
            <p class="nav-h">Поиск объявлений по странам</p>
            <div class="country-wrap">
                <a href="https://addnew.biz/regions/ukraina" class="country-name"><img src="{{ asset('assets/front/img/flags/ukrane.png') }}"> <span>Украина</span> <span class="btn-toggle"><i class="icon icon-plus"></i></span></a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/regions/ukraina/kievskaya-obl/kiev">Киев <span class="city-rate">2384</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/harkovskaya-obl/harkov">Харьков <span class="city-rate">959</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/dnepropetrovskaya-obl/dnepropetrovsk">Днепропетровск <span class="city-rate">520</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/odesskaya-obl/odessa">Одесса <span class="city-rate">506</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/zaporozhskaya-obl/zaporozhe">Запорожье <span class="city-rate">276</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/lvovskaya-obl/lvov">Львов <span class="city-rate">244</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/sumskaya-obl/sumi">Сумы <span class="city-rate">158</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/hmelnitckaya-obl/hmelnitckij">Хмельницкий <span class="city-rate">143</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/nikolaevskaya-obl/nikolaev">Николаев <span class="city-rate">137</span></a></li>
                    <li><a href="https://addnew.biz/regions/ukraina/vinnitckaya-obl/vinnitca">Винница <span class="city-rate">113</span></a></li>
                </ul>
            </div>
            <div class="country-wrap">
                <a href="https://addnew.biz/regions/rossiya" class="country-name"> <!-- active -->
                    <img src="{{ asset('assets/front/img/flags/russia.png') }}">
                    <span>Россия</span>
                    <span class="btn-toggle"><i class="icon icon-plus"></i></span>
                </a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/regions/rossiya/moskva-i-moskovskaya-obl/moskva">Москва <span class="city-rate">81</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/lipetckaya-obl/lipetck">Липецк <span class="city-rate">68</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/rostovskaya-obl/rostov-na-donu">Ростов-на-Дону <span class="city-rate">41</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/sankt-peterburg-i-oblast/sankt-peterburg">Санкт-Петербург <span class="city-rate">27</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/novosibirskaya-obl/novosibirsk">Новосибирск <span class="city-rate">14</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/krasnodarskij-kraj/sochi">Сочи <span class="city-rate">14</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/ryazanskaya-obl/ryazan">Рязань <span class="city-rate">10</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/sverdlovskaya-obl/ekaterinburg">Екатеринбург <span class="city-rate">10</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/moskva-i-moskovskaya-obl/noginsk">Ногинск <span class="city-rate">8</span></a></li>
                    <li><a href="https://addnew.biz/regions/rossiya/tatarstan/naberezhnie-chelni">Набережные Челны <span class="city-rate">8</span></a></li>
                </ul>
            </div>
            <div class="country-wrap">
                <a href="https://addnew.biz/regions/kitaj" class="country-name">
                    <img src="{{ asset('assets/front/img/flags/china.png') }}">
                    <span>Китай</span>
                    <span class="btn-toggle"><i class="icon icon-plus"></i></span>
                </a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/regions/kitaj/hejluntczyan/harbin">Харбин <span class="city-rate">214</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/gansu/lanchzhou">Ланьчжоу <span class="city-rate">9</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/pekin/pekin">Пекин <span class="city-rate">3</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/hebej/shihajkvang">Шихайкванг <span class="city-rate">2</span></a></li>
                    <li><a href="https://addnew.biz/regions/kitaj/guandon/zhenzhen">Женьжень <span class="city-rate">2</span></a></li>
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
                    <a href="{{ route('index') }}"><img src='{{ asset('assets/front/img/logo.png') }}' alt=""></a>
                </div>

                <div class="header-account">
                    @if(Auth::check())
                        <span class="header-welcome">Добро пожаловать, <strong>{{ Auth::user()->email }}</strong>!</span>
                        <a href="{{ route('profile.ads') }}" rel="nofollow" class="header-link link-register">Кабинет</a>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();"
                           rel="nofollow" class="header-link link-login">Выход</a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @else
                        <span class="header-welcome">Добро пожаловать, <strong>гость</strong>!</span>
                        <a href="{{ route('register') }}" rel="nofollow" class="header-link link-register">Регистрация</a>
                        <a href="{{ route('login') }}" rel="nofollow" class="header-link link-login">Вход</a>
                    @endif
                        <a href="{{ route('ad.step.category') }}" class="btn btn-advert"><i class="icon icon-plus"></i> Подать объявление</a>

                </div>

            </div>
        </div>
    </div>

    @widget('front.search')

</header>

<div style="padding: 48px;margin-top: 100px;background: #000; color: #fff; text-align: center; font-size: 16px">
    http://addnew.biz
</div>
@yield('content')



<footer class="footer">
    <div class="container">
        <div class="footer-inner">
            <ul class="footer-menu">
                <li><a href="/">Главная</a></li>
                <li><a href="{{ route('blog.index') }}">Блог</a></li>
{{--                <li><a href="{{ route('countries') }}">Страны</a></li>--}}
                <li><a href="{{ route('contacts') }}">Контакты</a></li>
                @if($pages)
                    @foreach($pages as $page)
                        <li><a href="{{ $page->url }}">{{ $page->name }}</a></li>
                    @endforeach
                @endif
            </ul>
            <div class="btn btn-subscribe" onclick="modal.set('subscribe-modal').show();">Подписаться</div>
            <ul class="footer-social">
                <li><a href="https://vk.com/public131156262" class="vk" target="_blank" rel="noreferrer" alt="Доска бесплатных объявлений Addnew.biz в социальной сети Вконтакте"></a></li>
                <li><a href="https://www.facebook.com/addnew.biz/" class="fb" target="_blank" rel="noreferrer" alt="Доска бесплатных объявлений Addnew.biz в социальной сети Facebook"></a></li>
                <li><a href="https://www.instagram.com/addnewbiz/" class="in" target="_blank" rel="noreferrer"></a></li>
                <li><a href="https://addnewbiz.business.site/" class="plus" target="_blank"></a></li>
                <li><a href="https://twitter.com/AddnewBiz" class="tw" target="_blank" rel="noreferrer"></a></li>
                <li><a href="https://my.mail.ru/community/addnew.biz/" class="mail" target="_blank" rel="noreferrer"></a></li>
                <li><a href="https://ok.ru/group/54246475890813/" class="ok" target="_blank" rel="noreferrer"></a></li>
            </ul>
        </div>
        <div class="copyright">© {{ date("Y") }} Доска объявлений AddNew.Biz. Все права защищены.</div>
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
                <label>Ваша электронная почта <span class="star">*</span></label>
                <input type="text" name="subscriber_email" id="subscriber_email" class="form-control">
            </div>
            <p class="error-holder"></p>
            <button class="btn btn-subscribe btn-subscribe-trigger">Подписаться</button>
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