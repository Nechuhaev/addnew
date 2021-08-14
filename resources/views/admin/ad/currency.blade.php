@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $currency->name ?? 'Валюты' }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.adCurrencies') }}
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
                        @if($currency)
                            <input type="hidden" name="currency_id" value="{{ $currency->id }}">
                        @endif

                        <div class="form-group">
                            <label for="name">Название</label>
                            <div>
                                <input type="text"
                                       name="name"
                                       id="name"
                                       value="{{ old('name') ?? $currency->name ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rate">Курс (1 - для валюты по умолчанию)</label>
                            <div>
                                <input type="text"
                                       name="rate"
                                       id="rate"
                                       value="{{ old('rate') ?? $currency->rate ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="content">Трехзначный код валюты</label>

                            <div>
                                <input type="text"
                                       name="code"
                                       id="code"
                                       value="{{ old('code') ?? $currency->code ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_title">Символ валюты</label>
                            <div>
                                <input type="text"
                                       name="symbol"
                                       id="symbol"
                                       value="{{ old('symbol') ?? $currency->symbol ?? '' }}"
                                       placeholder=""
                                       class="form-control form-control-line">
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
                    @if($currencies->items())
                        <table class="table table-bordered table-hover table-middle-cell">
                            <tr>
                                <th>Название</th>
                                <th>Курс</th>
                                <th>Код</th>
                                <th>Символ</th>
                                <th></th>
                            </tr>

                            @foreach($currencies as $currency)
                                <tr>
                                    <td>{{ $currency->name . (($currency->is_default) ? ' (по умолчанию)' : '') }}</td>
                                    <td>{{ $currency->rate }}</td>
                                    <td>{{ $currency->code }}</td>
                                    <td>{{ $currency->symbol }}</td>
                                    <td class="text-center cell-actions">
                                        <a href="{{ route('admin.adCurrencies.edit', ['id' => $currency->id]) }}"><i class="mdi mdi-18px mdi-table-edit"></i></a>
                                        <a href="{{ route('admin.adCurrencies.delete', ['id' => $currency->id]) }}" onclick="return confirm('Вы пытаетесь удалить валюту {{ $currency->name }}. Подтвердите действие.')" class="text-danger"><i class="mdi mdi-18px mdi-delete"></i></a>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    @else
                        Валюты не найдены
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection