@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Промпт: {{ $prompt->label }}</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('admin.prompts') }}" class="btn btn-sm btn-secondary mb-3">&larr; До списку промптів</a>

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

                    <p><strong>Команда:</strong> <code>{{ $prompt->command_class }}</code></p>
                    @if($prompt->description)
                        <p class="text-muted">{{ $prompt->description }}</p>
                    @endif

                    <form action="{{ route('admin.prompts.update', $prompt->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="template">Текст промпту</label>
                            <textarea name="template" id="template" class="form-control" rows="20" style="font-family: monospace;">{{ old('template', $prompt->template) }}</textarea>
                            <small class="form-text text-muted">
                                Плейсхолдери у форматі <code>{{ '{{назва}}' }}</code> підставляються кодом
                                автоматично при кожному запуску — не видаляйте й не перейменовуйте їх,
                                інакше відповідні дані просто не потраплять у запит до моделі.
                            </small>
                        </div>
                        <button type="submit" class="btn btn-success">Зберегти зміни</button>
                    </form>

                    <form action="{{ route('admin.prompts.reset', $prompt->id) }}" method="POST" style="display:inline-block; margin-top: 12px;"
                          onsubmit="return confirm('Скинути промпт до початкового тексту з коду? Поточні зміни буде втрачено.');">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Скинути до замовчування</button>
                    </form>

                    <hr>
                    <h5>Оригінальний текст (для довідки, тільки читання)</h5>
                    <textarea class="form-control" rows="12" style="font-family: monospace; background:#f8f9fa;" readonly>{{ $prompt->default_template }}</textarea>
                </div>
            </div>
        </div>
    </div>
@endsection
