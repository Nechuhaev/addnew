<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>@yield('meta_title')</title>
    <!-- <base href=""> -->
    <meta name="description" content="@yield('meta_description')">

    <link href="{{ asset('assets/front/css/start.min.css') }}" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/style.min.css') }}" media="all">
</head>
<body class=""> <!-- fixed -->
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
            <a href="/main.html"><img src='{{ asset('assets/front/img/logo.png') }}' alt=""></a>
        </div>
        <div class="btn-bars">
            <i class="icon-arrow-left">&nbsp;</i>
        </div>
    </div>
    <div class="nav-inner">
        <div class="nav-account">
            <span class="nav-h">Добро пожаловать, <strong>гость</strong>!</span><br>
            <a href="/create-listing" class="header-link">Подать объявление</a><br>
            <a href="/?s=&scat=0&loc_search=&sa=search" class="header-link">Поиск по объявлениям</a><br>
            <a href="/register.html" rel="nofollow" class="header-link link-register">Регистрация</a>
        </div>
        <div class="nav-countries">
            <p class="nav-h">Поиск объявлений по странам</p>
            <div class="country-wrap">
                <a href="https://addnew.biz/ukraina/" class="country-name"><img src="{{ asset('assets/front/img/flags/ukrane.png') }}"> <span>Украина</span> <span class="btn-toggle"><i class="icon icon-plus"></i></span></a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/ukraina/kievskaya-obl/kiev">Киев <span class="city-rate">2384</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/harkovskaya-obl/harkov">Харьков <span class="city-rate">959</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/dnepropetrovskaya-obl/dnepropetrovsk">Днепропетровск <span class="city-rate">520</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/odesskaya-obl/odessa">Одесса <span class="city-rate">506</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/zaporozhskaya-obl/zaporozhe">Запорожье <span class="city-rate">276</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/lvovskaya-obl/lvov">Львов <span class="city-rate">244</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/sumskaya-obl/sumi">Сумы <span class="city-rate">158</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/hmelnitckaya-obl/hmelnitckij">Хмельницкий <span class="city-rate">143</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/nikolaevskaya-obl/nikolaev">Николаев <span class="city-rate">137</span></a></li>
                    <li><a href="https://addnew.biz/ukraina/vinnitckaya-obl/vinnitca">Винница <span class="city-rate">113</span></a></li>
                </ul>
            </div>
            <div class="country-wrap">
                <a href="https://addnew.biz/rossiya/" class="country-name"> <!-- active -->
                    <img src="{{ asset('assets/front/img/flags/russia.png') }}">
                    <span>Россия</span>
                    <span class="btn-toggle"><i class="icon icon-plus"></i></span>
                </a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/rossiya/moskva-i-moskovskaya-obl/moskva">Москва <span class="city-rate">81</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/lipetckaya-obl/lipetck">Липецк <span class="city-rate">68</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/rostovskaya-obl/rostov-na-donu">Ростов-на-Дону <span class="city-rate">41</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/sankt-peterburg-i-oblast/sankt-peterburg">Санкт-Петербург <span class="city-rate">27</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/novosibirskaya-obl/novosibirsk">Новосибирск <span class="city-rate">14</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/krasnodarskij-kraj/sochi">Сочи <span class="city-rate">14</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/ryazanskaya-obl/ryazan">Рязань <span class="city-rate">10</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/sverdlovskaya-obl/ekaterinburg">Екатеринбург <span class="city-rate">10</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/moskva-i-moskovskaya-obl/noginsk">Ногинск <span class="city-rate">8</span></a></li>
                    <li><a href="https://addnew.biz/rossiya/tatarstan/naberezhnie-chelni">Набережные Челны <span class="city-rate">8</span></a></li>
                </ul>
            </div>
            <div class="country-wrap">
                <a href="https://addnew.biz/kitaj/" class="country-name">
                    <img src="{{ asset('assets/front/img/flags/china.png') }}">
                    <span>Китай</span>
                    <span class="btn-toggle"><i class="icon icon-plus"></i></span>
                </a>
                <ul class="cities-list">
                    <li><a href="https://addnew.biz/kitaj/hejluntczyan/harbin">Харбин <span class="city-rate">214</span></a></li>
                    <li><a href="https://addnew.biz/kitaj/gansu/lanchzhou">Ланьчжоу <span class="city-rate">9</span></a></li>
                    <li><a href="https://addnew.biz/kitaj/pekin/pekin">Пекин <span class="city-rate">3</span></a></li>
                    <li><a href="https://addnew.biz/kitaj/hebej/shihajkvang">Шихайкванг <span class="city-rate">2</span></a></li>
                    <li><a href="https://addnew.biz/kitaj/guandon/zhenzhen">Женьжень <span class="city-rate">2</span></a></li>
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
                    <a href="/main.html"><img src='{{ asset('assets/front/img/logo.png') }}' alt=""></a>
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
                        <a href="/create-advert.html" class="btn btn-advert"><i class="icon icon-plus"></i> Подать объявление</a>

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
                <li><a href="/">Главная</a></li>
                <li><a href="{{ route('blog.index') }}">Блог</a></li>
                <li><a href="{{ route('countries') }}">Страны</a></li>
                <li><a href="#">Контакты</a></li>
                <li><a href="#">Confide</a></li>
            </ul>
            <div class="btn btn-subscribe modal" data-modal="modal-subscribe">Подписаться</div>
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
        <div class="copyright">© 2019 Доска объявлений AddNew.Biz. Все права защищены.</div>
    </div>
</footer>
<div class="toTop"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/arrow-up-alt2.svg') }}" /></div>
<div id="subscribe-shadow">
    <div class="subscribe-wrap">
        <button id="subscribe-close" class="modal-close">
            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/no-alt.svg') }}" />
        </button>

        <div class="thanks">
            Спасибо!<br><br>
            Проверьте свою почту для подтверждения подписки.
            <button class="btn btn-subscribe modal-close">Закрыть</button>
        </div>
        <div class="form-subscribe">
            <div class="form-group">
                <label>Ваше имя <span class="star">*</span></label>
                <input type="text" class="form-control">
            </div>
            <div class="form-group">
                <label>Ваша электронная почта <span class="star">*</span></label>
                <input type="text" class="form-control">
            </div>
            <button class="btn btn-subscribe btn-submit">Подписаться</button>
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
<script>

    // Автокомплит для городов поиск
    var options = {
        url: function(phrase) {
            if (phrase.length > 2 && phrase.length < 6 ) {
                return "/api/ad/city/autocomplete/" + phrase;
            }
        },
        getValue: "name",
        listLocation: "data",
        list: {
            onChooseEvent: function () {
                $('#search_city_id').val($("#autocomplete_c").getSelectedItemData().id);
            }
        }
    };
    $("#autocomplete_c").easyAutocomplete(options);

    // Подтягиваем категорию при выборе родительской для поиска
    $('#search_category').on('change', function () {
        var value = $(this).val();
        console.log(value);

        $('#search_sub_category').prop('disabled', true);

        var sub_categories = '<option value="0">Искать во всей категории</option>';
        $.getJSON("/api/ad/category/children/" + value, function ( data ) {
            console.log(data.data);
            $.each( data.data, function ( key, val ) {
                sub_categories += "<option value='" + val.id + "'>" + val.name + "</option>";
            } )

            $('#search_sub_category').html(sub_categories);
            $('#search_sub_category').prop('disabled', false);
        });
    })
</script>

</body>
</html>