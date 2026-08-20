@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Переклади (uk / ru)</h4>
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

                    <form action="{{ route('admin.translations') }}" method="get" class="form-inline mb-3">
                        <select name="group" class="form-control mr-2" onchange="this.form.submit()">
                            <option value="">— Всі групи —</option>
                            @foreach($groups as $g)
                                <option value="{{ $g }}" {{ $currentGroup === $g ? 'selected' : '' }}>{{ $g }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="search" class="form-control mr-2" placeholder="Пошук за ключем" value="{{ $search }}">
                        <button class="btn btn-secondary">Фільтр</button>
                    </form>

                    <hr>
                    <h4>Додати новий ключ</h4>
                    <form action="{{ route('admin.translations.store') }}" method="post" class="form-inline mb-4">
                        @csrf
                        <input type="text" name="group" class="form-control mr-2" placeholder="Група (напр. front)" required>
                        <input type="text" name="key" class="form-control mr-2" placeholder="Ключ (напр. read_more)" required>
                        <input type="text" name="uk" class="form-control mr-2" placeholder="Українською" style="min-width:200px;">
                        <input type="text" name="ru" class="form-control mr-2" placeholder="Російською" style="min-width:200px;">
                        <button class="btn btn-success">Додати</button>
                    </form>
                    <hr>

                    <form action="{{ route('admin.translations.update') }}" method="post">
                        @csrf
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Група</th>
                                        <th>Ключ</th>
                                        <th>Українською</th>
                                        <th>Російською</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rows as $i => $row)
                                        <tr>
                                            <td>
                                                {{ $row['group'] }}
                                                <input type="hidden" name="items[{{ $i }}][group]" value="{{ $row['group'] }}">
                                            </td>
                                            <td>
                                                {{ $row['key'] }}
                                                <input type="hidden" name="items[{{ $i }}][key]" value="{{ $row['key'] }}">
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $i }}][uk]" class="form-control" rows="1">{{ $row['uk'] }}</textarea>
                                            </td>
                                            <td>
                                                <textarea name="items[{{ $i }}][ru]" class="form-control" rows="1">{{ $row['ru'] }}</textarea>
                                            </td>
                                            <td>
                                                <button type="submit" form="delete-{{ $i }}" class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Видалити ключ «{{ $row['key'] }}»?');">Видалити</button>
                                            </td>
                                        </tr>
                                        <form id="delete-{{ $i }}" action="{{ route('admin.translations.destroy') }}" method="post" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="group" value="{{ $row['group'] }}">
                                            <input type="hidden" name="key" value="{{ $row['key'] }}">
                                        </form>
                                    @empty
                                        <tr>
                                            <td colspan="5">Перекладів поки немає. Додайте перший ключ вище.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($rows->isNotEmpty())
                            <button type="submit" class="btn btn-success">Зберегти всі зміни</button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection