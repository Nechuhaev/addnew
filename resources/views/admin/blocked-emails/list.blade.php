@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Список запрещенных почтовых ящиков</h4>
            </div>
            <div class="col-7 align-self-center d-flex align-items-center justify-content-end">
                <div class="">
                    <a href="/admin/blocked-emails/new" style="color:white; margin-right: 5px" class="btn btn-success">Добавить</a>
                </div>
                <div class="">
                    <a id="deleteMany" href="/admin/blocked-emails/deleteMany" class="btn btn-danger btn-block">Удалить</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            @if ($emails)

                <div class="card" style="margin-bottom: 5px;">
                    <div class="card-body" style="padding: 5px 1.25rem;font-weight: 900;color: #000;font-size: 12px">
                        <div class="row align-items-center">
                            <div class="col-1">
                            </div>
                            <div class="col-2">
                                <span>ID</span>
                            </div>
                            <div class="col-8">
                                <span>Почтовый ящик</span>
                            </div>
                            <div class="col-1 text-right">

                            </div>
                        </div>
                    </div>
                </div>
                @foreach($emails as $email)
                    <div class="card" style="margin-bottom:3px;">
                        <div class="card-body" style="padding: 0.25rem;">
                            <div class="row align-items-center">
                                <div class="col-1">
                                    <input type="checkbox" name="id[]" value="{{ $email->id }}">
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">ID</small></div>
                                    {{ $email->id }}
                                </div>
                                <div class="col-8">
                                    <div><small class="text-muted">Ящик</small></div>
                                    {{ $email->mailbox }}
                                </div>
                                <div class="col-1 text-right">
                                    <a onclick="return confirm('Почтовый ящик {{ $email->mailbox }} будет удален. Подтвердите действия!')"
                                       href="{{ route('admin.blocked-email.delete', $email->id) }}"><i class="text-danger mdi mdi-24px mdi-delete"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
            <div class="row">
                <div class="col-6"></div>
                <div class="col-6 text-right">Всего ящиков в базе: {{ count($emails) }}</div>
            </div>


        </div>
    </div>
@endsection