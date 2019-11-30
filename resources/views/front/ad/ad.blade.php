@extends('front.layout')

@section('load-scripts')
    <script type="text/javascript" charset="UTF-8" src="https://maps.googleapis.com/maps/api/js?language=ru&region=RU&key=AIzaSyDfdB0wmym_DAbmbgubW-Tm3ivVN_ZCJMw&ver=3.0"></script>
@endsection

@section('content')
    <main class="adv-page">
        <div class="container">
            <div class="banner">
                <img src="{{ asset('assets/front/img/banners/banner-7.jpg') }}" alt="">
            </div>

            {{ Breadcrumbs::render('ad.page', $ad) }}

            <div class="columns columns-nowrap">
                <aside class="column-left hidden-xs">
                    <div class="adv-img">
                        <a href="{{ $ad->image }}" data-rel="colorbox" class="colorbox group1" title="{{ $ad->name }}">
                            <img class="img-responsive" src="{{ $ad->image }}" title="velosiped" alt="velosiped" style="opacity: 1;">
                        </a>

                        @if($ad->images)
                            <div class="adv-imgs">
                            @foreach($ad->images as $image)
                                    <a href="{{ $image }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $ad->name }} - Изображение {{ $loop->iteration }}">
                                        <img src="{{ $image }}" alt="velik2" title="velik2" width="50" height="50" style="opacity: 1;">
                                    </a>
                            @endforeach
                            </div>
                        @endif
                    </div>
                    <div class="adv-info">
                        <h3>Объявление <strong>№ {{ $ad->id }}</strong></h3>
                        <div class="adv-view">
                            <div><span>Всего просмотров:</span> <strong>{{ $ad->total_views }}</strong></div>
                            <div><span>За сегодня:</span> <strong>{{ $ad->today_views }}</strong></div>
                        </div>
                    </div>
                    <div class="adv-share">
                        <h3>Share &amp; Like</h3>
                        <ul class="share42init social-link">
                            <li><a href="https://twitter.com/AddnewBiz" class="tw" target="_blank" rel="noreferrer"></a></li>
                            <li><a href="https://addnewbiz.business.site/" class="plus" target="_blank"></a></li>
                            <li><a href="https://www.facebook.com/addnew.biz/" class="fb" target="_blank" rel="noreferrer" alt="Доска бесплатных объявлений Addnew.biz в социальной сети Facebook"></a></li>
                        </ul>
                    </div>
                    <div class="banner">
                        <img src="{{ asset('assets/front/img/banners/banner-9.jpg') }}" alt="">
                    </div>
                    @widget('front.adTags', ['tags' => $ad->tags()->get()])
                </aside>
                <div class="column-content">
                    <div class="adv-title">
                        <h1><span>{{ $ad->name }}</span> <a href="#" class="adv-bookmark" title="Добавить в избранное"></a></h1>

                        <div class="adv-img visible-xs">
                            <a href="{{ $ad->image }}" data-rel="colorbox" class="colorbox group1" title="{{ $ad->name }}">
                                <img class="img-responsive" src="{{ $ad->image }}" title="velosiped" alt="velosiped" style="opacity: 1;">
                            </a>
                            @if($ad->images)
                                <div class="adv-imgs">
                                    @foreach($ad->images as $image)
                                        <a href="{{ $ad->image }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $ad->name }} - Изображение {{ $loop->iteration }}"><img src="{{ $ad->image }}" alt="velik2" title="velik2" width="50" height="50" style="opacity: 1;"></a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="adv-price-block">
                            <div class="adv-price-h">
                                <span>Цена:</span>

                                <div class="adv-currency">
                                    @foreach($ad->currency->getFormattedPrices() as $price)
                                        <span {{ ($price['is_default']) ? 'class="active"' : '' }} data-currency="{{ $price['code'] }}">{{ $price['symbol'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="adv-price">
                                @foreach($ad->currency->getFormattedPrices() as $price)
                                    <span class="{{ $price['code'] }} {{ ($price['is_default']) ? 'active' : '' }}">{{ $price['value'] }}</span>
                                @endforeach
                                <em class="currency-sign">ГРН</em>
                            </div>
                        </div>
                    </div>
                    <div class="adv-meta">
                        <ul class="adv-meta-list">
                            <li><span>Страна:</span><a href="{{ $ad->city->region->country->url }}">{{ $ad->city->region->country->name }}</a></li>
                            <li><span>Автор:</span>{{ $ad->user->username }}</li>
                            <li><span>Город:</span><a href="{{ $ad->city->url }}">{{ $ad->city->name }}</a></li>
                            <li><span>Дата создания:</span>{{ $ad->date_start }}</li>
                            <li><span>Район:</span><a href="{{ $ad->city->region->url }}">{{ $ad->city->region->name }}</a></li>
                            <li><span>Актуально до:</span>{{ $ad->date_end }}</li>
                        </ul>
                        <div class="adv-contacts">
                            <div class="adv-contacts-inner">
                                <ul class="adv-contacts-list">
                                    <li><span>Телефон:</span> <a href="tel:{{ $ad->telephone }}">{{ $ad->telephone }}</a></li>
                                    <li><span>Электронная почта:</span> <a href="mailto:{{ $ad->email }}">{{ $ad->email }}</a></li>
                                </ul>
                                <span class="btn-notice">Показать контакты</span>
                            </div>
                        </div>
                    </div>
                    <div class="adv-description">
                        <div class="adv-h">Описание</div>
                        <p>{{ $ad->content }}</p>
                    </div>
                    <div class="banner">
                        <img src="{{ asset('assets/front/img/banners/banner-8.jpg') }}" alt="">
                    </div>
                    <div class="adv-location">
                        <div class="adv-h">Расположение:</div>


                        <div id="map" style="display: block; position: relative; overflow: hidden; min-height: 300px"></div>


                    </div>
                    <div class="adv-callback">
                        <div class="adv-h">Связь:</div>

                        <form class="form-contact columns" action="#priceblock2" method="post" enctype="multipart/form-data">

                            <div class="form-message">
                                <i class="icon icon-mail"></i>
                                <span>Чтобы узнать подробную информацию об этом объявлении, заполните форму ниже и отправьте сообщение автору.</span>
                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" value="" class="form-control required" aria-required="true">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Почта</label>
                                    <input type="text" value="" class="form-control required" aria-required="true">
                                </div>
                            </div>
                            <div class="col-1">
                                <div class="form-group">
                                    <label>Сообщение</label>
                                    <textarea class="form-control required" aria-required="true"></textarea>
                                </div>
                            </div>
                            <div class="col-1">
                                <div class="form-action">
                                    <button name="submit" type="submit" class="btn-form" value="Отправить запрос">
                                        <i class="icon icon-plane"></i>
                                        <span class="btn-text">Отправить запрос</span>
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>

                </div> <!-- column-content -->
            </div> <!-- columns -->

            <div class="show-more hidden">
                <section class="show-more__text">
                    <h2>Надежное медицинское страхование туристов, Киев, 100 грн.</h2>
                    <p>Это объявление автора starsky живущего в стране Украина из города Киев рассказывает о том, что Надежное медицинское страхование туристов актуально на сегодняшний день по цене 100 грн.</p>
                    <p>Наши посетители могут размещать на сайте самые различные объявления под названием Надежное медицинское страхование туристов. Стоимость своих услуг, товаров, предложений они выставляют самостоятельно, например 100 грн. USD. эта стоимость может быть в гривне, долларах или евро, по коммерческому курсу Национального банка.</p>
                    <p>
                        На нашей доске бесплатных объявлений Addnew.biz - 151 категория в 106 странах мира.</p>
                    <p>
                        При размещении объявления Надежное медицинское страхование туристов пользователь starsky получает возможность разместить свое объявление на карте Google Maps с позиционированием по стране Украина и городу Киев.</p>
                    <p> Также наши посетители получают абсолютно бесплатную возможность размещать неограниченное количество объявлений различной тематики и направлений.</p>
                    <p>
                        Одним из ключевых преимуществ нашей доски объявлений является абсолютное отсутствие каких либо платежей для наших посетителей.</p>
                    <p>
                        Разместив объявление как зарегистрированный пользователь вы имеете возможность управлять объявлениями, изменять, дополнять, удалять и продлевать объявления через личный кабинет. Мы не заставляем своих посетителей регистрироваться, вы можете размещать объявления анонимно без каких либо обязательств.</p>
                    <p>Разместив на нашей доске объявление вы напрямую общаетесь с покупателем ваших услуг или товаров, без посредников и скрытых платежей по всем странам и городам.</p>
                    <p>Объявления наших пользователей размещаются на других досках через наш автоматизированный сервис рассылки.</p>
                    <p>Доска объявлений создана не только для размещения объявлений, но и для поиска. Помимо основного поиска по любым словам, мы предоставляем возможность фильтровать объявления по Меткам, Странам, Городам, Категориям и Подкатегориям.</p>
                    <p>Получив простую возможность размещения объявлений ВЫ останетесь с нами навсегда.</p>
                    <p>Самые разнообразные категории доски объявлений дают вам обширную аудиторию спроса и активных продаж.</p>
                    <p>Итак подытожим: ваше объявление на тему Надежное медицинское страхование туристов было размещено автором starsky  живущим в стране Украина из города Киев по цене 100 грн., абсолютно бесплатно на срок 90 дней. По истечении срока не забудьте его продлить, удалить или изменить.</p>
                    <p>На нашей доске продается все не только без регистрации, но и без платежей и скрытых комиссий!</p>
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">Показать</span>
            </div>
        </div> <!-- container -->
    </main>
@endsection

@section('script')
    <script>
        init_google_map('Украина, Киев', '2');
    </script>
@endsection