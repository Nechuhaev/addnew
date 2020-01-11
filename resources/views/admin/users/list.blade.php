@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Список зарегистрированных пользователей</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.users') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            @if ($users)
                <div class="card">
                    <div class="card-body">
                        <form action="{{ $action_search }}" method="GET">
                            <div class="row">
                                <div class="col-md-9">
                                    <input type="text"
                                           name="name"
                                           value="{{ $s ?? "" }}"
                                           placeholder="Поиск по пользователям"
                                           class="form-control form-control-line">
                                </div>
                                <div class="col-md-3"><button class="btn btn-primary btn-block">Искать</button></div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card" style="margin-bottom: 5px;">
                    <div class="card-body" style="padding: 5px 1.25rem;font-weight: 900;color: #000;font-size: 12px">
                        <div class="row align-items-center">
                            <div class="col-1"></div>
                            <div class="col-2">
                                <a style="color: #6e6e6e" href="?order=username&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">ФИО (ЛОГИН)</a> {!! ($order == 'username') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
                            </div>
                            <div class="col-3">
                                <a style="color: #6e6e6e" href="?order=email&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">EMAIL</a> {!! ($order == 'email') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
                            </div>
                            <div class="col-2">
                                <a style="color: #6e6e6e" href="?order=created_at&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">РЕГИСТРАЦИЯ</a> {!! ($order == 'created_at') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
                            </div>
                            <div class="col-1">
                                <a style="color: #6e6e6e" href="?order=ads_count&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">КОЛ-ВО</a> {!! ($order == 'ads_count') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
                            </div>
                            <div class="col-2">
                                <a style="color: #6e6e6e" href="?order=updated_at&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">ПОСЛЕДНИЙ ВХОД</a> {!! ($order == 'updated_at') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
                            </div>
                            <div class="col-1 text-right">

                            </div>
                        </div>
                    </div>
                </div>
                @foreach($users as $user)
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-1">
                                    <div class="user-avatar" style="{{ $user->avatar_gradient }}">
                                        <div class="inner">
                                            {{ $user->avatar_text }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">ФИО</small></div>
                                    {{ $user->username }}
                                </div>
                                <div class="col-3">
                                    <div><small class="text-muted">email</small></div>
                                    {{ $user->email }}
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">Дата регистрации</small></div>
                                    {{ $user->created_date }}
                                </div>
                                <div class="col-1">
                                    <div><small class="text-muted">Кол-во</small></div>
                                    {{ $user->ads()->count() }}
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">Последний вход</small></div>
                                    {{ $user->updated_at }}
                                </div>
                                <div class="col-1 text-right">
                                    <a href="{{ route('admin.user', $user->id) }}"><i class="mdi mdi-24px mdi-account-edit"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
            <div class="row">
                <div class="col-6">{{ $users->appends(request()->query())->links() }}</div>
                <div class="col-6 text-right">Всего пользователей в базе: {{ $users->total() }}</div>
            </div>


        </div>
    </div>
@endsection