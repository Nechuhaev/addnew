@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Dashboard</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="#">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
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
            <div class="card">

            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">

                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-1">
                            <div class="user-avatar" style="background: linear-gradient(#c1cfdc, #da89c1);">
                                <div class="inner">
                                    A.K.
                                </div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div><small class="text-muted">ФИО</small></div>
                            Anatolii Koziura
                        </div>
                        <div class="col-3">
                            <div><small class="text-muted">email</small></div>
                            anatolii.koziura@gmail.com
                        </div>
                        <div class="col-2">
                            <div><small class="text-muted">Дата регистрации</small></div>
                            04-11-2019
                        </div>
                        <div class="col-2">
                            <div><small class="text-muted">Объявлений</small></div>
                            2
                        </div>
                        <div class="col-1 text-right">
                            <a href="http://addnew.loc/admin/user/1"><i class="mdi mdi-24px mdi-account-edit"></i></a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>



@endsection