@extends('admin.layout')


@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Панель управления addnew.biz</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.index') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Панель управления</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('content')
    <!-- ============================================================== -->
    <!-- Email campaign chart -->
    <!-- ============================================================== -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Объявления за последние 7 дней</h4>
                    <div class="sales ct-charts mt-3"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title m-b-0">Объявления</h4>
                    <h2 class="font-light">{{ $ads_count }}</h2>
                    <div class="m-t-30">
                        <div class="row text-center">
                            <div class="col-6 border-right">
                                <h4 class="m-b-0">{{ $ads_count_today }}</h4>
                                <span class="font-14 text-muted">Сегодня</span>
                            </div>
                            <div class="col-6">
                                <h4 class="m-b-0">{{ $ads_count_week }}</h4>
                                <span class="font-14 text-muted">за 30 дней</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title m-b-0">Пользователи</h4>
                    <h2 class="font-light">{{ $customers_count }}</h2>
                    <div class="m-t-30">
                        <div class="row text-center">
                            <div class="col-6 border-right">
                                <h4 class="m-b-0">{{ $customers_count_today }}</h4>
                                <span class="font-14 text-muted">Сегодня</span>
                            </div>
                            <div class="col-6">
                                <h4 class="m-b-0">{{ $customers_count_week }}</h4>
                                <span class="font-14 text-muted">за 30 дней</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ============================================================== -->
    <!-- Email campaign chart -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Ravenue - page-view-bounce rate -->
    <!-- ============================================================== -->
    <div class="row">
        <!-- column -->
        @if($top_countries)
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Топ 10 стран</h4>
                </div>
                <div class="table-responsive">

                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th class="border-top-0">№</th>
                            <th class="border-top-0">СТРАНА</th>
                            <th class="border-top-0 text-center">ВСЕГО ОБЪЯВЛЕНИЙ</th>
                            <th class="border-top-0 text-center">ПОСЛЕДНЕЕ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($top_countries as $top_country)
                            <tr>
                                <td class="txt-oflo">{{ $loop->iteration}}</td>
                                <td class="txt-oflo">{{ $top_country->name }}</td>
                                <td class="text-center"><span class="label label-success label-rounded">{{ $top_country->ads_count }}</span> </td>
                                <td class="txt-oflo text-center">{{ date('d-m-Y H:s', strtotime($top_country->last_created_at)) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif


        @if($top_categories)
            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Топ 10 категорий</h4>
                    </div>
                    <div class="table-responsive">

                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th class="border-top-0">№</th>
                                <th class="border-top-0">СТРАНА</th>
                                <th class="border-top-0 text-center">ВСЕГО ОБЪЯВЛЕНИЙ</th>
                                <th class="border-top-0 text-center">ПОСЛЕДНЕЕ</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($top_categories as $top_category)
                                <tr>
                                    <td class="txt-oflo">{{ $loop->iteration}}</td>
                                    <td class="txt-oflo">{{ $top_category->name }}</td>
                                    <td class="text-center"><span class="label label-info label-rounded">{{ $top_category->ads_count }}</span> </td>
                                    <td class="txt-oflo text-center">{{ date('d-m-Y H:s', strtotime($top_category->last_created_at)) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- ============================================================== -->
    <!-- Ravenue - page-view-bounce rate -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Recent comment and chats -->
    <!-- ============================================================== -->
    {{--<div class="row">--}}
        {{--<!-- column -->--}}
        {{--<div class="col-lg-6">--}}
            {{--<div class="card">--}}
                {{--<div class="card-body">--}}
                    {{--<h4 class="card-title">Recent Comments</h4>--}}
                {{--</div>--}}
                {{--<div class="comment-widgets" style="height:430px;">--}}
                    {{--<!-- Comment Row -->--}}
                    {{--<div class="d-flex flex-row comment-row m-t-0">--}}
                        {{--<div class="p-2">--}}
                            {{--<img src="{{ asset('assets/admin/assets/images/users/1.jpg') }}" alt="user" width="50" class="rounded-circle">--}}
                        {{--</div>--}}
                        {{--<div class="comment-text w-100">--}}
                            {{--<h6 class="font-medium">James Anderson</h6>--}}
                            {{--<span class="m-b-15 d-block">Lorem Ipsum is simply dummy text of the printing and type setting industry. </span>--}}
                            {{--<div class="comment-footer">--}}
                                {{--<span class="text-muted float-right">April 14, 2016</span>--}}
                                {{--<span class="label label-rounded label-primary">Pending</span>--}}
                                {{--<span class="action-icons">--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-pencil-alt"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-check"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-heart"></i>--}}
                                                {{--</a>--}}
                                            {{--</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    {{--<!-- Comment Row -->--}}
                    {{--<div class="d-flex flex-row comment-row">--}}
                        {{--<div class="p-2">--}}
                            {{--<img src="{{ asset('assets/admin/assets/images/users/4.jpg') }}" alt="user" width="50" class="rounded-circle">--}}
                        {{--</div>--}}
                        {{--<div class="comment-text active w-100">--}}
                            {{--<h6 class="font-medium">Michael Jorden</h6>--}}
                            {{--<span class="m-b-15 d-block">Lorem Ipsum is simply dummy text of the printing and type setting industry. </span>--}}
                            {{--<div class="comment-footer ">--}}
                                {{--<span class="text-muted float-right">April 14, 2016</span>--}}
                                {{--<span class="label label-success label-rounded">Approved</span>--}}
                                {{--<span class="action-icons active">--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-pencil-alt"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="icon-close"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-heart text-danger"></i>--}}
                                                {{--</a>--}}
                                            {{--</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    {{--<!-- Comment Row -->--}}
                    {{--<div class="d-flex flex-row comment-row">--}}
                        {{--<div class="p-2">--}}
                            {{--<img src="{{ asset('assets/admin/assets/images/users/5.jpg') }}" alt="user" width="50" class="rounded-circle">--}}
                        {{--</div>--}}
                        {{--<div class="comment-text w-100">--}}
                            {{--<h6 class="font-medium">Johnathan Doeting</h6>--}}
                            {{--<span class="m-b-15 d-block">Lorem Ipsum is simply dummy text of the printing and type setting industry. </span>--}}
                            {{--<div class="comment-footer">--}}
                                {{--<span class="text-muted float-right">April 14, 2016</span>--}}
                                {{--<span class="label label-rounded label-danger">Rejected</span>--}}
                                {{--<span class="action-icons">--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-pencil-alt"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-check"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-heart"></i>--}}
                                                {{--</a>--}}
                                            {{--</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    {{--<!-- Comment Row -->--}}
                    {{--<div class="d-flex flex-row comment-row m-t-0">--}}
                        {{--<div class="p-2">--}}
                            {{--<img src="{{ asset('assets/admin/assets/images/users/2.jpg') }}" alt="user" width="50" class="rounded-circle">--}}
                        {{--</div>--}}
                        {{--<div class="comment-text w-100">--}}
                            {{--<h6 class="font-medium">Steve Jobs</h6>--}}
                            {{--<span class="m-b-15 d-block">Lorem Ipsum is simply dummy text of the printing and type setting industry. </span>--}}
                            {{--<div class="comment-footer">--}}
                                {{--<span class="text-muted float-right">April 14, 2016</span>--}}
                                {{--<span class="label label-rounded label-primary">Pending</span>--}}
                                {{--<span class="action-icons">--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-pencil-alt"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-check"></i>--}}
                                                {{--</a>--}}
                                                {{--<a href="javascript:void(0)">--}}
                                                    {{--<i class="ti-heart"></i>--}}
                                                {{--</a>--}}
                                            {{--</span>--}}
                            {{--</div>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                {{--</div>--}}
            {{--</div>--}}
        {{--</div>--}}
        {{--<!-- column -->--}}
        {{--<div class="col-lg-6">--}}
            {{--<div class="card">--}}
                {{--<div class="card-body">--}}
                    {{--<h4 class="card-title">Temp Guide</h4>--}}
                    {{--<div class="d-flex align-items-center flex-row m-t-30">--}}
                        {{--<div class="display-5 text-info"><i class="wi wi-day-showers"></i> <span>73<sup>°</sup></span></div>--}}
                        {{--<div class="m-l-10">--}}
                            {{--<h3 class="m-b-0">Saturday</h3><small>Ahmedabad, India</small>--}}
                        {{--</div>--}}
                    {{--</div>--}}
                    {{--<table class="table no-border mini-table m-t-20">--}}
                        {{--<tbody>--}}
                        {{--<tr>--}}
                            {{--<td class="text-muted">Wind</td>--}}
                            {{--<td class="font-medium">ESE 17 mph</td>--}}
                        {{--</tr>--}}
                        {{--<tr>--}}
                            {{--<td class="text-muted">Humidity</td>--}}
                            {{--<td class="font-medium">83%</td>--}}
                        {{--</tr>--}}
                        {{--<tr>--}}
                            {{--<td class="text-muted">Pressure</td>--}}
                            {{--<td class="font-medium">28.56 in</td>--}}
                        {{--</tr>--}}
                        {{--<tr>--}}
                            {{--<td class="text-muted">Cloud Cover</td>--}}
                            {{--<td class="font-medium">78%</td>--}}
                        {{--</tr>--}}
                        {{--</tbody>--}}
                    {{--</table>--}}
                    {{--<ul class="row list-style-none text-center m-t-30">--}}
                        {{--<li class="col-3">--}}
                            {{--<h4 class="text-info"><i class="wi wi-day-sunny"></i></h4>--}}
                            {{--<span class="d-block text-muted">09:30</span>--}}
                            {{--<h3 class="m-t-5">70<sup>°</sup></h3>--}}
                        {{--</li>--}}
                        {{--<li class="col-3">--}}
                            {{--<h4 class="text-info"><i class="wi wi-day-cloudy"></i></h4>--}}
                            {{--<span class="d-block text-muted">11:30</span>--}}
                            {{--<h3 class="m-t-5">72<sup>°</sup></h3>--}}
                        {{--</li>--}}
                        {{--<li class="col-3">--}}
                            {{--<h4 class="text-info"><i class="wi wi-day-hail"></i></h4>--}}
                            {{--<span class="d-block text-muted">13:30</span>--}}
                            {{--<h3 class="m-t-5">75<sup>°</sup></h3>--}}
                        {{--</li>--}}
                        {{--<li class="col-3">--}}
                            {{--<h4 class="text-info"><i class="wi wi-day-sprinkle"></i></h4>--}}
                            {{--<span class="d-block text-muted">15:30</span>--}}
                            {{--<h3 class="m-t-5">76<sup>°</sup></h3>--}}
                        {{--</li>--}}
                    {{--</ul>--}}
                {{--</div>--}}
            {{--</div>--}}

        {{--</div>--}}
    {{--</div>--}}
    <!-- ============================================================== -->
    <!-- Recent comment and chats -->
    <!-- ============================================================== -->
@endsection

