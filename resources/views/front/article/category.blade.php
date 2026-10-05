@extends('front.layout')
@php
    $blogIsRu = app()->getLocale() === 'ru';
    $blogCat = (isset($category) && $category) ? $category : null;
    if ($blogCat) {
        $blogTitle = !empty($blogCat->meta_title)
            ? $blogCat->meta_title
            : ($blogIsRu ? $blogCat->name . ' — статьи блога | AddNew.biz' : $blogCat->name . ' — статті блогу | AddNew.biz');
        $blogDesc = !empty($blogCat->meta_description)
            ? $blogCat->meta_description
            : ($blogIsRu
                ? 'Статьи рубрики «' . $blogCat->name . '» в блоге доски объявлений AddNew.biz: советы, обзоры и полезные материалы.'
                : 'Статті рубрики «' . $blogCat->name . '» у блозі дошки оголошень AddNew.biz: поради, огляди та корисні матеріали.');
    } else {
        $blogTitle = $blogIsRu
            ? 'Блог доски объявлений AddNew.biz: статьи и советы'
            : 'Блог дошки оголошень AddNew.biz: статті та поради';
        $blogDesc = $blogIsRu
            ? 'Блог доски объявлений AddNew.biz: новости, статьи и полезные материалы о том, как сделать ваше объявление эффективным.'
            : 'Блог дошки оголошень AddNew.biz: новини, статті та корисні матеріали про те, як зробити ваше оголошення ефективним.';
    }
@endphp
@section('meta_title', $blogTitle)
@section('meta_description', $blogDesc)

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
                                <p class="blog-views">
                                    {{ __('blog.views_stats', ['total' => $article->total_views, 'today' => $article->today_views]) }}
                                    &nbsp;•&nbsp;
                                    {{ __('blog.read_time', ['minutes' => $article->read_time_minutes]) }}
                                </p>
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
                    <div class="banner">
                        @include('front.adsense.category-right')
                    </div>
                    @widget('front.articleCategory')
                </aside>
            </div>
        </div>
    </main>
@endsection