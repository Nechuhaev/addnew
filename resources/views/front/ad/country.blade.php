@extends('front.layout')

@section('content')

<main class="category-page">
    <div class="container">
        <div class="banner">
            <img src="{{ asset('assets/front/img/banners/banner-4.jpg') }}" alt="">
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
            <div class="column-content">
                <div class="banner">
                    <img src="{{ asset('assets/front/img/banners/banner-5.jpg') }}" alt="">
                </div>

                @include('front.loop.ads', ['ads' => $ads])

                {!!  $links  !!}

                <div class="banner">
                    <img src="{{ asset('assets/front/img/banners/banner-6.jpg') }}" alt="">
                </div>


                <div class="show-more hidden">
                    <section class="show-more__text">
                        {!! $entity->content !!}
                    </section>
                    <div class="show-more__shadow"></div>
                    <span class="show-more__btn btn-show">Показать</span>
                </div>

            </div>


            <aside class="column-right">
                @widget('front.adCategories', ['heading' => $entity->name])
                <div class="banner">
                    <img src="{{ asset('assets/front/img/banners/banner-9.jpg') }}" alt="">
                </div>
                @if(isset($tags))
                    @widget('front.adTags', ['tags' => $tags])
                @endif
            </aside>
        </div>

    </div>

</main>


@endsection