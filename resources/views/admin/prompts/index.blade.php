@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Промпти</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        Тут зібрані всі промпти, які AI-команди сайту надсилають у Claude/OpenRouter/Groq/
                        Cloudflare/Gemini. Список наповнюється автоматично — щойно команда запускається
                        вперше, її промпт з'являється тут для редагування. Плейсхолдери у фігурних дужках
                        (наприклад <code>@{{site_topic}}</code>) підставляються кодом автоматично —
                        не видаляйте їх, якщо не впевнені, що саме вони роблять (опис під кожним промптом
                        це пояснює).
                    </p>

                    @if($grouped->isEmpty())
                        <p>Поки що жодна AI-команда не запускалась — список порожній. Запустіть будь-яку
                            з команд (наприклад <code>content:publish</code>) хоча б раз, і її промпти
                            з'являться тут.</p>
                    @else
                        @foreach($grouped as $commandClass => $prompts)
                            <h3 style="margin-top: 24px;">
                                {{ class_basename($commandClass) }}
                                <small class="text-muted">({{ $commandClass }})</small>
                            </h3>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Назва</th>
                                            <th>Опис</th>
                                            <th>Оновлено</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($prompts as $prompt)
                                            <tr>
                                                <td>{{ $prompt->label }}</td>
                                                <td class="text-muted">{{ $prompt->description }}</td>
                                                <td>{{ $prompt->updated_at->format('d.m.Y H:i') }}</td>
                                                <td>
                                                    <a href="{{ route('admin.prompts.edit', $prompt->id) }}" class="btn btn-sm btn-primary">
                                                        Редагувати
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
