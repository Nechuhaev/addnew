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
                                <div class="col-3">
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
                                <div class="col-2">
                                    <div><small class="text-muted">Объявлений</small></div>
                                    {{ $user->ads()->count() }}
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
                <div class="col-6">{{ $users->links() }}</div>
                <div class="col-6 text-right">Всего пользователей в базе: {{ $users->total() }}</div>
            </div>


        </div>
    </div>
@endsection