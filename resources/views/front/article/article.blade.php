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

                    @if($randomProducts->isNotEmpty())
                        <section class="article-promo-block">
                            <p class="section-heading">Товари з магазинів</p>
                            <div class="related-ads">
                                @foreach($randomProducts as $product)
                                    <div class="related-ad">
                                        <div class="image">
                                            <a href="{{ route('ad.page', ['slug' => $product->slug]) }}" title="{{ $product->name }}">
                                                <img src="{{ $product->image }}" alt="{{ $product->name }}" class="img-responsive" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                                            </a>
                                        </div>
                                        <div class="price">{{ $product->price }} {{ optional($product->currency)->code }}</div>
                                        <a href="{{ route('ad.page', ['slug' => $product->slug]) }}" title="{{ $product->name }}" class="ad-heading">{{ $product->name }}</a>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if($randomListings->isNotEmpty())
                        <section class="article-promo-block">
                            <p class="section-heading">Оголошення на дошці</p>
                            <div class="related-ads">
                                @foreach($randomListings as $listing)
                                    <div class="related-ad">
                                        <div class="image">
                                            <a href="{{ route('ad.page', ['slug' => $listing->slug]) }}" title="{{ $listing->name }}">
                                                <img src="{{ $listing->image }}" alt="{{ $listing->name }}" class="img-responsive" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                                            </a>
                                        </div>
                                        <div class="price">
                                            @if($listing->price)
                                                {{ $listing->price }} {{ optional($listing->currency)->code }}
                                            @else
                                                Безкоштовно
                                            @endif
                                        </div>
                                        <a href="{{ route('ad.page', ['slug' => $listing->slug]) }}" title="{{ $listing->name }}" class="ad-heading">{{ $listing->name }}</a>
                                    </div>
                                @endforeach
                            </div>
                        </section>
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