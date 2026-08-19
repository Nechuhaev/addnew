@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Статистика по категоріях оголошень</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    @if (Breadcrumbs::exists('admin.stat.adCategories'))
                        {{ Breadcrumbs::render('admin.stat.adCategories') }}
                    @endif
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
                    <div class="ad-category-chart" style="height: 350px;"></div>
                    <small class="text-muted">Показано топ-15 категорій за кількістю оголошень. Повний список — у таблиці нижче.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Оголошення й товари по категоріях</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Категорія</th>
                                    <th>Оголошень</th>
                                    <th>Товарів (магазини)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($category_stats as $i => $cat)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ $cat->name }}</td>
                                        <td>{{ $cat->total_ads }}</td>
                                        <td>{{ $cat->total_products }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">Даних поки немає</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('footer-scripts')
    <script>
        $(function () {
            var chart = new Chartist.Bar('.ad-category-chart', {
                labels: {!! json_encode($chartlist_labels) !!},
                series: [
                    {!! json_encode($chartlist_values) !!}
                ]
            }, {
                low: 0,
                axisY: {
                    onlyInteger: true,
                },
                axisX: {
                    labelInterpolationFnc: function(value) {
                        return value.length > 12 ? value.substring(0, 12) + '…' : value;
                    }
                }
            });
        })
    </script>
@endsection