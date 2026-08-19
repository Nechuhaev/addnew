@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Работа с стоп-словами</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.stop-word') }}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2>Постійний список стоп-слів</h2>
                    <p>Ці слова блокують публікацію НОВИХ оголошень, що їх містять.</p>
                    <p class="text-danger"><b>Увага:</b> додавання слова одразу сканує й ВИДАЛЯЄ вже існуючі
                        оголошення, що містять це слово (без окремого підтвердження).</p>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('admin.stop-word.store') }}" method="post" class="form-inline mb-3"
                          onsubmit="return confirm('Увага! Це негайно видалить усі існуючі оголошення, що містять ці слова. Продовжити?');">
                        @csrf
                        <input type="text" name="new_words" class="form-control mr-2" style="min-width: 300px;"
                               placeholder="слово1, слово2, слово3" required>
                        <button class="btn btn-success">Додати</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Слово</th>
                                    <th>Додано</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stopWords as $sw)
                                    <tr>
                                        <td>{{ $sw->word }}</td>
                                        <td>{{ $sw->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <form action="{{ route('admin.stop-word.destroy', $sw->id) }}" method="post"
                                                  onsubmit="return confirm('Видалити слово «{{ $sw->word }}» зі списку?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Видалити</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">Список порожній</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2>Масове видалення вже опублікованих оголошень</h2>
                    <p>Окремий інструмент — НЕ пов'язаний зі списком вище. Будуть видалені всі оголошення,
                        які вже містять одне з введених нижче слів.</p>
                    <p>Слова перелічуються через кому, пошук ведеться по частковому співпадінню, мінімальна довжина слова — 3 символи</p>
                    <b>Тут варто ще раз нагадати про важливість бекапів</b>
                    @if(isset($warning))
                    <h2 class="text-danger text-xl-center">{{ $warning }}</h2>
                    @endif
                    <hr>
                    <form action="{{ $action }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="words" class="col-md-12">Список слів для пошуку й видалення</label>
                            <div class="col-md-12">
                            <textarea rows="5"
                                      id="words"
                                      name="words"
                                      class="form-control form-control-line">{{ $words ?? '' }}</textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-12">
                                @if(isset($warning))
                                    <button class="btn btn-warning">Удалить</button>
                                @else
                                    <button class="btn btn-success">Проверить</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection