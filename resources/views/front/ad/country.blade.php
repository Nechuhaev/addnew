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

        @if(isset($children))
        <div class="children">
            @foreach($children as $child)
                <div class="child">
                    <a href="{{ $child->url }}">{{ $child->name }}</a>
                </div>
            @endforeach
        </div>
        @endif


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
                @widget('front.adCategories', ['heading' => $entity->name])
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