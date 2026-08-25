@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $default['name'] ?? 'Добавить статью' }} </h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <form class="form-horizontal form-material" method="POST" action="{{ $action }}">
        @csrf
        @if(isset($page_id))
            <input type="hidden" name="page_id" value="{{ $page_id }}">
        @endif
        <div class="row">
            <div class="col-lg-4 col-xlg-3 col-md-5">
                @include('admin.form-widgets.meta_data', ['default' => $default])

                {{-- UK-версії SEO-полів — окремо тут, а не в спільному
                     партіалі meta_data (він використовується ще й для
                     статей та категорій блогу). --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="color:#2e7d32;">Параметри SEO (UK)</h4>
                        <div class="form-group">
                            <label class="col-md-12">Meta-тег title (UK)</label>
                            <div class="col-md-12">
                                <input type="text"
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

                @include('admin.form-widgets.sort_order', ['default' => $default])
            </div>
            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <div class="card-body">
                    @include('admin.form-widgets.title', ['default' => $default])
                    @include('admin.form-widgets.slug', ['default' => $default])
                    @include('admin.form-widgets.content', ['default' => $default])
                    </div>
                </div>

                {{-- Українська версія сторінки --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title" style="color:#2e7d32;">Українська версія</h4>
                        <div class="form-group">
                            <label class="col-md-12">Назва (UK)</label>
                            <div class="col-md-12">
                                <input type="text"
                                       name="name_uk"
                                       value="{{ $default['name_uk'] ?? '' }}"
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-12">Контент (UK)</label>
                            <div class="col-md-12">
                                <textarea name="content_uk" rows="12"
                                          class="content form-control form-control-line">{{ $default['content_uk'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                @include('admin.form-widgets.save_button', ['delete_action_route' => route('admin.page.delete')])
            </div>
        </div>
    </form>
@endsection