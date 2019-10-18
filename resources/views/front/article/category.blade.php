@extends('front.layout')

@section('content')
    <main class="blog-page">
        <div class="container">
            <div class="banner">
                <img src="{{ asset('assets/front/img/banners/banner-4.jpg') }}" alt="">
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Блог</span></li>
            </ul>

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <div class="banner">
                        <img src="{{ asset('assets/front/img/banners/banner-5.jpg') }}" alt="">
                    </div>

                    @if($articles)
                        @foreach($articles as $article)
                            <div class="blog-item">
                                <h3><a href="{{ $article['url'] }}">{{ $article['name'] }}</a></h3>
                                <div class="blog-meta">
                                    <span><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/editor-ul.svg') }}" /> <a href="https://addnew.biz/category/nedvizhimost-2/" rel="category tag">Недвижимость</a></span>
                                    <span><img class="img-svg" height="20" width="20" src="{{ asset('assets/front/img/dashicons/clock.svg') }}" /> <span>Июль 23, 2019</span></span>
                                </div>
                                <div class="blog-intro">
                                    @if($article['image'])
                                    <img width="150" height="75" src="{{ $article['image'] }}" class="blog-img" alt="{{ $article['name'] }}">
                                    @endif

                                    <p><span style="font-weight: 400;">{!! $article['excerpt'] !!}</span></p>
                                </div>

                                <p class="blog-views">Всего просмотров: 27, за сегодня: 2</p>
                            </div>
                        @endforeach
                    @endif

                    <div class="pagination">
                        <span class="pagination-total">Страница 1 из 277</span>
                        <span aria-current="page" class="pagination-item current">1</span>
                        <a class="pagination-item cp-fixed-color btn_orange" rel="nofollow" href="https://addnew.biz/biznes-i-uslugi/page/2/">2</a>
                        <a class="pagination-item cp-fixed-color btn_orange" rel="nofollow" href="https://addnew.biz/biznes-i-uslugi/page/3/">3</a>
                        <span class="pagination-item dots">…</span>
                        <a class="pagination-item cp-fixed-color btn_orange" rel="nofollow" href="https://addnew.biz/biznes-i-uslugi/page/277/">277</a>
                        <a class="next pagination-item cp-fixed-color btn_orange" rel="nofollow" href="https://addnew.biz/biznes-i-uslugi/page/2/">››</a>
                    </div>

                    <div class="banner">
                        <img src="{{ asset('assets/front/img/banners/banner-6.jpg') }}" alt="">
                    </div>

                </div>
                <aside class="column-right">
                    @widget('front.articleCategory')
                </aside>
            </div>

        </div>

    </main>
@endsection