@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $tag->name ?? 'Метки объявлений' }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.adTags') }}
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
                            <label>Метка (RU)</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       value="{{ old('name') ?? optional($tag)->getOriginal('name') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label style="color:#2e7d32;">Мітка (UK)</label>
                            <div>
                                <input type="text"
                                       name="name_uk"
                                       value="{{ old('name_uk') ?? optional($tag)->getOriginal('name_uk') ?? '' }}"
                                       placeholder="Якщо порожньо — покаже RU-версію"
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
                            <label for="content">Описание категории (RU)</label>
                            <div>
                                <textarea name="content"
                                          id="content"
                                          class="content form-control form-control-line">{{ old('content') ?? optional($tag)->getOriginal('content') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="content_uk" style="color:#2e7d32;">Опис мітки (UK)</label>
                            <div>
                                <textarea name="content_uk"
                                          id="content_uk"
                                          class="content form-control form-control-line">{{ old('content_uk') ?? optional($tag)->getOriginal('content_uk') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_title">Meta-тег title (RU)</label>
                            <div>
                                <input type="text"
                                       name="meta_title"
                                       id="meta_title"
                                       value="{{ old('meta_title') ?? optional($tag)->getOriginal('meta_title') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_title_uk" style="color:#2e7d32;">Meta-тег title (UK)</label>
                            <div>
                                <input type="text"
                                       name="meta_title_uk"
                                       id="meta_title_uk"
                                       value="{{ old('meta_title_uk') ?? optional($tag)->getOriginal('meta_title_uk') ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_description">Meta-тег description (RU)</label>
                            <div>
                                <textarea name="meta_description"
                                          rows="5"
                                          id="meta_description"
                                          class="form-control form-control-line">{{ old('meta_description') ?? optional($tag)->getOriginal('meta_description') ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="meta_description_uk" style="color:#2e7d32;">Meta-тег description (UK)</label>
                            <div>
                                <textarea name="meta_description_uk"
                                          rows="5"
                                          id="meta_description_uk"
                                          class="form-control form-control-line">{{ old('meta_description_uk') ?? optional($tag)->getOriginal('meta_description_uk') ?? '' }}</textarea>
                            </div>
                        </div>
                        @if($tag)
                            <div class="form-group">
                                <label>SEO-статус</label>
                                <div>
                                    @if($tag->seo_optimized)
                                        <span class="badge badge-success">Унікальний SEO згенеровано{{ $tag->seo_optimized_at ? ' (' . $tag->seo_optimized_at->format('d.m.Y H:i') . ')' : '' }}</span>
                                    @else
                                        <span class="badge badge-warning">Ще старий/шаблонний текст — дочекайтесь щоденного пакетного оновлення чи запустіть вручну</span>
                                    @endif
                                </div>
                            </div>
                        @endif
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
                            <div class="col-md-7">
                                <input type="text"
                                       name="name"
                                       value="{{ $requested_tag ?? "" }}"
                                       placeholder="Поиск меток"
                                       class="form-control form-control-line">
                            </div>
                            <div class="col-md-3"><button class="btn btn-primary btn-block">Найти метки</button></div>
                            <div class="col-2">
                                <a id="deleteMany" href="/admin/adTags/deleteMany" class="btn btn-danger btn-block">Удалить</a>
                            </div>
                        </div>
                    </form>
                    <hr>
                    @if($tags->items())
                        <table class="table table-bordered table-hover table-middle-cell">
                            <tr>
                                <th></th>
                                <th class="text-center">Метка</th>
                                <th>
                                    <a style="color: black; width: 100px"
                                       href="{{ route('admin.adTags', array_merge(request()->query(), ['order' => 'ads_count', 'direction' => ($direction == 'asc') ? 'desc' : 'asc'])) }}">Кол-во</a>
                                    {!! ($order == 'ads_count') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-down"></i>' : '<i class="mdi mdi-arrow-up"></i>' : '';   !!}
                                </th>
                                <th class="text-center">SEO</th>
                                <th class="text-center"></th>
                            </tr>
                            @foreach($tags as $tag_row)
                                <tr>
                                    <td style="width: 30px"><input type="checkbox" name="id[]" value="{{ $tag_row->id }}"></td>
                                    <td>{{ $tag_row->name }}</td>
                                    <td style="width: 100px" class="text-center">
                                        <a href="{{ $tag_row->url }}" target="_blank" title="Переглянути оголошення з цією міткою на сайті">{{ $tag_row->ads_count }}</a>
                                    </td>
                                    <td class="text-center" style="width: 40px">
                                        @if($tag_row->seo_optimized)
                                            <i class="mdi mdi-check-circle text-success" title="Унікальний SEO згенеровано"></i>
                                        @else
                                            <i class="mdi mdi-clock-outline text-muted" title="Ще старий/шаблонний текст"></i>
                                        @endif
                                    </td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adTags.edit', ['id' => $tag_row->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adTags.delete', ['id' => $tag_row->id]) }}" onclick="return confirm('Вы пытаетесь удалить метку {{ $tag_row->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                        {{ $tags->appends(request()->query())->links() }}
                    @else
                        <p>Меток не найдено</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
