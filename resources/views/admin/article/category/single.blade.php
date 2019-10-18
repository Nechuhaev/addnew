@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $page_title }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.article.category', $category) }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <form class="form-horizontal form-material" method="POST" action="{{ $action }}">
        @csrf
        @if($category)
            <input type="hidden" name="category_id" value="{{ $category->id }}">
        @endif

    <div class="row">
        <!-- Column -->
        <div class="col-lg-4 col-xlg-3 col-md-5">
            @include('admin.form-widgets.meta_data', ['default' => $default])

            @include('admin.form-widgets.sort_order', ['default' => $default])

        </div>
        <!-- Column -->
        <!-- Column -->
        <div class="col-lg-8 col-xlg-9 col-md-7">
            @include('admin.form-widgets.title_slug_content',  ['default' => $default])
            @include('admin.form-widgets.save_button')
        </div>
        <!-- Column -->
    </div>
    </form>

@endsection