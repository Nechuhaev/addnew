@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Настройки SEO описаний</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Главная</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">SEO</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if($items)

            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                            <b>Страница</b>
                        </div>
                        <div class="col-2 text-center">
                            <b>Описание</b>
                        </div>
                        <div class="col-2 text-center">
                            <b>Meta-title</b>
                        </div>
                        <div class="col-2 text-center">
                            <b>Meta-description</b>
                        </div>
                        <div class="col-2 text-center">
                            <b>Действия</b>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($items as $item)
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-4">
                                {{ $item['name'] }}
                            </div>
                            <div class="col-2 text-center">
                                @if($item['description'])
                                    <i style="font-size: 22px; color: #23bf4c" class="mdi mdi-check"></i>
                                @else
                                    <i style="font-size: 22px; color: red;" class="mdi mdi-close"></i>
                                @endif
                            </div>
                            <div class="col-2 text-center">
                                @if($item['meta_title'])
                                    <i style="font-size: 22px; color: #23bf4c" class="mdi mdi-check"></i>
                                @else
                                    <i style="font-size: 22px; color: red;" class="mdi mdi-close"></i>
                                @endif
                            </div>
                            <div class="col-2 text-center">
                                @if($item['meta_description'])
                                    <i style="font-size: 22px; color: #23bf4c" class="mdi mdi-check"></i>
                                @else
                                    <i style="font-size: 22px; color: red;" class="mdi mdi-close"></i>
                                @endif
                            </div>
                            <div class="col-2 text-center">
                                <a href="{{ $item['action'] }}" class="btn btn-default btn-block">Изменить</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @endif
        </div>
    </div>
@endsection