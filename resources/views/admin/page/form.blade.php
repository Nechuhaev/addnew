@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">{{ $default['name'] ?? 'Добавить статью' }} </h4>
            </div>
        </div>
    </div>
@endsection

@section('content')


    <form class="form-horizontal form-material" method="POST" action="{{ $action }}">
        @csrf
        @if(isset($page_id))
            <input type="hidden" name="page_id" value="{{ $page_id }}">
        @endif
        <div class="row">

            <div class="col-lg-4 col-xlg-3 col-md-5">

                @include('admin.form-widgets.meta_data', ['default' => $default])

                @include('admin.form-widgets.sort_order', ['default' => $default])

            </div>

            <div class="col-lg-8 col-xlg-9 col-md-7">
                <div class="card">
                    <div class="card-body">

                    @include('admin.form-widgets.title', ['default' => $default])
                    @include('admin.form-widgets.slug', ['default' => $default])
                    @include('admin.form-widgets.content', ['default' => $default])
                    </div>
                </div>


                @include('admin.form-widgets.save_button', ['delete_action_route' => route('admin.page.delete')])
            </div>

        </div>
    </form>


@endsection