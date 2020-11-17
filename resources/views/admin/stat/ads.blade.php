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
                        <div class="sales"></div>
                    </div>
                </div>


        </div>
    </div>

    <script>
        $(function () {
            var chart = new Chartist.Line('.sales', {
                labels: [1, 2, 3, 4, 5, 6, 7],
                series: [
                    [24.5, 0, 0, 0, 34.9, 48.6, 40],
                    [8.9, 5.8, 21.9, 5.8, 16.5, 6.5, 14.5]
                ]
            }, {
                low: 0,
                high: 48,
                showArea: true,
                fullWidth: true,
                plugins: [
                    Chartist.plugins.tooltip()
                ],
                axisY: {
                    onlyInteger: true,
                    scaleMinSpace: 40,
                    offset: 20,
                    labelInterpolationFnc: function(value) {
                        return (value / 10) + 'k';
                    }
                },

            });

            var chart = [chart];
        })
    </script>
@endsection