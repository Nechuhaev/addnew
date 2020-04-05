@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? $entity->name)

@section('meta_description', $meta['meta_description'] ?? $entity->content)

@section('content')

    <main class="category-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            {{ Breadcrumbs::render($breadcrumbs, $entity) }}

            <p style="font-size: 20px;font-weight: 900;">Все объявления пользователя {{ $entity->username }}</p>
            <div class="author">
                <div class="author-photo"><img alt="Пользователь {{ $entity->username }}" src="https://secure.gravatar.com/avatar/f17c59914122f91f742418889e41b124?s=250&amp;d=mm&amp;r=g" class="author-avatar" height="250" width="250"></div>
                <div class="author-info"><strong>Дата регистрации:</strong> {{ $entity->created_at }}</div>
                <div class="author-info"><strong>Всего объявлений автора:</strong> {{ $entity->ads()->count() }}</div>
                @if ($entity->info)
                    <div class="author-description">
                        <h3>Описание</h3>
                        <p>{{ $entity->info }}</p>
                    </div>
                @endif
            </div>


            <hr>

            <div class="columns columns-nowrap">
                @if($microdata)
                    <div itemtype="http://schema.org/AggregateOffer" itemscope itemprop="offers">
                        <meta content="{{ $microdata->ads_count }}" itemprop="offerCount">
                        <meta content="{{ $microdata->max }}" itemprop="highPrice">
                        <meta content="{{ $microdata->min }}" itemprop="lowPrice">
                        <meta content="UAH" itemprop="priceCurrency">
                    </div>
                @endif
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>

                    @include('front.loop.ads', ['ads' => $ads])

                    {!!  $links  !!}

                    <div class="banner">
                        @include('front.adsense.bottom-listing')
                    </div>


                    @if($meta['description'])
                        <div class="show-more">
                            <section class="show-more__text">
                                {!! $meta['description']  !!}
                            </section>
                            <div class="show-more__shadow"></div>
                            <span class="show-more__btn btn-show">Показать</span>
                        </div>
                    @endif

                </div>


                <aside class="column-right">
                    @widget('front.adCategories', ['heading' => $entity->name, 'filter' => $entity->slug])
                    <div class="banner">
                        @include('front.adsense.category-right')
                    </div>
                    @if(isset($tags))
                        @widget('front.adTags', ['tags' => $tags])
                    @endif
                </aside>
            </div>

        </div>

    </main>


@endsection