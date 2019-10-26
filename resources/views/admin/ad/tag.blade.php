@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Dashboard</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-4">
            <div class="card">
                <div class="card-body">
                    <form action="{{ $action }}" method="POST" class="category-form">
                        @csrf
                        @if($tag)
                            <input type="hidden" name="tag_id" value="{{ $tag->id }}">
                        @endif

                        <div class="form-group">
                            <label>Метка</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       value="{{ old('name') ?? $tag->name ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Slug</label>
                            <div>
                                <input type="text"
                                       name="slug"
                                       value="{{ old('slug') ?? $tag->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="content">Описание категории</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? $tag->content ?? '' }}</textarea>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_title">Meta-тег title</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? $tag->meta_title ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description">Meta-тег description</label>
                            <div>
                                <textarea name="meta_description"
                                          rows="5"
                                          id="meta_description"
                                          class="form-control form-control-line">{{ old('meta_description') ?? $tag->meta_description ?? '' }}</textarea>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group text-center">
                            <button class="btn btn-success">Сохранить</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ $action_search }}" method="GET">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text"
                                       name="name"
                                       value="{{ $requested_tag ?? "" }}"
                                       placeholder="Поиск меток"
                                       class="form-control form-control-line">
                            </div>
                            <div class="col-md-4"><button class="btn btn-primary btn-block">Найти метки</button></div>
                        </div>
                    </form>

                    <hr>

                    @if($tags->items())
                        <table class="table table-bordered table-hover table-middle-cell">
                            @foreach($tags as $tag)
                                <tr>
                                    <td>{{ $tag->name }}</td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adTags.edit', ['id' => $tag->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adTags.delete', ['id' => $tag->id]) }}" onclick="return confirm('Вы пытаетесь удалить метку {{ $tag->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        {{ $tags->links() }}
                    @else
                        <p>Меток не найдено</p>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection