@extends('front.layout')

@section('content')
    <main class="steps-page">
        <div class="container">
            <div class="banner">
                <img src="img/banners/banner-7.jpg" alt="">
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Детали</span></li>
            </ul>

            <ul class="steps-row" data-steps="4">
                <li class="steps-done">Категория</li>
                <li class="steps-done">Детали</li>
                <li class="steps-todo">Предпросмотр</li>
                <li class="steps-todo">Спасибо</li>
            </ul>
            <div class="steps-content" id="step-2">
                <h2>Размещение объявления: <span>Детали</span></h2>
                <div class="columns">
                    <div class="col-2">
                        <p class="hidden-xs">Пожалуйста, заполните поля ниже, чтобы разместить объявление на сайте. Обязательные для заполнения поля обозначены звездочкой (&nbsp;*&nbsp;). У вас будет возможность ознакомиться с вашим объявлением перед его размещением.</p>
                        <!--more-->
                        <p class="hidden-xs">Зарегистрируйтесь бесплатно и начните размещать объявления в считанные минуты. Управляйте объявлениями из личного кабинета.</p>

                    </div>
                    <div class="col-2">
                        <div class="notice-wrap">
                            <div class="notice">
                                <i class="icon icon-lock"></i>
                                <div><p><a href="#">Зарегистрируйтесь</a> бесплатно и начните размещать объявления в считанные минуты. Управляйте объявлениями из личного кабинета.</p></div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="category-change">
                    <strong>Категория: <br>Легковые автомобили</strong>
                    <a href="/steps.html">Изменить</a>
                </div>
                <form class="form-step" action="#" method="post" enctype="multipart/form-data" >
                    <div class="columns">
                        <div class="col-2">
                            <div class="form-group">
                                <label>Автор объявления<span class="star">(*)</span></label>
                                <input type="text" class="form-control required">
                                <span class="form-help">Введите имя, от лица которого вы публикуете объявление.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Телефон<span class="star">(*)</span></label>
                                <input type="text" class="form-control required">
                                <span class="form-help">Вы можете ввести несколько номеров, разделив их запятой. Введите
                                    телефонный номер в международной системе нумерации, например: +380501112233.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Страна<span class="star">(*)</span></label>
                                <select class="form-control">
                                    <option value="">-- Выберите --</option>
                                    <option value="4">Австралия</option>
                                    <option value="63">Австрия</option>
                                    <option value="81">Азербайджан</option>
                                    <option value="173">Ангуилья</option>
                                    <option value="177">Аргентина</option>
                                    <option value="245">Армения</option>
                                    <option value="248">Беларусь</option>
                                    <option value="401">Белиз</option>
                                    <option value="404">Бельгия</option>
                                    <option value="425">Бермуды</option>
                                    <option value="428">Болгария</option>
                                    <option value="467">Бразилия</option>
                                    <option value="616">Великобритания</option>
                                    <option value="924">Венгрия</option>
                                    <option value="971">Вьетнам</option>
                                    <option value="994">Гаити</option>
                                    <option value="1007">Гваделупа</option>
                                    <option value="1012">Германия</option>
                                    <option value="1206">Голландия</option>
                                    <option value="1258">Греция</option>
                                    <option value="1280">Грузия</option>
                                    <option value="1366">Дания</option>
                                    <option value="1380">Египет</option>
                                    <option value="1393">Израиль</option>
                                    <option value="1451">Индия</option>
                                    <option value="1663">Иран</option>
                                    <option value="1696">Ирландия</option>
                                    <option value="1707">Испания</option>
                                    <option value="1786">Италия</option>
                                    <option value="1894">Казахстан</option>
                                    <option value="2163">Камерун</option>
                                    <option value="2172">Канада</option>
                                    <option value="2297">Кипр</option>
                                    <option value="2303">Киргызстан</option>
                                    <option value="2374">Китай</option>
                                    <option value="2430">Коста-Рика</option>
                                    <option value="2443">Кувейт</option>
                                    <option value="2448">Латвия</option>
                                    <option value="2509">Ливия</option>
                                    <option value="2514">Литва</option>
                                    <option value="2614">Люксембург</option>
                                    <option value="2617">Мексика</option>
                                    <option value="2788">Молдова</option>
                                    <option value="2833">Монако</option>
                                    <option value="2837">Новая Зеландия</option>
                                    <option value="2880">Норвегия</option>
                                    <option value="2897">Польша</option>
                                    <option value="3141">Португалия</option>
                                    <option value="3156">Реюньон</option>
                                    <option value="3159">Россия</option>
                                    <option value="5647">Сальвадор</option>
                                    <option value="5666">Словакия</option>
                                    <option value="5673">Словения</option>
                                    <option value="5678">Суринам</option>
                                    <option value="5681">США</option>
                                    <option value="9575">Таджикистан</option>
                                    <option value="9638">Туркменистан</option>
                                    <option value="9701">Туркс и Кейкос</option>
                                    <option value="9705">Турция</option>
                                    <option value="9782">Уганда</option>
                                    <option value="9787">Узбекистан</option>
                                    <option value="9908">Украина</option>
                                    <option value="10000">testesteste</option>
                                    <option value="10648">Финляндия</option>
                                    <option value="10668">Франция</option>
                                    <option value="10874">Чехия</option>
                                    <option value="10904">Швейцария</option>
                                    <option value="10933">Швеция</option>
                                    <option value="10968">Эстония</option>
                                    <option value="11002">Югославия</option>
                                    <option value="11014">Южная Корея</option>
                                    <option value="11060">Япония</option>
                                    <option value="277551">Нидерланды</option>
                                    <option value="277553">Хорватия</option>
                                    <option value="277555">Румыния</option>
                                    <option value="277557">Гонконг</option>
                                    <option value="277559">Индонезия</option>
                                    <option value="277561">Иордания</option>
                                    <option value="277563">Малайзия</option>
                                    <option value="277565">Сингапур</option>
                                    <option value="277567">Тайвань</option>
                                    <option value="277569">Туркмения</option>
                                    <option value="582029">Карибы</option>
                                    <option value="582031">Чили</option>
                                    <option value="582040">Корея</option>
                                    <option value="582041">Македония</option>
                                    <option value="582043">Мальта</option>
                                    <option value="582044">Пакистан</option>
                                    <option value="582046">Перу</option>
                                    <option value="582050">Тайланд</option>
                                    <option value="582051">О.А.Э.</option>
                                    <option value="582060">Ливан</option>
                                    <option value="582064">Эквадор</option>
                                    <option value="582065">Морокко</option>
                                    <option value="582067">Сирия</option>
                                    <option value="582077">Куба</option>
                                    <option value="582082">Мозамбик</option>
                                    <option value="582090">Тунис</option>
                                    <option value="582105">Остров Мэн</option>
                                    <option value="582106">Ямайка</option>
                                    <option value="2567393">Гондурас</option>
                                    <option value="2577958">Доминиканская республика</option>
                                    <option value="2687701">Монголия</option>
                                    <option value="3410238">Ирак</option>
                                    <option value="3661568">ЮАР</option>
                                    <option value="7716093">Арулько</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Область<span class="star">(*)</span></label>
                                <select class="form-control">
                                    <option value="">-- Выберите --</option>
                                    <option value="4">Австралия</option>
                                    <option value="63">Австрия</option>
                                    <option value="81">Азербайджан</option>
                                    <option value="173">Ангуилья</option>
                                    <option value="177">Аргентина</option>
                                    <option value="245">Армения</option>
                                    <option value="248">Беларусь</option>
                                    <option value="401">Белиз</option>
                                    <option value="404">Бельгия</option>
                                    <option value="425">Бермуды</option>
                                    <option value="428">Болгария</option>
                                    <option value="467">Бразилия</option>
                                    <option value="616">Великобритания</option>
                                    <option value="924">Венгрия</option>
                                    <option value="971">Вьетнам</option>
                                    <option value="994">Гаити</option>
                                    <option value="1007">Гваделупа</option>
                                    <option value="1012">Германия</option>
                                    <option value="1206">Голландия</option>
                                    <option value="1258">Греция</option>
                                    <option value="1280">Грузия</option>
                                    <option value="1366">Дания</option>
                                    <option value="1380">Египет</option>
                                    <option value="1393">Израиль</option>
                                    <option value="1451">Индия</option>
                                    <option value="1663">Иран</option>
                                    <option value="1696">Ирландия</option>
                                    <option value="1707">Испания</option>
                                    <option value="1786">Италия</option>
                                    <option value="1894">Казахстан</option>
                                    <option value="2163">Камерун</option>
                                    <option value="2172">Канада</option>
                                    <option value="2297">Кипр</option>
                                    <option value="2303">Киргызстан</option>
                                    <option value="2374">Китай</option>
                                    <option value="2430">Коста-Рика</option>
                                    <option value="2443">Кувейт</option>
                                    <option value="2448">Латвия</option>
                                    <option value="2509">Ливия</option>
                                    <option value="2514">Литва</option>
                                    <option value="2614">Люксембург</option>
                                    <option value="2617">Мексика</option>
                                    <option value="2788">Молдова</option>
                                    <option value="2833">Монако</option>
                                    <option value="2837">Новая Зеландия</option>
                                    <option value="2880">Норвегия</option>
                                    <option value="2897">Польша</option>
                                    <option value="3141">Португалия</option>
                                    <option value="3156">Реюньон</option>
                                    <option value="3159">Россия</option>
                                    <option value="5647">Сальвадор</option>
                                    <option value="5666">Словакия</option>
                                    <option value="5673">Словения</option>
                                    <option value="5678">Суринам</option>
                                    <option value="5681">США</option>
                                    <option value="9575">Таджикистан</option>
                                    <option value="9638">Туркменистан</option>
                                    <option value="9701">Туркс и Кейкос</option>
                                    <option value="9705">Турция</option>
                                    <option value="9782">Уганда</option>
                                    <option value="9787">Узбекистан</option>
                                    <option value="9908">Украина</option>
                                    <option value="10000">testesteste</option>
                                    <option value="10648">Финляндия</option>
                                    <option value="10668">Франция</option>
                                    <option value="10874">Чехия</option>
                                    <option value="10904">Швейцария</option>
                                    <option value="10933">Швеция</option>
                                    <option value="10968">Эстония</option>
                                    <option value="11002">Югославия</option>
                                    <option value="11014">Южная Корея</option>
                                    <option value="11060">Япония</option>
                                    <option value="277551">Нидерланды</option>
                                    <option value="277553">Хорватия</option>
                                    <option value="277555">Румыния</option>
                                    <option value="277557">Гонконг</option>
                                    <option value="277559">Индонезия</option>
                                    <option value="277561">Иордания</option>
                                    <option value="277563">Малайзия</option>
                                    <option value="277565">Сингапур</option>
                                    <option value="277567">Тайвань</option>
                                    <option value="277569">Туркмения</option>
                                    <option value="582029">Карибы</option>
                                    <option value="582031">Чили</option>
                                    <option value="582040">Корея</option>
                                    <option value="582041">Македония</option>
                                    <option value="582043">Мальта</option>
                                    <option value="582044">Пакистан</option>
                                    <option value="582046">Перу</option>
                                    <option value="582050">Тайланд</option>
                                    <option value="582051">О.А.Э.</option>
                                    <option value="582060">Ливан</option>
                                    <option value="582064">Эквадор</option>
                                    <option value="582065">Морокко</option>
                                    <option value="582067">Сирия</option>
                                    <option value="582077">Куба</option>
                                    <option value="582082">Мозамбик</option>
                                    <option value="582090">Тунис</option>
                                    <option value="582105">Остров Мэн</option>
                                    <option value="582106">Ямайка</option>
                                    <option value="2567393">Гондурас</option>
                                    <option value="2577958">Доминиканская республика</option>
                                    <option value="2687701">Монголия</option>
                                    <option value="3410238">Ирак</option>
                                    <option value="3661568">ЮАР</option>
                                    <option value="7716093">Арулько</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Город<span class="star">(*)</span></label>
                                <select class="form-control">
                                    <option value="">-- Выберите --</option>
                                    <option value="4">Австралия</option>
                                    <option value="63">Австрия</option>
                                    <option value="81">Азербайджан</option>
                                    <option value="173">Ангуилья</option>
                                    <option value="177">Аргентина</option>
                                    <option value="245">Армения</option>
                                    <option value="248">Беларусь</option>
                                    <option value="401">Белиз</option>
                                    <option value="404">Бельгия</option>
                                    <option value="425">Бермуды</option>
                                    <option value="428">Болгария</option>
                                    <option value="467">Бразилия</option>
                                    <option value="616">Великобритания</option>
                                    <option value="924">Венгрия</option>
                                    <option value="971">Вьетнам</option>
                                    <option value="994">Гаити</option>
                                    <option value="1007">Гваделупа</option>
                                    <option value="1012">Германия</option>
                                    <option value="1206">Голландия</option>
                                    <option value="1258">Греция</option>
                                    <option value="1280">Грузия</option>
                                    <option value="1366">Дания</option>
                                    <option value="1380">Египет</option>
                                    <option value="1393">Израиль</option>
                                    <option value="1451">Индия</option>
                                    <option value="1663">Иран</option>
                                    <option value="1696">Ирландия</option>
                                    <option value="1707">Испания</option>
                                    <option value="1786">Италия</option>
                                    <option value="1894">Казахстан</option>
                                    <option value="2163">Камерун</option>
                                    <option value="2172">Канада</option>
                                    <option value="2297">Кипр</option>
                                    <option value="2303">Киргызстан</option>
                                    <option value="2374">Китай</option>
                                    <option value="2430">Коста-Рика</option>
                                    <option value="2443">Кувейт</option>
                                    <option value="2448">Латвия</option>
                                    <option value="2509">Ливия</option>
                                    <option value="2514">Литва</option>
                                    <option value="2614">Люксембург</option>
                                    <option value="2617">Мексика</option>
                                    <option value="2788">Молдова</option>
                                    <option value="2833">Монако</option>
                                    <option value="2837">Новая Зеландия</option>
                                    <option value="2880">Норвегия</option>
                                    <option value="2897">Польша</option>
                                    <option value="3141">Португалия</option>
                                    <option value="3156">Реюньон</option>
                                    <option value="3159">Россия</option>
                                    <option value="5647">Сальвадор</option>
                                    <option value="5666">Словакия</option>
                                    <option value="5673">Словения</option>
                                    <option value="5678">Суринам</option>
                                    <option value="5681">США</option>
                                    <option value="9575">Таджикистан</option>
                                    <option value="9638">Туркменистан</option>
                                    <option value="9701">Туркс и Кейкос</option>
                                    <option value="9705">Турция</option>
                                    <option value="9782">Уганда</option>
                                    <option value="9787">Узбекистан</option>
                                    <option value="9908">Украина</option>
                                    <option value="10000">testesteste</option>
                                    <option value="10648">Финляндия</option>
                                    <option value="10668">Франция</option>
                                    <option value="10874">Чехия</option>
                                    <option value="10904">Швейцария</option>
                                    <option value="10933">Швеция</option>
                                    <option value="10968">Эстония</option>
                                    <option value="11002">Югославия</option>
                                    <option value="11014">Южная Корея</option>
                                    <option value="11060">Япония</option>
                                    <option value="277551">Нидерланды</option>
                                    <option value="277553">Хорватия</option>
                                    <option value="277555">Румыния</option>
                                    <option value="277557">Гонконг</option>
                                    <option value="277559">Индонезия</option>
                                    <option value="277561">Иордания</option>
                                    <option value="277563">Малайзия</option>
                                    <option value="277565">Сингапур</option>
                                    <option value="277567">Тайвань</option>
                                    <option value="277569">Туркмения</option>
                                    <option value="582029">Карибы</option>
                                    <option value="582031">Чили</option>
                                    <option value="582040">Корея</option>
                                    <option value="582041">Македония</option>
                                    <option value="582043">Мальта</option>
                                    <option value="582044">Пакистан</option>
                                    <option value="582046">Перу</option>
                                    <option value="582050">Тайланд</option>
                                    <option value="582051">О.А.Э.</option>
                                    <option value="582060">Ливан</option>
                                    <option value="582064">Эквадор</option>
                                    <option value="582065">Морокко</option>
                                    <option value="582067">Сирия</option>
                                    <option value="582077">Куба</option>
                                    <option value="582082">Мозамбик</option>
                                    <option value="582090">Тунис</option>
                                    <option value="582105">Остров Мэн</option>
                                    <option value="582106">Ямайка</option>
                                    <option value="2567393">Гондурас</option>
                                    <option value="2577958">Доминиканская республика</option>
                                    <option value="2687701">Монголия</option>
                                    <option value="3410238">Ирак</option>
                                    <option value="3661568">ЮАР</option>
                                    <option value="7716093">Арулько</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Электронная почта<span class="star">(*)</span></label>
                                <input type="email" class="form-control required">
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Заголовок<span class="star">(*)</span></label>
                                <input type="text" class="form-control required">
                                <span class="form-help">Введите наименование товара, объекта или услуги. Чем точнее тематические слова,
                                    тем выше вероятность показа вашего объявления в поисковиках. В заголовке не допускается:
                                    номер телефона, электронный адрес, ссылки. Не допускаются заглавные буквы (кроме аббревиатур).</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Метки<span class="star">(*)</span></label>
                                <input type="text" class="form-control required">
                                <span class="form-help">Метки - это ключевые слова, по которым поисковые системы определяют, что именно
                                    находится на странице. Это поле не обязательно к заполнению, но наличие правильных меток увеличит количество просмотров
                                    вашего объявления. Используйте только те ключевые слова, которые имеют отношение к вашему объявлению.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Описание<span class="star">(*)</span></label>
                                <textarea rows="8" class="form-control required"></textarea>
                            </div>
                        </div>
                        <div class="col-2">

                            <div class="form-group upload-file">
                                <label>Изображение</label><br>
                                <label class="upload-label">
                                    <input name="file" type="file" class="input-file"  multiple="true" />
                                    <div>Загрузить</div>
                                    <input class="input-file-name" type="text" id="input-file-name" value="Файл не выбран." disabled />
                                </label>

                                <span class="form-help">Допустимое количество загружаемых файлов: 5. Максимальный размер файла: 1024 KB.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Цена<span class="star">(*)</span></label>
                                <input type="text" class="form-control required">
                                <span class="form-help">Введите реальную стоимость вашего товара или услуги. Администрация площадки врпаве
                                    удалить объявление за некорректно предоставленную информацию.</span>
                            </div>
                        </div>
                        <div class="col-2">
                            <div class="form-group">
                                <label>Валюта<span class="star">(*)</span></label>
                                <select class="form-control required">
                                    <option value="">-- Выберите --</option>
                                    <option value="грн.">грн.</option>
                                    <option value="дол.">дол.</option>
                                    <option value="евро.">евро.</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="form-action">
                        <input type="submit" name="step1" id="step1" class="btn btn-step" value="Продолжить">
                    </div>

                </form>
            </div>




        </div> <!-- container -->
    </main>
@endsection