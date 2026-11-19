@extends('front.layout')

@section('meta_title', $page->meta_title ?? $page->name)
@section('meta_description', $page->meta_description ?? \Illuminate\Support\Str::limit(trim(strip_tags((string) $page->content)), 160))

@section('content')
    <main class="confid-page">
        <div class="container">
            <div class="banner">
                @include('front.adsense.top')
            </div>

            <ul class="breadcrumb">
                <li><a href="{{ route('index') }}">{{ __('front.home') }}</a></li>
                <li><span>{{ $page->name }}</span></li>
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