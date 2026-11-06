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
                                <div class="col-md-7">
                                    <input type="text"
                                           name="name"
                                           value="{{ $s ?? "" }}"
                                           placeholder="Поиск по пользователям"
                                           class="form-control form-control-line">
                                </div>
                                <div class="col-md-3"><button class="btn btn-primary btn-block">Искать</button></div>
                                <div class="col-2">
                                    <a id="deleteMany" href="/admin/user/deleteMany" class="btn btn-danger btn-block">Удалить</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                @if (!empty($ipFilter))
                    <div class="alert alert-warning d-flex justify-content-between align-items-center">
                        <span>Аккаунты, зарегистрированные с IP <b>{{ $ipFilter }}</b>: {{ $users->total() }}. Отметьте их и нажмите «Удалить», чтобы удалить разом.</span>
                        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary">Сбросить фильтр</a>
                    </div>
                @endif

                <div class="card" style="margin-bottom: 5px;">
                    <div class="card-body" style="padding: 5px 1.25rem;font-weight: 900;color: #000;font-size: 12px">
                        <div class="row align-items-center">
                            <div class="col-1"></div>
                            <div class="col-1"></div>
                            <div class="col-2">
                                <a style="color: #6e6e6e" href="?order=username&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">ФИО (ЛОГИН)</a> {!! ($order == 'username') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
                            </div>
                            <div class="col-2">
                                <a style="color: #6e6e6e" href="?order=email&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">EMAIL</a> {!! ($order == 'email') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
                            </div>
                            <div class="col-2">
                                <a style="color: #6e6e6e" href="?order=created_at&direction={{ ($direction == 'asc') ? 'desc' : 'asc'  }}">РЕГИСТРАЦИЯ · IP</a> {!! ($order == 'created_at') ? ($direction != 'asc') ? '<i class="mdi mdi-arrow-up"></i>' : '<i class="mdi mdi-arrow-down"></i>' : '';   !!}
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
                                    <input type="checkbox" name="id[]" value="{{ $user->id }}">
                                </div>
                                <div class="col-1">
                                    <img src="{{ $user->image ?? asset('assets/front/img/placeholder.png') }}" alt="user" width="60">
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">ФИО</small></div>
                                    {{ $user->username }}
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">email</small></div>
                                    {{ $user->email }}
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">Дата регистрации · IP</small></div>
                                    {{ $user->created_date }}
                                    @if ($user->register_ip)
                                        @php($ipN = $ipCounts[$user->register_ip] ?? 1)
                                        <div>
                                            <a href="{{ route('admin.users', ['ip' => $user->register_ip]) }}" title="Все аккаунты с этого IP"
                                               style="font-size:12px">{{ $user->register_ip }}</a>
                                            @if ($ipN > 1)
                                                <span class="badge badge-{{ $ipN >= 3 ? 'danger' : 'warning' }}" title="Аккаунтов с этого IP">{{ $ipN }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <div><small class="text-muted">IP —</small></div>
                                    @endif
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

                                    <a onclick="return confirm('Пользователь {{ $user->username }} будет удален вместе с его объявлениями. Подтвердите действия!')"
                                       href="{{ route('admin.user.delete', $user->id) }}"><i class="text-danger mdi mdi-24px mdi-delete"></i></a>
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