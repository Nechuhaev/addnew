@extends('front.layout')

@section('meta_title', $page->meta_title ?? $page->name);
@section('meta_description', $page->meta_description ?? strip_tags($page->description));

@section('content')
    <main class="confid-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="/">Главная</a></li>
                <li><span>Confid</span></li>
            </ul>

            <h1>{{ $page->name }}</h1>
            <div class="columns">
                <div class="column-content">

                    <div class="confid-content">
                        {!! $page->content !!}
                    </div>
                </div>
                <aside class="column-right">
                </aside>
            </div>
        </div>

    </main>
@endsection