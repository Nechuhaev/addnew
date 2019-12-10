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

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>

                    @include('front.loop.ads', ['ads' => $ads])

                    {!!  $links  !!}

                    <div class="banner">
                        @include('front.adsense.bottom-listing')
                    </div>


                    <div class="show-more">
                        <section class="show-more__text">
                            {!! $meta['description']  !!}
                        </section>
                        <div class="show-more__shadow"></div>
                        <span class="show-more__btn btn-show">Показать</span>
                    </div>

                </div>
                <aside class="column-right">
                    @if($entity->parent_id)
                        @widget('front.adCategories', ['heading' => $entity->name])
                    @else
                        @widget('front.adSubCategories', ['parent' => $entity])
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