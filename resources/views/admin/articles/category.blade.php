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
                    <h4 class="card-title">Параметры SEO</h4>
                    <div class="form-group">
                        <label class="col-md-12">Meta-тег title</label>
                        <div class="col-md-12">
                            <input type="text"
                                   placeholder="noobmaster69"
                                   name="meta_title"
                                   value="{{ $default['meta_title'] }}"
                                   class="form-control form-control-line">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-12">Meta-тег description</label>
                        <div class="col-md-12">
                            <textarea rows="5"
                                      name="meta_description"
                                      class="form-control form-control-line">{{ $default['meta_description'] }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Дополнительно</h4>
                    <div class="form-group">
                        <label class="col-md-12">Порядок сортировки</label>
                        <div class="col-md-12">
                            <input type="number"
                                   placeholder=""
                                   value="{{ $default['sort_order'] }}"
                                   name="sort_order"
                                   class="form-control form-control-line">
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <!-- Column -->
        <!-- Column -->
        <div class="col-lg-8 col-xlg-9 col-md-7">
            <div class="card">
                <div class="card-body">

                        <div class="form-group">
                            <label class="col-md-12">Название статьи</label>
                            <div class="col-md-12">
                                <input type="text"
                                       name="name"
                                       value="{{ $default['name'] }}"
                                       placeholder="noobmaster69"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-12">Slug</label>
                            <div class="col-md-12">
                                <input type="text"
                                       name="slug"
                                       value="{{ $default['slug'] }}"
                                       placeholder="noobmaster69"
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-12">Контент</label>
                            <div class="col-md-12">
                                <textarea name="content" rows="5"
                                          class="content form-control form-control-line">{{ $default['content'] }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <button class="btn btn-success">Сохранить статью</button>
                            </div>
                        </div>

                </div>
            </div>
        </div>
        <!-- Column -->
    </div>


    </form>

    <script src="https://cdn.tiny.cloud/1/acl3zjjcwn2wu5y9ad8741ibtyz1fcoi1iwhsdhpqblv1q2y/tinymce/5/tinymce.min.js"></script>

    <script>
        tinymce.init({
            selector:'textarea.content',
            height: 700,
            plugins: "image",
            images_upload_url: 'postAcceptor.php',
            automatic_uploads: false
        });</script>
@endsection