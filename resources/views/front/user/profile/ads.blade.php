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

                    @if(session()->has('success'))
                        <div class="alert success"> <!-- success -->
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="alert"> <!-- success -->
                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/warning.svg') }}" />
                            {{ session()->get('error') }}
                        </div>
                    @endif

                    <p>Ниже указан список всех объявлений, размещённых вами. Для совершения определённой задачи, нажмите на одну из опций. Если у вас всё ещё остались вопросы, свяжитесь с администрацией сайта.</p>

                    @if($ads)


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
                        @foreach($ads as $ad)

                        <tr>
                            <td class="td-number"><span class="btn-table-toggle"><i class="icon icon-plus"></i></span>{{ $loop->iteration }}.</td>

                            <td class="td-adv">
                                <h3><a href="{{ $ad->url }}">{{ $ad->name }}</a></h3>

                                <p class="td-meta">
                                    <span class="meta-tag"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/editor-ul.svg') }}" />&nbsp;<a href="{{ $ad->category->url }}" rel="tag" class="">{{ $ad->category->path }}</a></span>
                                    <span class="meta-date"><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/clock.svg') }}" />&nbsp;<span>{{ $ad->date_start }}</span></span>
                                </p>
                                <div class="td-actions">
                                    <ul>
                                        <li><strong>Просмотры:</strong> {{ $ad->total_views }}</li>
                                        <li><strong>Статус:</strong> Выключено</li>
                                        <li><strong>Опции:</strong>
                                            <a title="Редактировать объявление" href="https://addnew.biz/edit-listing/?listing_edit=202151" class="edit">
                                                <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/edit.svg') }}" />
                                            </a>
                                            <a title="Удалить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=delete" onclick="return confirmBeforeDeleteAd();" class="delete">
                                                <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/trash.svg') }}" />
                                            </a>
                                            <a title="Возобновить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=restart" class="restart">
                                                <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/update.svg') }}" />
                                            </a>
                                        </li>
                                        <li><a title="Отметить как устаревшее" href="https://addnew.biz/dashboard/?aid=202151&amp;action=setSold">Отметить как устаревшее</a></li>
                                    </ul>
                                </div>
                            </td>
                            <td class="hidden-xs">{{ $ad->total_views }}</td>

                            <td class="hidden-xs">
                                <span class="status">{{ __('user/ads.status_' . $ad->status) }}</span>
                            </td>

                            <td class="hidden-xs">
                                <ul class="td-actions">
                                    <li>
                                        <a title="Редактировать объявление" href="https://addnew.biz/edit-listing/?listing_edit=202151" class="edit">
                                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/edit.svg') }}" />
                                        </a>
                                        <a title="Удалить объявление" href="{{ route('ad.delete', ['id' => $ad->id]) }}" onclick="return confirm('Вы дейсвительно хотите удалить объявление? Отменить это действие будет невозможно.');" class="delete">
                                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/trash.svg') }}" />
                                        </a>
                                        <a title="Возобновить объявление" href="https://addnew.biz/dashboard/?aid=202151&amp;action=restart" class="restart">
                                            <img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/controls-pause.svg') }}" />
                                        </a>
                                    </li>
                                    <li><a title="Отметить как устаревшее" href="https://addnew.biz/dashboard/?aid=202151&amp;action=setSold">Отметить как устаревшее</a></li>
                                </ul>
                            </td>
                        </tr>
                        @endforeach

                        </tbody>
                    </table>
                        {{ $ads->links('front.widgets.paginate') }}
                    @endif

                </div>
                @include('front.sidebars.user')
            </div>
        </div>
    </main>
@endsection