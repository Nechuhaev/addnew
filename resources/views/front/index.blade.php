@extends('front.layout')

@section('content')
    <main class="home-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            @if($categories)
                <div class="columns">
                @foreach($categories as $category)
                        <div class="col">

                            @foreach($category as $parent_category)
                                <ul class="catalog">
                                <li class="first first-64">
                                    <img src="{{ asset($parent_category->image) }}" alt="" class="catalog-img">
                                    <a href="https://addnew.biz/transport/">{{ $parent_category->name }}</a>
                                </li>
                                @if($parent_category->children->count())
                                    @foreach($parent_category->children as $child)
                                    <li><a href="{{ $child->url }}">{{ $child->name }}</a></li>
                                    @endforeach
                                @endif
                                </ul>
                            @endforeach
                            </ul>
                        </div>
                @endforeach
                </div>
            @endif
            <div class="columns">
                <div class="col">
                    <ul class="catalog">
                        <li class="first first-64">
                            <img src="{{ asset('assets/front/img/icons/icon-1.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/transport/">Транспорт</a></li>
                        <li><a href="https://addnew.biz/transport/transport-logistika/">Транспорт / логистика</a></li>
                        <li><a href="https://addnew.biz/transport/legkovyie-avtomobili/">Легковые автомобили</a></li>
                        <li><a href="https://addnew.biz/transport/gruzovyie-avtomobili/">Грузовые автомобили</a></li>
                        <li><a href="https://addnew.biz/transport/avtobusyi/">Автобусы</a></li>
                        <li><a href="https://addnew.biz/transport/moto/">Мото</a></li>
                        <li><a href="https://addnew.biz/transport/zapchasti-aksessuaryi/">Запчасти, аксессуары</a></li>
                        <li><a href="https://addnew.biz/transport/spetstehnika/">Спецтехника</a></li>
                        <li><a href="https://addnew.biz/transport/selhoztehnika/">Сельхозтехника</a></li>
                        <li><a href="https://addnew.biz/transport/vodnyiy-transport/">Водный транспорт</a></li>
                        <li><a href="https://addnew.biz/transport/pritsepyi/">Прицепы</a></li>
                        <li><a href="https://addnew.biz/transport/vozdushnyiy-transport/">Воздушный транспорт</a></li>
                        <li><a href="https://addnew.biz/transport/shini-diski/">Шины и диски</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-65">
                            <img src="{{ asset('assets/front/img/icons/icon-2.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/biznes-i-uslugi/">Бизнес и услуги</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/stroitelstvo-remont-uborka/">Строительство / ремонт / уборка</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/finansovyie-uslugi-partnerstvo/">Финансовые услуги / партнерство</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/perevozki-arenda-transporta/">Перевозки / аренда транспорта</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/reklama-poligrafiya-marketing-internet/">Реклама / полиграфия / маркетинг / интернет</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/nyani-sidelki/">Няни / сиделки</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/uslugi-agentstv-nedvizhimosti/">Услуги агентств недвижимости</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/krasota-zdorove/">Красота, здоровье</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/byitovyie-uslugi/">Бытовые услуги</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/ritualnyie-uslugi/">Ритуальные услуги</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/ohrannyie-uslugi/">Охранные услуги</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/seminaryi-treningi/">Семинары, тренинги</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/prodazha-biznesa/">Продажа бизнеса</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/razvlecheniya-iskusstvo-foto-video/">Развлечения / Искусство / Фото / Видео</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/turizm-immigratsiya/">Туризм / иммиграция</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/uslugi-perevodchikov-nabor-teksta/">Услуги переводчиков / набор текста</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/avto-moto-uslugi/">Авто / мото услуги</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/obsluzhivanie-remont-tehniki/">Обслуживание, ремонт техники</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/setevoy-marketing/">Сетевой маркетинг</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/yuridicheskie-uslugi/">Юридические услуги</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/prokat-tovarov/">Прокат товаров</a></li>
                        <li><a href="https://addnew.biz/biznes-i-uslugi/prochie-uslugi/">Прочие услуги</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-72">
                            <img src="{{ asset('assets/front/img/icons/icon-3.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/hobbi-otdyih-i-sport/">Хобби</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/znakomstva-i-kontaktyi/">Знакомства и контакты</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/antikvariat-kollektsii/">Антиквариат / коллекции</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/muzyikalnyie-instrumentyi/">Музыкальные инструменты</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/sport-otdyih/">Спорт / отдых</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/knigi-zhurnalyi/">Книги / журналы</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/cd-dvd-plastinki-kassetyi/">CD / DVD / пластинки / кассеты</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/biletyi/">Билеты</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/poisk-poputchikov/">Поиск попутчиков</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/poisk-grupp-muzyikantov/">Поиск групп / музыкантов</a></li>
                        <li><a href="https://addnew.biz/hobbi-otdyih-i-sport/drugoe/">Другое</a></li>
                    </ul>
                </div>

                <div class="col">
                    <ul class="catalog">
                        <li class="first first-67">
                            <img src="{{ asset('assets/front/img/icons/icon-4.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/nedvizhimost-2/">Недвижимость</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/snimu/">Сниму</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/arenda-kvartir/">Аренда квартир</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/arenda-komnat/">Аренда комнат</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/arenda-domov/">Аренда домов</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/arenda-pokupka-zemli/">Аренда и покупка земли</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/arenda-garazhey-stoyanok/">Аренда гаражей / стоянок</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/arenda-pomeshheniy/">Аренда помещений</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/prodazha-kvartir/">Продажа квартир</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/prodazha-komnat/">Продажа комнат</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/prodazha-domov/">Продажа домов</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/kuplyu/">Куплю</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/prodazha-pomeshheniy/">Продажа помещений</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/prodazha-garazhey-stoyanok/">Продажа гаражей / стоянок</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/obmen-nedvizhimosti/">Обмен недвижимости</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/ishhu-kompanona/">Ищу компаньона</a></li>
                        <li><a href="https://addnew.biz/nedvizhimost-2/prodazha-nedvizhimosti-za-rubezhom/">Продажа недвижимости за рубежом</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-70">
                            <img src="{{ asset('assets/front/img/icons/icon-5.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/elektronika/">Электроника</a></li>
                        <li><a href="https://addnew.biz/elektronika/telefonyi/">Телефоны</a></li>
                        <li><a href="https://addnew.biz/elektronika/kompyuteryi/">Компьютеры</a></li>
                        <li><a href="https://addnew.biz/elektronika/fototehnika/">Фототехника</a></li>
                        <li><a href="https://addnew.biz/elektronika/tv-videotehnika/">Тв / видеотехника</a></li>
                        <li><a href="https://addnew.biz/elektronika/audiotehnika/">Аудиотехника</a></li>
                        <li><a href="https://addnew.biz/elektronika/igryi-i-igrovyie-pristavki/">Игры и игровые приставки</a></li>
                        <li><a href="https://addnew.biz/elektronika/tehnika-dlya-doma/">Техника для дома</a></li>
                        <li><a href="https://addnew.biz/elektronika/tehnika-dlya-kuhni/">Техника для кухни</a></li>
                        <li><a href="https://addnew.biz/elektronika/klimaticheskoe-oborudovanie/">Климатическое оборудование</a></li>
                        <li><a href="https://addnew.biz/elektronika/individualnyiy-uhod/">Индивидуальный уход</a></li>
                        <li><a href="https://addnew.biz/elektronika/aksessuaryi-i-komplektuyushhie/">Аксессуары и комплектующие</a></li>
                        <li><a href="https://addnew.biz/elektronika/prochaya-elektronika/">Прочая электроника</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-2986">
                            <img src="{{ asset('assets/front/img/icons/icon-6.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/otdam-darom/">Отдам даром</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-68">
                            <img src="{{ asset('assets/front/img/icons/icon-7.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/zhivotnyie/">Животные</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/sobaki/">Собаки</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/koshki/">Кошки</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/akvariumistika/">Аквариумистика</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/ptitsyi/">Птицы</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/gryizunyi/">Грызуны</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/reptilii/">Рептилии</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/selhoz-zhivotnyie/">Сельхоз животные</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/zhivotnyie-darom/">Животные даром</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/zootovaryi/">Зоотовары</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/vyazka/">Вязка</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/drugie-zhivotnyie/">Другие животные</a></li>
                        <li><a href="https://addnew.biz/zhivotnyie/byuro-nahodok/">Бюро находок</a></li>
                    </ul>
                </div>

                <div class="col">
                    <ul class="catalog">
                        <li class="first first-63">
                            <img src="{{ asset('assets/front/img/icons/icon-8.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/detskiy-mir/">Детский мир</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/detskaya-obuv/">Детская обувь</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/detskaya-odezhda/">Детская одежда</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/detskie-kolyaski/">Детские коляски</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/detskaya-mebel/">Детская мебель</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/detskie-avtokresla/">Детские автокресла</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/detskiy-transport/">Детский транспорт</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/igrushki/">Игрушки</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/prochie-detskie-tovaryi/">Прочие детские товары</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/tovaryi-dlya-buduschih-mam/">Товары для будущих мам</a></li>
                        <li><a href="https://addnew.biz/detskiy-mir/tovaryi-dlya-shkolnikov/">Товары для школьников</a></li>
                    </ul>
                    <div class="banner">
                        <img src="{{ asset('assets/front/img/banners/banner-3.jpg') }}" alt="">
                    </div>
                    <ul class="catalog">
                        <li class="first first-66">
                            <img src="{{ asset('assets/front/img/icons/icon-9.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/rabota/">Работа</a></li>
                        <li><a href="https://addnew.biz/rabota/ishhu-rabotu/">Ищу работу</a></li>
                        <li><a href="https://addnew.biz/rabota/predlagayu-rabotu/">Предлагаю работу</a></li>
                    </ul>
                </div>

                <div class="col">
                    <ul class="catalog">
                        <li class="first first-69">
                            <img src="{{ asset('assets/front/img/icons/icon-10.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/dom-i-sad/">Дом и сад</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/kantstovaryi-rashodnyie-materialyi/">Канцтовары / расходные материалы</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/mebel/">Мебель</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/produktyi-pitaniya-napitki/">Продукты питания / напитки</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/sad-ogorod/">Сад / огород</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/predmetyi-interera/">Предметы интерьера</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/komnatnyie-rasteniya/">Комнатные растения</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/posuda-kuhonnaya-utvar/">Посуда / кухонная утварь</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/sadovyiy-inventar/">Садовый инвентарь</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/hozyaystvennyiy-inventar-byitovaya-himiya/">Хозяйственный инвентарь / бытовая химия</a></li>
                        <li><a href="https://addnew.biz/dom-i-sad/prochie-tovaryi-dlya-doma/">Прочие товары для дома</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-2982">
                            <img src="{{ asset('assets/front/img/icons/icon-11.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/stroitelstvo-i-remont/">Строительство и ремонт</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/stroitelnyie-materialyi/">Строительные материалы</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/zamki-i-furnitura/">Замки и фурнитура</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/santehnika/">Сантехника</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/nasosyi/">Насосы</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/drugoe-stroitelstvo-i-remont/">Другое</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/dveri/">Двери</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/vorota-i-zaboryi/">Ворота и заборы</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/ventilyatsionnyie-sistemyi/">Вентиляционные системы</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/instrumentyi-stroitelstvo-i-remont/">Инструменты</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/okna/">Окна</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/lestnitsyi/">Лестницы</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/elektrika/">Электрика</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/metallokonstruktsii/">Металлоконструкции</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/otdelochnyie-i-oblitsovochnyie-materialyi/">Отделочные и облицовочные материалы</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/balkonyi/">Балконы</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/otoplenie/">Отопление</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/gotovyie-konstruktsii/">Готовые конструкции</a></li>
                        <li><a href="https://addnew.biz/stroitelstvo-i-remont/arenda/">Аренда</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-71">
                            <img src="{{ asset('assets/front/img/icons/icon-12.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/moda-i-stil/">Мода и стиль</a></li>
                        <li><a href="https://addnew.biz/moda-i-stil/odezhda-obuv/">Одежда / обувь</a></li>
                        <li><a href="https://addnew.biz/moda-i-stil/dlya-svadbyi/">Для свадьбы</a></li>
                        <li><a href="https://addnew.biz/moda-i-stil/naruchnyie-chasyi/">Наручные часы</a></li>
                        <li><a href="https://addnew.biz/moda-i-stil/aksessuaryi/">Аксессуары</a></li>
                        <li><a href="https://addnew.biz/moda-i-stil/podarki/">Подарки</a></li>
                        <li><a href="https://addnew.biz/moda-i-stil/krasota-zdorove-moda-i-stil/">Красота / здоровье</a></li>
                        <li><a href="https://addnew.biz/moda-i-stil/moda-raznoe/">Мода разное</a></li>
                    </ul>
                    <ul class="catalog">
                        <li class="first first-2989">
                            <img src="{{ asset('assets/front/img/icons/icon-13.png') }}" alt="" class="catalog-img">
                            <a href="https://addnew.biz/oborudovanie-2/">Оборудование</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/torgovoe-oborudovanie/">Торговое оборудование</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/proizvodstvennoe-oborudovanie/">Производственное оборудование</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/stroitelnoe-oborudovanie/">Строительное оборудование</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/kuplyu-oborudovanie-2/">Куплю</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/oborudovanie-dlya-sfer-uslug/">Оборудование для сфер услуг</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/drugoe-oborudovanie-2/">Другое</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/metalloiskateli/">Металлоискатели</a></li>
                        <li><a href="https://addnew.biz/oborudovanie-2/ruchnoy-i-elektroinstrument/">Ручной и электроинструмент</a></li>
                    </ul>
                </div>
            </div>

            <div class="show-more hidden">
                <section class="show-more__text">
                    <h1>Доска объявлений Addnew.biz, бесплатные объявления в Вашем городе</h1>
                    <p style="text-align: justify;">Онлайн доска бесплатных объявлений Addnew.biz - это тысячи городов по всем странам. Миллионы посетителей и покупателей недвижимости, автомобилей, электроники, продуктов питания, животных и различных услуг. Addnew.biz – это частные бесплатные объявления новых и б/у товаров по доступным ценам.
                        Каждый может пополнить свой кошелек дополнительным доходом в считанные секунды. Вам даже не нужно регистрироваться или платить за публикацию объявления. Достаточно выбрать одну из множества категорий - Детский мир, Бизнес, финансы, Оборудование, Отдать даром и ожидать мгновенного ответа от покупателей. Ваш быстрый доход начинается с <a href="http://addnew.biz/create-listing/">подачи объявления</a>.</p>
                    <p style="text-align: justify;">Команда доски постоянно работает над дизайном и юзабилити, чтобы пользователям было комфортно и они могли в короткий срок как найти так и подать объявление.</p>

                    <h2 style="text-align: center;">Доска частных объявлений для честных людей</h2>
                    <p style="text-align: justify;">На сайте доска бесплатных объявлений Addnew.biz представлены отзывы и фотографии товаров, чтобы Вы могли выбрать лучшее. Для нас важно чтобы Вы доверяли продавцу, а соответственно и нам.
                        Addnew.biz рассчитан не только на одноразовые объявления, онлайн доска Addnew - это дополнительная реклама для Вашего бизнеса и дела. Быстрый и прямой выход на клиентов и потенциальных покупателей. Доска бесплатных частных объявлений охватывает абсолютно различные сферы жизни, начиная от возможностей найти идеальную работу и заканчивая категориями cпорта, отдыха, продажи детских вещей и объявлений о знакомствах.</p>

                    <h3 style="text-align: center;">Как подать бесплатное объявление в Интернете?</h3>
                    <p style="text-align: justify;">Ответ довольно очевидный - используйте Addnew.biz. Вы занимаетесь ремонтом, но никто об этом не знает, пора заявить о себе людям. В категории «Строительство, ремонт» продаются не только двери, окна, ворота и заборы, замки и фурнитура, но и надаются различные услуги по установке сантехники, вентиляционных систем, отопления и много других работ для дома и семьи. И если Вы думаете, что просто <strong>подать бесплатное объявление</strong> будет не достаточно, на нашем сайте есть возможность выделить Ваше предложение, поставить его на первое место в ТОП, чтобы гарантировано продать свой товар или услуги.</p>

                    <h4 style="text-align: center;">Сайт доска бесплатных объявлений во всем мире - Addnew.biz</h4>
                    <p style="text-align: justify;">Для удачной сделки необходимо несколько ключевых факторов: полная конфиденциальность Вашей личной информации и открытость заявок на доске объявлений. Сайт доска бесплатных объявлений Украины - Addnew.biz гарантирует это.</p>
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">Показать</span>
            </div>

            <div class="banner">
                <img src="{{ asset('assets/front/img/banners/banner-2.jpg') }}" alt="">
            </div>

            <h2 class="last-advs-header">Последние объявления</h2>
            <div class="last-advs">
                <a href="https://addnew.biz/ads/0990088307-ishhu-kvartirantov-0639497658">
                        <span class="last-adv-title">
                            <img src="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2019/08/22083401/604539-100x100.jpg">
                            <strong> 0990088307 ищу квартирантов 0639497658 </strong>
                        </span>
                    <span class="last-adv-price"> 1 евро. </span>
                </a>
                <a href="https://addnew.biz/ads/prodam-opt-sigarety-jin-ling-25-sht">
                        <span class="last-adv-title">
                            <img src="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2019/08/22064729/864200-100x100.jpg">
                            <strong> Продам опт сигареты Jin Ling  25 шт </strong>
                        </span>
                    <span class="last-adv-price"> 225 грн. </span>
                </a>
                <a href="https://addnew.biz/ads/pribory-dlya-bolnyh-diabetom-na-lekarstva-mira">
                        <span class="last-adv-title">
                            <img src="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2019/08/21184543/42433-100x100.jpg">
                            <strong> Приборы для больных диабетом на Lekarstva-mira </strong>
                        </span>
                    <span class="last-adv-price"> 20 евро. </span>
                </a>
                <a href="https://addnew.biz/ads/parfyumeriya-skidki-ot-50-vse-brendy">
                        <span class="last-adv-title">
                            <img src="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2019/08/21170921/36582-100x100.jpg">
                            <strong> Парфюмерия. Скидки от 50%. Все бренды </strong>
                        </span>
                    <span class="last-adv-price"> 35 дол. </span>
                </a>
                <a href="https://addnew.biz/ads/avtorskij-joga-foto-tur-glubina-antaliya-turcziya-12-20-oktyabrya-2019-goda">
                        <span class="last-adv-title">
                            <img src="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2019/08/21155135/791765-100x100.jpeg">
                            <strong> Авторский йога-фото тур «Глубина» Анталия (Турция) 12 – 20 октября 2019 года </strong>
                        </span>
                    <span class="last-adv-price"> 1000 дол. </span>
                </a>
            </div>


            <div class="random-cities">
                <a href="https://addnew.biz/ispaniya/burgos/burgos"> Бургос </a>
                <a href="https://addnew.biz/rossiya/tomskaya-obl/kolpashevo"> Колпашево </a>
                <a href="https://addnew.biz/meksika/michoakan/puruandiro"> Пуруандиро </a>
                <a href="https://addnew.biz/rossiya/tverskaya-obl/vasilevskij-moh"> Васильевский Мох </a>
                <a href="https://addnew.biz/rossiya/ulyanovskaya-obl/izmajlovo"> Измайлово </a>
                <a href="https://addnew.biz/ispaniya/asturiya/gijon"> Гийон </a>
                <a href="https://addnew.biz/rossiya/smolenskaya-obl/monastirshina"> Монастырщина </a>
                <a href="https://addnew.biz/italiya/kampaniya/torre-annuntciata"> Торре-Аннунциата </a>
                <a href="https://addnew.biz/kanada/ontario/brantford"> Брантфорд </a>
                <a href="https://addnew.biz/rossiya/buryatiya/ilka"> Илька </a>
                <a href="https://addnew.biz/yuzhnaya-koreya/chollanam/chechon"> Чечон </a>
            </div>

        </div>

    </main>
@endsection