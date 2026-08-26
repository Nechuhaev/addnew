@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Статистика магазинов</h4>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted">
                        Зведена статистика по всіх магазинах: перегляди сторінки магазину,
                        перегляди товарів, покази контактів, переходи на сайт-джерело.
                        Натисніть на назву магазину, щоб побачити деталізацію по товарах.
                    </p>

                    @if($shops->isEmpty())
                        <p>Даних поки немає.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Магазин</th>
                                        <th class="text-center">Товарів</th>
                                        <th class="text-center">Перегляди магазину</th>
                                        <th class="text-center">Перегляди товарів</th>
                                        <th class="text-center">Показ контактів</th>
                                        <th class="text-center">Перехід на сайт</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shops as $shop)
                                        <tr>
                                            <td>{{ $shop['id'] }}</td>
                                            <td>
                                                <a href="{{ route('admin.shops.stats.show', ['id' => $shop['id']]) }}">
                                                    {{ $shop['name'] }}
                                                </a>
                                                <br><small class="text-muted">{{ $shop['email'] }}</small>
                                            </td>
                                            <td class="text-center">{{ $shop['products_count'] }}</td>
                                            <td class="text-center">{{ $shop['shop_views'] }}</td>
                                            <td class="text-center">{{ $shop['product_views'] }}</td>
                                            <td class="text-center">{{ $shop['click_contacts'] }}</td>
                                            <td class="text-center">{{ $shop['click_shop_link'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection