@extends('front.layout')

@section('meta_title', $article->meta_title ?? $article->name);
@section('meta_description', $article->meta_description ?? strip_tags($article->excerpt) ?? strip_tags($article->description));

@section('content')
    <main class="post-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>
            {{ Breadcrumbs::render('blog.category.article', $article) }}
            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        @include('front.adsense.top-listing')
                    </div>
                    <div class="blog-item">
                        <h1>{{ $article->name }}</h1>
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
                            <p><span style="font-weight: 400;">{{ $article->excerpt }}</span></p>
                        </div>
                        <div class="post-content">
                            {!! $article->content !!}
                        </div>
                    </div>
                </div>
                <aside class="column-right">
                    @widget('front.articleCategory')
                </aside>
            </div>
        </div>
    </main>
@endsection