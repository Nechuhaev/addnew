@extends('front.layout')

@section('meta_title', $category->meta_title ?? 'Блог | Доска объявлений AddNew.Biz');
@section('meta_description', $category->meta_description ?? '☑️ Блог доски объявлений addnew.biz - новости, статьи, полезные материалы как сделать ваше объявление эффективным.');


@section('content')
    <main class="blog-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            @if(isset($category))
                {{ Breadcrumbs::render('blog.category', $category) }}
            @else
                {{ Breadcrumbs::render('blog') }}
            @endif

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>

                    @if($articles)
                        @foreach($articles as $article)
                            <div class="blog-item">
                                <h3><a href="{{ $article->url }}">{{ $article->name }}</a></h3>
                                <div class="blog-meta">
                                    <span><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/editor-ul.svg') }}" />
                                        @foreach($article->categories()->get() as $category)
                                            <a href="{{ $category->url }}" rel="category tag">{{ $category->name }}</a> |
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

                    @if($category->content ?? null)
                    <div class="category-content">
                        {!! $category->content !!}
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