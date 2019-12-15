@extends('front.layout')

@section('meta_title', "Мои объявления | Доска объявлений addnew.biz")
@section('meta_description', "Мои объявления | Доска объявлений addnew.biz")

@section('content')
    <main class="account-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('profile.ads') }}

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <h1>Мои объявления</h1>

                    <div class="alert success"> <!-- success -->
                        <img class="img-svg" height="20" width="20" src="img/dashicons/warning.svg" />
                        <span>Объявление было приостановлено</span>
                    </div>
                    <p>Ниже указан список всех объявлений, размещённых вами. Для совершения определённой задачи, нажмите на одну из опций. Если у вас всё ещё остались вопросы, свяжитесь с администрацией сайта.</p>

                    <table class="table-account">
                        <thead>
                        <tr>
                            <th class="" data-class="">&nbsp;</th>
                            <th class="th-adv">Название</th>
                            <th class="hidden-xs" data-hide="phone">Просмотры</th>
                            <th class="hidden-xs" data-hide="phone">Статус</th>
                            <th class="hidden-xs" data-hide="phone">Опции</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="td-number"><span class="btn-table-toggle"><i class="icon icon-plus"></i></span>1.</td>

                            <td class="td-adv">
                                <h3><a href="https://addnew.biz/?post_type=ad_listing&amp;p=202151">Пластиковые цветочные горшки в ассортименте</a></h3>

                                <p class="td-meta">
                                    <span class="meta-tag"><img class="img-svg" height="20" width="20" src="img/dashicons/editor-ul.svg" />&nbsp;<a href="https://addnew.biz/biznes-i-uslugi/prochie-uslugi/" rel="tag" class="">Хозяйственный инвентарь / бытовая химия</a></span>
                                    <span class="meta-date"><img class="img-svg" height="20" width="20" src="img/dashicons/clock.svg" />&nbsp;<span>Сентябрь 3, 2019</span></span>
                                </p>
                                <div class="td-actions">
                                    <ul>
                                        <li><strong>Просмотры:</strong> 0</li>
                                        <li><strong>Статус:</strong> Выключено</li>
                                        <li><strong>Опции:</strong>
                                            <a title="Редактировать объявление" href="https://addnew.biz/edit-listing/?listing_edit=202151" class="edit">
                                                <img class="img-svg" height="20" width="20" src="img/dashicons/edit.svg" />
                                            </a>
                                            <a title="Удалить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=delete" onclick="return confirmBeforeDeleteAd();" class="delete">
                                                <img class="img-svg" height="20" width="20" src="img/dashicons/trash.svg" />
                                            </a>
                                            <a title="Возобновить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=restart" class="restart">
                                                <img class="img-svg" height="20" width="20" src="img/dashicons/update.svg" />
                                            </a>
                                        </li>
                                        <li><a title="Отметить как устаревшее" href="https://addnew.biz/dashboard/?aid=202151&amp;action=setSold">Отметить как устаревшее</a></li>
                                    </ul>
                                </div>
                            </td>

                            <td class="hidden-xs">0</td>

                            <td class="hidden-xs">
                                <span class="status">Выключено</span>
                            </td>

                            <td class="hidden-xs">
                                <ul class="td-actions">
                                    <li>
                                        <a title="Редактировать объявление" href="https://addnew.biz/edit-listing/?listing_edit=202151" class="edit">
                                            <img class="img-svg" height="20" width="20" src="img/dashicons/edit.svg" />
                                        </a>
                                        <a title="Удалить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=delete" onclick="return confirmBeforeDeleteAd();" class="delete">
                                            <img class="img-svg" height="20" width="20" src="img/dashicons/trash.svg" />
                                        </a>
                                        <a title="Возобновить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=restart" class="restart">
                                            <img class="img-svg" height="20" width="20" src="img/dashicons/controls-pause.svg" />
                                        </a>
                                    </li>
                                    <li><a title="Отметить как устаревшее" href="https://addnew.biz/dashboard/?aid=202151&amp;action=setSold">Отметить как устаревшее</a></li>
                                </ul>
                            </td>
                        </tr>

                        <tr>
                            <td class="td-number"><span class="btn-table-toggle"><i class="icon icon-plus"></i></span>2.</td>

                            <td class="td-adv">
                                <h3><a href="https://addnew.biz/?post_type=ad_listing&amp;p=202151">Тестовая услуга 2</a></h3>

                                <p class="td-meta">
                                    <span class="meta-tag"><img class="img-svg" height="20" width="20" src="img/dashicons/editor-ul.svg" />&nbsp;<a href="https://addnew.biz/biznes-i-uslugi/prochie-uslugi/" rel="tag" class="">Прочие услуги</a></span>
                                    <span class="meta-date"><img class="img-svg" height="20" width="20" src="img/dashicons/clock.svg" />&nbsp;<span>Сентябрь 3, 2019</span></span>
                                </p>
                                <div class="td-actions">
                                    <ul>
                                        <li><strong>Просмотры:</strong> 0</li>
                                        <li><strong>Статус:</strong> Включено</li>
                                        <li><strong>Опции:</strong>
                                            <a title="Редактировать объявление" href="https://addnew.biz/edit-listing/?listing_edit=202151" class="edit">
                                                <img class="img-svg" height="20" width="20" src="img/dashicons/edit.svg" />
                                            </a>
                                            <a title="Удалить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=delete" onclick="return confirmBeforeDeleteAd();" class="delete">
                                                <img class="img-svg" height="20" width="20" src="img/dashicons/trash.svg" />
                                            </a>
                                            <a title="Возобновить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=restart" class="restart">
                                                <img class="img-svg" height="20" width="20" src="img/dashicons/controls-pause.svg" />
                                            </a>
                                        </li>
                                        <li><a title="Отметить как устаревшее" href="https://addnew.biz/dashboard/?aid=202151&amp;action=setSold">Отметить как устаревшее</a></li>
                                    </ul>
                                </div>
                            </td>

                            <td class="hidden-xs">0</td>

                            <td class="hidden-xs">
                                <span class="status">Выключено</span>
                            </td>

                            <td class="hidden-xs">
                                <ul class="td-actions">
                                    <li>
                                        <a title="Редактировать объявление" href="https://addnew.biz/edit-listing/?listing_edit=202151" class="edit">
                                            <img class="img-svg" height="20" width="20" src="img/dashicons/edit.svg" />
                                        </a>
                                        <a title="Удалить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=delete" onclick="return confirmBeforeDeleteAd();" class="delete">
                                            <img class="img-svg" height="20" width="20" src="img/dashicons/trash.svg" />
                                        </a>
                                        <a title="Возобновить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=restart" class="restart">
                                            <img class="img-svg" height="20" width="20" src="img/dashicons/controls-pause.svg" />
                                        </a>
                                    </li>
                                    <li><a title="Отметить как устаревшее" href="https://addnew.biz/dashboard/?aid=202151&amp;action=setSold">Отметить как устаревшее</a></li>
                                </ul>
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection