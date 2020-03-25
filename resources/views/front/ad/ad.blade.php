@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? $ad->name)

@section('meta_description', $meta['meta_description'] ?? $ad->content)

@section('load-scripts')
    <script type="text/javascript" charset="UTF-8" src="https://maps.googleapis.com/maps/api/js?language=ru&region=RU&key=AIzaSyDfdB0wmym_DAbmbgubW-Tm3ivVN_ZCJMw&ver=3.0"></script>
@endsection

@section('content')
    <main class="adv-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render('ad.page', $ad) }}

            @if($ad->status == 'suspend')
            <div class="alert alert-success">
                Обратите внимание, объявление приостановлено автором!
            </div>
            @endif

            <div class="columns columns-nowrap" itemtype="http://schema.org/Product">
                <meta itemprop="name" content="{{ $ad->name }}" />
                <link itemprop="image" href="{{ $ad->image }}" />
                @if($ad->images && !empty($ad->images[0]))
                    @foreach($ad->images as $image)
                        <link itemprop="image" href="{{ $image }}" />
                    @endforeach
                @endif
                <meta itemprop="description" content="{{ $ad->content }}" />
                <div itemprop="offers" itemtype="http://schema.org/Offer" itemscope>
                    <link itemprop="url" href="{{ $ad->url }}" />
                    <meta itemprop="availability" content="https://schema.org/InStock" />
                    @foreach($prices as $price)
                        @if($price['selected'])
                            <meta itemprop="priceCurrency" content="{{ $price['currency'] }}" />
                            <meta itemprop="price" content="{{ $price['value'] }}" />
                        @endif
                    @endforeach
                    <div itemprop="seller" itemtype="http://schema.org/Organization" itemscope>
                        <meta itemprop="name" content="{{ $ad->user->username }}" />
                    </div>
                </div>

                <aside class="column-left hidden-xs">
                    <div class="adv-img">
                        <a href="{{ $ad->image }}" data-rel="colorbox" class="colorbox group1" title="{{ $ad->name }}">
                            <img class="img-responsive" src="{{ $ad->image }}" title="{{ $ad->name }}" alt="{{ $ad->name }}" style="opacity: 1;">
                        </a>

                        @if($ad->images && !empty($ad->images[0]))
                            <div class="adv-imgs">
                            @foreach($ad->images as $image)
                                    <a href="{{ $image }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $ad->name }} - Изображение {{ $loop->iteration }}">
                                        <img src="{{ $image }}" alt="{{ $ad->name . $loop->iteration }}" title="{{ $ad->name . $loop->iteration }}" width="50" height="50" style="opacity: 1;">
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
                            <li><a href="https://twitter.com/intent/tweet?url={{ $ad->full_url }}&text={{ $ad->name }}" class="tw" target="_blank" rel="noreferrer"></a></li>
                            <li><a href="https://www.facebook.com/sharer/sharer.php?u={{ $ad->full_url }}" class="fb" target="_blank" rel="noreferrer" alt="Доска бесплатных объявлений Addnew.biz в социальной сети Facebook"></a></li>
                        </ul>
                    </div>
                    <div class="banner">
                        @include('front.adsense.ad-left')
                    </div>
                    @widget('front.adTags', ['tags' => $ad->tags()->get()])
                </aside>
                <div class="column-content">
                    <div class="adv-title">
                        <h1><span>{{ $ad->name }}</span></h1>
                        {{--<a href="#" class="adv-bookmark" title="Добавить в избранное"></a>--}}

                        <div class="adv-img visible-xs">
                            <a href="{{ $ad->image }}" data-rel="colorbox" class="colorbox group1" title="{{ $ad->name }}">
                                <img class="img-responsive" src="{{ $ad->image }}" title="{{ $ad->name }}" alt="{{ $ad->name }}" style="opacity: 1;">
                            </a>
                            @if($ad->images)
                                <div class="adv-imgs">
                                    @foreach($ad->images as $image)
                                        <a href="{{ $ad->image }}" id="thumb{{ $loop->iteration }}" class="colorbox group1" data-rel="colorbox" title="{{ $ad->name }} - Изображение {{ $loop->iteration }}"><img src="{{ $ad->image }}" alt="{{ $ad->name . $loop->iteration }}" title="{{ $ad->name . $loop->iteration }}" width="50" height="50" style="opacity: 1;"></a>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="adv-price-block">
                            <div class="adv-price-h">
                                <span>Цена:</span>
                                @if ($prices)
                                    <div class="adv-currency">
                                        @foreach($prices as $price)
                                            <span class="{{ ($price['selected']) ? 'active' : '' }}" data-currency="{{ $price['currency'] }}">{{ $price['currency'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="adv-price">
                                @if ($prices)
                                    @foreach($prices as $price)
                                        <span class="{{ $price['currency'] }} {{ ($price['selected']) ? 'active' : '' }}">{{ $price['value'] }}</span>
                                        @if($price['selected'])
                                            <em class="currency-sign">{{ $price['currency'] }}</em>
                                        @endif
                                    @endforeach
                                @else
                                    <span class="active">Бесплатно</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="adv-meta">
                        <ul class="adv-meta-list">
                            <li><span>Страна:</span><a href="{{ $ad->city->region->country->url }}">{{ $ad->city->region->country->name }}</a></li>
                            <li><span>Автор:</span>{{ $ad->user->username }}</li>
                            <li><span>Город:</span><a href="{{ $ad->city->url }}">{{ $ad->city->name }}</a></li>
                            <li><span>Дата создания:</span>{{ $ad->date_created}}</li>
                            <li><span>Район:</span><a href="{{ $ad->city->region->url }}">{{ $ad->city->region->name }}</a></li>
                            @if($ad->status == 'active')
                                <li><span>Актуально до:</span>{{ $ad->date_end }}</li>
                            @endif
                        </ul>
                        @if($ad->status == 'active')
                            <div class="adv-contacts">
                                <div class="adv-contacts-inner">
                                    <ul class="adv-contacts-list">
                                        <li><span>Телефон:</span> <a href="tel:{{ $ad->telephone }}">{{ $ad->telephone }}</a></li>
                                        <li><span>Электронная почта:</span> <a href="mailto:{{ $ad->email }}">{{ $ad->email }}</a></li>
                                    </ul>
                                    <span class="btn-notice">Показать контакты</span>
                                </div>
                            </div>
                        @endif

                    </div>
                    <div class="adv-description">
                        <div class="adv-h">Описание</div>
                        <p>{{ $ad->content }}</p>
                    </div>
                    <div class="banner">
                        @include('front.adsense.ad-middle')
                    </div>

                    @if(env('APP_ENV') == 'production')
                    <div class="adv-location">
                        <div class="adv-h">Расположение:</div>

                        <div id="map" style="display: block; position: relative; overflow: hidden; min-height: 300px"></div>
                    </div>
                    @endif

                    @if ($ad->email)
                    <div class="adv-callback">
                        <div class="adv-h">Связь:</div>

                        @if(session()->has('success'))
                            <div class="alert alert-success">
                                {{ session()->get('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul style="padding: 0 0 0 10px;margin: 0;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form class="form-contact columns" action="{{ action('Front\Ad\Ad@message', ['slug' => $ad->slug]) }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-message">
                                <i class="icon icon-mail"></i>
                                <span>Чтобы узнать подробную информацию об этом объявлении, заполните форму ниже и отправьте сообщение автору.</span>
                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control required" aria-required="true">
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <label>Почта</label>
                                    <input type="text" name="email" value="{{ old('email') }}" class="form-control required" aria-required="true">
                                </div>
                            </div>
                            <div class="col-1">
                                <div class="form-group">
                                    <label>Сообщение</label>
                                    <textarea class="form-control required" name="message" aria-required="true">{{ old('message') }}</textarea>
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
                    @endif

                </div> <!-- column-content -->
            </div> <!-- columns -->

            <div class="show-more">
                <section class="show-more__text">
                    {!! $meta['description']  !!}
                </section>
                <div class="show-more__shadow"></div>
                <span class="show-more__btn btn-show">Показать</span>
            </div>

            @if($related)
                <section>
                    <p class="section-heading">Похожие объявления</p>
                    <div class="related-ads">
                        @foreach($related as $related_ad)
                        <div class="related-ad">
                            <div class="image">
                                <a href="{{ $related_ad->url }}" title="{{ $related_ad->name }}">
                                    <img src="{{ $related_ad->image }}" alt="{{ $related_ad->name }}" class="img-responsive">
                                </a>
                            </div>
                            <div class="price">100 грн <span class="city">Днепропетровск</span></div>
                            <a href="{{ $related_ad->url }}" title="{{ $related_ad->name }}" class="ad-heading">{{ $related_ad->name }}</a>
                        </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div> <!-- container -->
    </main>
@endsection

@section('script')
    @if (env('APP_ENV') == 'production')
    <script>
        init_google_map('{{ $ad->city->region->country->name }}, {{ $ad->city->name }}', '{{ $ad->name }}');
    </script>
    @endif
@endsection