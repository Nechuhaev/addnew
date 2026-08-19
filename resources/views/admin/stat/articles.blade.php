@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Аналітика статей блогу</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{-- Якщо breadcrumb-ключ ще не зареєстровано в routes/breadcrumbs.php,
                         цей блок просто не виведеться, а не впаде з помилкою --}}
                    @if (Breadcrumbs::exists('admin.stat.articles'))
                        {{ Breadcrumbs::render('admin.stat.articles') }}
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
                    <div class="sales"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Топ-10 статей за переглядами</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Стаття</th>
                                    <th>Переглядів</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top_articles as $i => $article)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><a href="{{ route('blog.article', $article->slug) }}" target="_blank">{{ $article->name }}</a></td>
                                        <td>{{ $article->total_views }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3">Даних поки немає</td>
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
            var chart = new Chartist.Line('.sales', {
                labels: [{{ implode(', ', $chartlist_labels) }}],
                series: [
@foreach($chartlist_lines as $chartlist_line)
                    [{{ implode(', ', $chartlist_line['x']) }}],
@endforeach
                ]
            }, {
                low: 0,
                showArea: true,
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
