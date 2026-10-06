@extends('front.layout')
@section('meta_title', $article->meta_title ?? $article->name);
@section('meta_description', $article->meta_description ?? strip_tags($article->excerpt) ?? strip_tags($article->description));
@php
    $ogPub = $article->getOriginal('created_at');
    $ogMod = $article->getOriginal('updated_at');
    $ogTags = $article->tags;
    if (is_string($ogTags)) { $ogTags = json_decode($ogTags, true); }
    $ogTags = is_array($ogTags) ? array_slice(array_filter($ogTags, 'is_scalar'), 0, 8) : [];
@endphp
@section('og_type', 'article')
@section('og_image', (string) ($article->image ?? ''))
@push('og_extra')
@if($ogPub)
<meta property="article:published_time" content="{{ \Carbon\Carbon::parse($ogPub)->toAtomString() }}">
@endif
@if($ogMod)
<meta property="article:modified_time" content="{{ \Carbon\Carbon::parse($ogMod)->toAtomString() }}">
@endif
@foreach($article->categories as $ogCat)
<meta property="article:section" content="{{ $ogCat->name }}">
@endforeach
@foreach($ogTags as $ogTag)
<meta property="article:tag" content="{{ $ogTag }}">
@endforeach
@php
    $ldImg = null;
    if (!empty($article->image)) {
        $ldImg = preg_match('~^https?://~i', $article->image) ? $article->image : url($article->image);
        $ldImg = preg_replace_callback('/[^\x21-\x7E]/', function ($m) {
            return rawurlencode($m[0]);
        }, $ldImg);
    }
    $ldDesc = trim(preg_replace('/\s+/u', ' ', strip_tags(html_entity_decode((string) ($article->meta_description ?: $article->excerpt), ENT_QUOTES, 'UTF-8'))));
    $ldCat = $article->categories->first();
    $ldData = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url()->current()],
        'headline' => \Illuminate\Support\Str::limit(trim(strip_tags((string) $article->name)), 110, ''),
        'description' => \Illuminate\Support\Str::limit($ldDesc, 200, '…'),
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru' : 'uk',
        'author' => ['@type' => 'Organization', 'name' => 'Addnew.biz', 'url' => url('/')],
        'publisher' => ['@type' => 'Organization', 'name' => 'Addnew.biz', 'logo' => ['@type' => 'ImageObject', 'url' => asset('assets/front/img/logo.png')]],
    ];
    if ($ldImg) {
        $ldData['image'] = [$ldImg];
    }
    if ($ogPub) {
        $ldData['datePublished'] = \Carbon\Carbon::parse($ogPub)->toAtomString();
    }
    if ($ogMod || $ogPub) {
        $ldData['dateModified'] = \Carbon\Carbon::parse($ogMod ?: $ogPub)->toAtomString();
    }
    if ($ldCat) {
        $ldData['articleSection'] = $ldCat->name;
    }
    if (!empty($ogTags)) {
        $ldData['keywords'] = implode(', ', $ogTags);
    }
@endphp
<script type="application/ld+json">{!! json_encode($ldData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

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
                            @if($ogMod && $ogPub && \Carbon\Carbon::parse($ogMod)->toDateString() !== \Carbon\Carbon::parse($ogPub)->toDateString())
                                <span>{{ app()->getLocale() === 'ru' ? 'Обновлено' : 'Оновлено' }}: {{ \Carbon\Carbon::parse($ogMod)->format('d.m.Y') }}</span>
                            @endif
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