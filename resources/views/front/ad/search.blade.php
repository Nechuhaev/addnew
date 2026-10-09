@extends('front.layout')

@section('meta_title', $meta['meta_title'] ?? $entity->name)

@section('meta_description', $meta['meta_description'] ?? $entity->content)

@section('style')
    @if(empty($ads) || $listingFilters->isActive())
        <meta name="robots" content="noindex, follow" />
    @endif
@endsection

@section('content')
    <main class="category-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            @php($ru = app()->getLocale() === 'ru')
            <ul class="breadcrumb">
                <li><a href="{{ $ru ? url('/ru') : url('/') }}">{{ $ru ? 'Главная' : 'Головна' }}</a></li>
                <li><span>{{ $ru ? 'Поиск' : 'Пошук' }}@if($term !== '') — «{{ $term }}»@endif</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>
                    <h1 class="search-h1">
                        @if($term !== '')
                            {{ $ru ? 'Результаты поиска' : 'Результати пошуку' }} «{{ $term }}»
                        @else
                            {{ $ru ? 'Все объявления' : 'Усі оголошення' }}
                        @endif
                        <span class="search-h1__count">— {{ number_format($total, 0, '', ' ') }}</span>
                    </h1>

                    @include('front.partials.search-shops', ['shops' => $shops, 'term' => $term])

                    @include('front.widgets.listing-filters')

                    @if($total === 0 && $term !== '')
                        @include('front.partials.search-empty', ['listingFilters' => $listingFilters, 'hasShops' => $shops->isNotEmpty()])
                    @endif
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
                            <span class="show-more__btn btn-show">{{ app()->getLocale() === 'ru' ? 'Показать' : 'Показати' }}</span>
                        </div>
                    @endif

                </div>
                <aside class="column-right">
                    @widget('front.adCategories')
                    <div class="banner">
                        @include('front.adsense.category-right')
                    </div>
                    @widget('front.adTags', ['tags' => $tags])
                </aside>
            </div>

        </div>

    </main>
@endsection