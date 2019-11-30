@extends('front.layout')

@section('content')
    <main class="steps-page">
        <div class="container">
            <div class="banner">
                <img src="img/banners/banner-7.jpg" alt="">
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Предпросмотр</span></li>
            </ul>

            <ul class="steps-row" data-steps="4">
                <li class="steps-done">Категория</li>
                <li class="steps-done">Детали</li>
                <li class="steps-done">Предпросмотр</li>
                <li class="steps-todo">Спасибо</li>
            </ul>
            <div class="steps-content" id="step-3">
                <h2>Размещение объявления: <span>Предварительный просмотр</span></h2>
                <div class="steps-preview">
                    <p>Ниже вы видите, как будет ваше обявление выглядеть после публикации на сайте. Внимательно проверьте всю информацию, исправьте ошибки, если таковые имеются и опубликуйте объявление.</p>
                </div>
                <hr>
                <div class="columns">
                    <aside class="column-left hidden-xs">
                        <div class="adv-img">
                            <a href="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2018/10/15102400/367721-500x261.jpg" data-rel="colorbox" title="Надежное медицинское страхование туристов"><img src="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2018/10/15102400/367721-500x261.jpg" title="insurance" alt="insurance" style="opacity: 1;"></a>
                        </div>
                    </aside>
                    <div class="column-content">
                        <div class="adv-title">
                            <h1><span>Надежное медицинское страхование туристов</span> <a href="#" class="adv-bookmark" title="Добавить в избранное"></a></h1>
                            <div class="adv-img visible-xs">
                                <a href="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2018/10/15102400/367721-500x261.jpg" data-rel="colorbox" title="Надежное медицинское страхование туристов"><img src="https://s3.amazonaws.com/addnew-wp/wp-content/uploads/2018/10/15102400/367721-500x261.jpg" title="insurance" alt="insurance"></a>
                            </div>

                            <div class="adv-price-block">
                                <div class="adv-price-h">
                                    <span>Цена:</span>
                                    <div class="adv-currency">
                                        <span class="active" data-currency="uah">ГРН</span>
                                        <span data-currency="usd">USD</span>
                                        <span data-currency="eur">EUR</span>
                                    </div>
                                </div>
                                <div class="adv-price">
                                    <span class="uah active">100</span>
                                    <span class="usd">3.56</span>
                                    <span class="eur">3.05</span> <em class="currency-sign">ГРН</em>
                                </div>
                            </div>
                        </div>
                        <div class="adv-meta">
                            <ul class="adv-meta-list">
                                <li><span>Страна:</span><a href="https://addnew.biz/ukraina/">Украина</a></li>
                                <li><span>Автор:</span>starsky</li>
                                <li><span>Город:</span><a href="https://addnew.biz/ukraina/kievskaya-obl/kiev/">Киев</a></li>
                                <li><span>Дата создания:</span>28.08.2019г.</li>
                                <li><span>Район:</span><a href="https://addnew.biz/ukraina/kievskaya-obl/">Киевская обл.</a></li>
                                <li><span>Актуально до:</span>26.11.2019г.</li>
                            </ul>
                            <div class="adv-contacts">
                                <div class="adv-contacts-inner">
                                    <ul class="adv-contacts-list">
                                        <li><span>Телефон:</span> <a href="tel:380683641424">380683641424</a></li>
                                        <li><span>Электронная почта:</span> <a href="mailto:test@gmail.com">test@gmail.com</a></li>
                                    </ul>
                                    <span class="btn-notice">Показать контакты</span>
                                </div>
                            </div>
                        </div>
                        <div class="adv-description">
                            <div class="adv-h">Описание</div>
                            <p>Уважаемые потенциальные клиенты нашего замечательного агентства путешествий. Хотим предложить вам полисы туристического страхования для визита в любую страну сказочно красивой планеты под названием Земля. Так как  Земля не только прекрасна, но еще может быть и опасна для путешественника, то застраховаться в надежной компании будет совсем не лишним.  И стоить это вам будет почти ничего, если сравнить траты на страховку с возможными тратами, который может понести турист в случае болезни или в каком-нибудь несчастном случае и форс мажоре, не дай Бог. Страховое покрытие, что мы предлагаем, колеблется от 5.000 евро до 50.000 евро. И оно работает в любой стране мира, даже Гондурасе. Кроме этого менеджеры дадут квалифицированную консультацию, как вести себя, чтоб грамотно получить помощь согласно условиям страховой компании.  Будем рады ответить на все имеющиеся у Вас вопросы. Всегда Ваше, Стар Скай Тревел. Цены на полисы здесь http://www.travelling.kiev.ua/touristinsurance.htm</p>
                        </div>
                        <div class="adv-location">
                            <div class="adv-h">Расположение:</div>
                            <div class="map" style="background-image: url('img/map.jpg');"></div>
                        </div>
                        <form action="#" class="form-steps">
                            <div class="form-action">
                                <input type="submit" name="step1" id="step1" class="btn btn-step btn-change" value="Изменить">
                                <input type="submit" name="step1" id="step1" class="btn btn-step btn-publish" value="Опубликовать">
                            </div>
                        </form>
                    </div> <!-- column-content -->
                </div>


            </div>




        </div> <!-- container -->
    </main>

@endsection