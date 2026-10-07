@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-7 align-self-center">
                <h4 class="page-title">Шаблони листів магазинам</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.shops') }}" class="btn btn-sm btn-secondary mb-3">&larr; До магазинів</a>
                    @if($template)
                        <a href="{{ route('admin.shopMessageTemplates') }}" class="btn btn-sm btn-success mb-3">+ Новий шаблон</a>
                    @endif

                    @if($templates->isEmpty())
                        <p>Шаблонів поки немає — створіть перший у формі праворуч.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Назва</th>
                                        <th>Тема листа</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $t)
                                        <tr @if($template && $template->id === $t->id) class="table-active" @endif>
                                            <td><a href="{{ route('admin.shopMessageTemplates.edit', $t->id) }}">{{ $t->name }}</a></td>
                                            <td class="text-muted" style="font-size:13px;">{{ $t->subject }}</td>
                                            <td class="text-right" style="white-space:nowrap;">
                                                <a href="{{ route('admin.shopMessageTemplates.edit', $t->id) }}" class="btn btn-sm btn-secondary">Редагувати</a>
                                                <a href="{{ route('admin.shopMessageTemplates.delete', $t->id) }}" class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Видалити шаблон «{{ $t->name }}»?');">&times;</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">{{ $template ? 'Редагувати шаблон' : 'Новий шаблон' }}</h4>

                    <form action="{{ $action }}" method="post">
                        @csrf
                        @if($template)
                            <input type="hidden" name="template_id" value="{{ $template->id }}">
                        @endif

                        <div class="form-group">
                            <label for="name">Назва шаблону (бачите тільки ви)</label>
                            <input type="text" name="name" id="name" class="form-control" required
                                   value="{{ old('name', optional($template)->name) }}">
                        </div>

                        <div class="form-group">
                            <label for="subject">Тема листа</label>
                            <input type="text" name="subject" id="subject" class="form-control" required
                                   value="{{ old('subject', optional($template)->subject) }}">
                        </div>

                        <div class="form-group">
                            <label for="body">Текст листа</label>
                            <textarea name="body" id="body" class="form-control content" rows="12">{{ old('body', optional($template)->body) }}</textarea>
                            <small class="form-text text-muted">
                                Підставляються при виборі шаблону на сторінці магазину:
                                <code>@{{shop_name}}</code> — назва магазину,
                                <code>@{{shop_id}}</code> — ID,
                                <code>@{{shop_url}}</code> — сторінка магазину на addnew,
                                <code>@{{password_reset_url}}</code> — відновлення пароля.
                                <br>У листах кандидатам (Магазини → Кандидати): <code>@{{shop_name}}</code>,
                                <code>@{{goods}}</code> — «товари» або «товари в категорії «…»»,
                                <code>@{{site_url}}</code>, <code>@{{register_url}}</code> — реєстрація магазину,
                                <code>@{{sender_name}}</code> — ваше ім'я.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success">{{ $template ? 'Зберегти' : 'Створити' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
