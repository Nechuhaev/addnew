@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Создать ящик</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">

                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <form class="form-horizontal form-material" method="POST" action="{{ route('admin.blocked-email.create') }}">
        @csrf
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="form-group col-8" style="display:inline-block;">
                        <label for="email" class="col-md-12">Ящик</label>
                        <div class="col-md-12">
                            <input type="text required"
                                   name="mailbox"
                                   placeholder="yahoo.com"
                                   value=""
                                   class="form-control form-control-line">
                        </div>
                    </div>
                    <div class="form-group col-3" style="display:inline-block;">
                        <div class="col-sm-12">
                            <button class="btn btn-success">Добавить ящик</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Column -->
    </div>
    </form>
@endsection