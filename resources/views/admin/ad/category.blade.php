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
                        @if($category)
                            <input type="hidden" name="category_id" value="{{ $category->id }}">
                        @endif

                        <div class="form-group">
                            <label for="parent_id">Родительская категория</label>
                            <div>
                                <select name="parent_id" class="form-control" id="parent_id">
                                    <option value="0">У этой категории нет родителей :(</option>
                                    @if($parent_list)
                                        @foreach($parent_list as $value)
                                            @if (old('parent_id'))
                                                <option {{ (old('parent_id') == $value['id']) ? 'selected' : '' }} value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                            @elseif (isset($category['parent_id']))
                                                <option {{ ($category['parent_id'] == $value['id']) ? 'selected' : '' }} value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                            @else
                                                <option value="{{ $value['id'] }}">{{ $value['name'] }}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="name">Название категории</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? $category->name ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="slug">Slug</label>
                            <div>
                                <input type="text"
                                       name="slug"
                                       id="slug"
                                       value="{{ old('slug') ?? $category->slug ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="content">Описание категории</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? $category->content ?? '' }}</textarea>

                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_title">Meta-тег title</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? $category->meta_title ?? '' }}"
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
                                          class="form-control form-control-line">{{ old('meta_description') ?? $category->meta_description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="sort_order">Порядок сортировки</label>
                            <div>
                                <input type="text"
                                       name="sort_order"
                                       id="sort_order"
                                       value="{{ old('sort_order') ?? $category->sort_order ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Изображение</label>
                            <div>
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary select-image">
                                        <i class="fa fa-picture-o"></i> Выбрать
                                        </a>
                                    </span>
                                    <input id="thumbnail" value="{{ old('image') ?? $category->image ?? '' }}" class="form-control" type="text" name="image">
                                </div>
                                <img id="holder" class="img-fluid" style="margin-top: 20px" src="{{ old('image') ?? $category->image ?? 'http://placehold.it/400x250' }}">
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
                    @if($tree)
                        <table class="table table-bordered table-hover table-middle-cell">
                        @foreach($tree as $item)
                                <tr>
                                    <th>{{ $item['name'] }}</th>
                                    <th></th>
                                    <th class="text-center cell-actions">
                                        <a href="{{ route('admin.adCategories.edit', ['id' => $item['id']]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="#" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </th>
                                </tr>
                                @if($item['children'])
                                    @foreach($item['children'] as $child)
                                        <tr>
                                            <td></td>
                                            <td>{{ $child['name'] }}</td>
                                            <td class="text-center cell-actions">
                                                <a href="{{ route('admin.adCategories.edit', ['id' => $child['id']]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                                <a href="{{ route('admin.adCategories.delete', ['id' => $child['id']]) }}" onclick="return confirm('Вы пытаетесь удалить категорию {{ $child['name'] }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                        @endforeach
                        </table>
                    @else
                        <p>Категории еще не добавлялись</p>
                    @endif

                </div>
            </div>
        </div>

    </div>
@endsection