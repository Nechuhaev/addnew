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


                <div class="card">
                    <div class="card-body">

                        @foreach($filters as $filter)
                            <h3>{{ $filter['heading'] }}</h3>
                            @foreach($filter['values'] as $filter_value)
                                <a href="{{ $filter_value['value'] }}" class="chartlist--filter--item {{ $filter_value['is_active'] ? 'active' : null }}">{{ $filter_value['name'] }}</a>
                            @endforeach
                        @endforeach

                        <div class="sales"></div>
                    </div>
                </div>


        </div>
    </div>
@endsection

@section('footer-scripts')
    <script>
        $(function () {
            var chart = new Chartist.Line('.sales', {
                labels: [{{ implode(', ', $chartlist_labels) }}],
                series: [
@foreach($chartlist_lines as $chartlist_line)
                    [{{ implode(', ', $chartlist_line['x']) }}],
@endforeach
                ]
            }, {
                low: 0,
                // high: 3000,
                showArea: true,
                // fullWidth: true,
                plugins: [
                    Chartist.plugins.tooltip()
                ],
                axisY: {
                    onlyInteger: true,
                },

            });

            var chart = [chart];
        })
    </script>
@endsection