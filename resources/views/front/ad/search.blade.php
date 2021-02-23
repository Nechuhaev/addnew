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

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Поиск - "{{ request()->get('s') }}"</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>
                    <h1>Результат поиска '{{ request()->get('s') }}' — {{ $total }} шт.</h1>
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