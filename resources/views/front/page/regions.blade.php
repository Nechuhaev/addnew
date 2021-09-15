@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? $entity->name)

@section('meta_description', $meta['meta_description'] ?? $entity->content)

@section('content')
    <main class="category-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

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
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>

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
                    <div class="banner">
                        @include('front.adsense.category-right')
                    </div>
                </aside>
            </div>
        </div>
    </main>
@endsection