@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <a href="{{ route('admin.ad') }}" class="btn btn-primary">Добавить объявление</a>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.ads') }}
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
                <form action="{{ $search_action }}" method="get" class="search-form">
                    <div class="row">
                        <div class="col-10">
                            <input type="text"
                                   name="search"
                                   class="form-control"
                                   value="{{ $search ?? '' }}"
                                   placeholder="Поиск по названию / описанию / телефону и email">
                        </div>
                        <div class="col-2">
                            <button class="btn btn-default btn-block">Искать</button>
                        </div>
                    </div>
                </form>
            </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($ads->items())
                @foreach($ads as $ad)
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-2">
                                <img src="{{ $ad->image }}" class="img-fluid">
                            </div>
                            <div class="col-3">
                                <b>{{ $ad->name }}</b>
                                <div><small class="text-muted">Категория</small></div>
                                {{ $ad->category->path }}
                            </div>
                            <div class="col-3">
                                <div><small class="text-muted">Добавлено</small></div>
                                {{ $ad->created_date }}
                                <div><small class="text-muted">Автор</small></div>
                                {{ $ad->user->email }}
                            </div>
                            <div class="col-3">
                                <div><small class="text-muted">Город</small></div>
                                {{ $ad->city->path }}
                            </div>
                            <div class="col-1 text-right">
                                <a href="{{ route('admin.ad.edit', ['id' => $ad->id]) }}"><i class="mdi mdi-24px mdi-account-edit"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
                {{ $ads->links() }}
            @else
                <div class="card">
                    <div class="card-body">
                        Объявлений не найдено
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection