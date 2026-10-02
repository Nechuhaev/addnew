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
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Параметри SEO</h4>
                    <div class="form-group">
                        <label class="col-md-12">Meta-тег title (RU)</label>
                        <div class="col-md-12">
                            <input type="text" name="meta_title" value="{{ $default['meta_title'] }}" class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" style="color:#2e7d32;">Meta-тег title (UK)</label>
                        <div class="col-md-12">
                            <input type="text" name="meta_title_uk" value="{{ $default['meta_title_uk'] }}" class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Meta-тег description (RU)</label>
                        <div class="col-md-12">
                            <textarea rows="5" name="meta_description" class="form-control form-control-line">{{ $default['meta_description'] }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" style="color:#2e7d32;">Meta-тег description (UK)</label>
                        <div class="col-md-12">
                            <textarea rows="5" name="meta_description_uk" class="form-control form-control-line">{{ $default['meta_description_uk'] }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            @include('admin.form-widgets.sort_order', ['default' => $default])

        </div>
        <!-- Column -->
        <!-- Column -->
        <div class="col-lg-8 col-xlg-9 col-md-7">
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <label class="col-md-12">Назва категорії (RU)</label>
                        <div class="col-md-12">
                            <input type="text" name="name" value="{{ $default['name'] ?? null }}" class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" style="color:#2e7d32;">Назва категорії (UK)</label>
                        <div class="col-md-12">
                            <input type="text" name="name_uk" value="{{ $default['name_uk'] ?? null }}" placeholder="Якщо порожньо — покаже RU-версію" class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Slug</label>
                        <div class="col-md-12">
                            <input type="text" name="slug" value="{{ $default['slug'] ?? null }}" class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12">Контент (RU)</label>
                        <div class="col-md-12">
                            <textarea name="content" rows="5" class="content form-control form-control-line">{{ $default['content'] ?? null }}</textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-12" style="color:#2e7d32;">Контент (UK)</label>
                        <div class="col-md-12">
                            <textarea name="content_uk" rows="5" class="content form-control form-control-line">{{ $default['content_uk'] ?? null }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            @include('admin.form-widgets.save_button')
        </div>
        <!-- Column -->
    </div>
    </form>

@endsection
