@extends('front.layout')

@section('content')
    <main class="post-page">
        <div class="container">
            <div class="banner">
                <img src="{{ asset('assets/front/img/banners/banner-4.jpg') }}" alt="">
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><a href="/">Недвижимость</a></li>
                <li><span>Как снять квартиру</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        <img src="{{ asset('assets/front/img/banners/banner-5.jpg') }}" alt="">
                    </div>

                    <div class="blog-item">
                        <h1>{{ $article->name }}</h1>
                        <div class="blog-meta">
                            <span><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/editor-ul.svg') }}" /> <a href="https://addnew.biz/category/nedvizhimost-2/" rel="category tag">Недвижимость</a></span>
                            <span><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/clock.svg') }}" /> <span>Июль 23, 2019</span></span>
                        </div>
                        <div class="blog-intro">
                            @if($article['image'])
                                <img width="150" height="75" src="{{ $article['image'] }}" class="blog-img" alt="{{ $article['name'] }}">
                            @endif

                            <p><span style="font-weight: 400;">Надлежащее планирование и глубокие исследования необходимы, чтобы снять квартиру, которая соответствует вашим потребностям и бюджету. </span><span style="font-weight: 400;">Аренда квартир</span><span style="font-weight: 400;"> иногда бывает такой долгой и утомительной, что хочется обратиться за помощью к агенту по недвижимости. Если вы не можете позволить себе такую роскошь, тогда вам нужно сделать все самостоятельно. Однако сделать это нелегко, особенно если вы никогда этим не занимались.</span></p>
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