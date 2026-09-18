@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $template->name ?? 'Шаблони листів магазинам' }}</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul style="padding: 0 0 0 10px;margin: 0;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <a href="{{ route('admin.shops') }}" class="btn btn-sm btn-secondary mb-3">&larr; До списку магазинів</a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-5">
            <div class="card">
                <div class="card-body">
                    <h5>{{ $template ? 'Редагувати шаблон' : 'Новий шаблон' }}</h5>
                    <form action="{{ $action }}" method="POST">
                        @csrf
                        @if($template)
                            <input type="hidden" name="template_id" value="{{ $template->id }}">
                        @endif
                        <div class="form-group">
                            <label>Назва шаблону (для себе, у списку)</label>
                            <input type="text" name="name" value="{{ old('name') ?? optional($template)->name ?? '' }}" class="form-control" placeholder="Наприклад: Запрошення підключитись">
                        </div>
                        <div class="form-group">
                            <label>Тема листа</label>
                            <input type="text" name="subject" value="{{ old('subject') ?? optional($template)->subject ?? '' }}" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Текст листа</label>
                            <textarea name="body" class="form-control content" rows="12">{{ old('body') ?? optional($template)->body ?? '' }}</textarea>
                            <small class="form-text text-muted">
                                Доступні плейсхолдери (підставляються автоматично при виборі шаблону
                                на сторінці конкретного магазину):<br>
                                <code>{{ '{{shop_name}}' }}</code> — назва магазину<br>
                                <code>{{ '{{shop_id}}' }}</code> — ID магазину<br>
                                <code>{{ '{{shop_url}}' }}</code> — посилання на публічну сторінку магазину<br>
                                <code>{{ '{{password_reset_url}}' }}</code> — посилання на відновлення пароля
                            </small>
                        </div>
                        <button class="btn btn-success">{{ $template ? 'Зберегти зміни' : 'Створити шаблон' }}</button>
                        @if($template)
                            <a href="{{ route('admin.shopMessageTemplates') }}" class="btn btn-link">Скасувати</a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="col-7">
            <div class="card">
                <div class="card-body">
                    <h5>Наявні шаблони</h5>
                    @if($templates->isEmpty())
                        <p>Шаблонів поки немає.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Назва</th>
                                    <th>Тема</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $t)
                                    <tr>
                                        <td>{{ $t->name }}</td>
                                        <td>{{ $t->subject }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.shopMessageTemplates.edit', $t->id) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                            <a href="{{ route('admin.shopMessageTemplates.delete', $t->id) }}"
                                               onclick="return confirm('Видалити шаблон «{{ $t->name }}»?');"
                                               class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
