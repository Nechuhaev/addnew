@extends('front.layout')

@section('meta_title', $category_object->meta_title ?? 'Блог');
@section('meta_description', $category_object->meta_description ?? 'Описание блога');


@section('content')
    <main class="blog-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Блог</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>

                    @if($articles)
                        @foreach($articles as $article)
                            <div class="blog-item">
                                <h3><a href="{{ $article->href }}">{{ $article->name }}</a></h3>
                                <div class="blog-meta">
                                    <span><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/editor-ul.svg') }}" />
                                        @foreach($article->categories()->get() as $category)
                                            <a href="{{ $category->href }}" rel="category tag">{{ $category->name }}</a> |
                                        @endforeach
                                    </span>
                                    <span><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/clock.svg') }}" /> <span>{{ $article->created_at }}</span></span>
                                </div>
                                <div class="blog-intro">
                                    @if($article->image)
                                    <img width="150" height="75" src="{{ $article->image }}" class="blog-img" alt="{{ $article->name }}">
                                    @endif

                                    <p><span style="font-weight: 400;">{!! $article->excerpt !!}</span></p>
                                </div>

                                <p class="blog-views">Всего просмотров: 27, за сегодня: 2</p>
                            </div>
                        @endforeach
                    @endif


                    <div class="col-6">{{ $articles->links('front.widgets.paginate') }}</div>

                    <div class="banner">
                        @include('front.adsense.bottom-listing')
                    </div>

                    @if($category_object->content ?? null)
                    <div class="category-content">
                        {!! $category_object->content !!}
                    </div>
                    <br>
                    @endif

                </div>
                <aside class="column-right">
                    @widget('front.articleCategory')
                </aside>
            </div>

        </div>

    </main>
@endsection