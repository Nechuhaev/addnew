@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $default['name'] ?? 'Добавить статью' }} </h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.article.article', $article ?? null) }}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    @if($categories)
        <form class="form-horizontal form-material" method="POST" action="{{ $action }}">
            @csrf
            @if(isset($article_id))
                <input type="hidden" name="article_id" value="{{ $article_id }}">
            @endif
        <div class="row">
            <div class="col-lg-4 col-xlg-3 col-md-5">
                @include('admin.form-widgets.image_picker', ['default' => $default])
                @include('admin.form-widgets.meta_data', ['default' => $default])

                {{-- UK-версії SEO-полів — окремо тут, а не в спільному
                     партіалі meta_data (він використовується ще й для
                     категорій блогу та сторінок, де UK-колонок немає). --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="color:#2e7d32;">Параметри SEO (UK)</h4>
                        <div class="form-group">
                            <label class="col-md-12">Meta-тег title (UK)</label>
                            <div class="col-md-12">
                                <input type="text"
                                       placeholder=""
                                       name="meta_title_uk"
                                       value="{{ $default['meta_title_uk'] ?? '' }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Meta-тег description (UK)</label>
                            <div class="col-md-12">
                                <textarea rows="5"
                                          name="meta_description_uk"
                                          class="form-control form-control-line">{{ $default['meta_description_uk'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin.form-widgets.categories', [
                    'categories' => $categories,
                    'default' => $default
                ])
                @include('admin.form-widgets.sort_order', ['default' => $default])
            </div>
            <div class="col-lg-8 col-xlg-9 col-md-7">
                @include('admin.form-widgets.title_slug_content', ['default' => $default])

                {{-- UK-версії заголовку/опису/контенту статті --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="color:#2e7d32;">Українська версія</h4>
                        <div class="form-group">
                            <label class="col-md-12">Назва статті (UK)</label>
                            <div class="col-md-12">
                                <input type="text"
                                       name="name_uk"
                                       value="{{ $default['name_uk'] ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Короткий опис (UK)</label>
                            <div class="col-md-12">
                                <textarea name="excerpt_uk" rows="5"
                                          class="form-control form-control-line">{{ $default['excerpt_uk'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Контент (UK)</label>
                            <div class="col-md-12">
                                <textarea name="content_uk" rows="5"
                                          class="content form-control form-control-line">{{ $default['content_uk'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin.form-widgets.save_button', ['delete_action_route' => route('admin.article.delete')])
            </div>
        </div>
        </form>
    @else
        <div class="card">
            <div class="card-body">
                У Вас нет ни одной категории, к которой можно добавить статью. <a class="btn btn-default" href="{{ route('admin.article.category.add') }}">Создать категорию</a>
            </div>
        </div>
    @endif
@endsection