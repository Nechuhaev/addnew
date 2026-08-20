@extends('front.layout')
@section('meta_title', $meta['meta_title'] ?? $entity->name)
@section('meta_description', $meta['meta_description'] ?? $entity->content)
@section('style')
    @if(empty($ads))
        <meta name="robots" content="noindex, follow" />
    @endif
@endsection
@section('content')
    <main class="category-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render($breadcrumbs, $entity, $filter) }}
            @if ($filters)
                <div class="columns filter">
                    @foreach($filters as $_filter)
                    <div class="col">
                        <div class="filter-wrap">
                            <a href="{{ $_filter['url'] }}" class="filter-name">
                                @if($_filter['image'])
                                    <img src="{{ $_filter['image'] }}" alt="{{ $_filter['name'] }}"> <span>{{ $_filter['name'] }} ({{ $_filter['ads_count'] }})</span>
                                @else
                                    <span>{{ $_filter['name'] }} ({{ $_filter['ads_count'] }})</span>
                                @endif
                            </a>
                        </div>
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
                        <span class="show-more__btn btn-show">{{ __('front.show_more') }}</span>
                    </div>
                    @endif
                </div>
                <aside class="column-right">
                    @if($entity->parent_id)
{{--                        @widget('front.adCategories', ['heading' => $entity->name, 'filter' => $filter])--}}
                        @widget('front.adSubCategories', [
                            'parent' => $entity->parent,
                            'filter' => $filter,
                            'active_category' => $entity
                        ])
                    @else
                        @widget('front.adSubCategories', ['parent' => $entity, 'filter' => $filter])
                    @endif
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