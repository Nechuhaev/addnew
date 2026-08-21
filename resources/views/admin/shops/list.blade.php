@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Магазини</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    @if (Breadcrumbs::exists('admin.shops'))
                        {{ Breadcrumbs::render('admin.shops') }}
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
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <form action="{{ route('admin.shops') }}" method="get" class="form-inline mb-3">
                        <input type="text" name="search" class="form-control mr-2" style="min-width: 300px;"
                               placeholder="Пошук за назвою, email, телефоном, країною, ID"
                               value="{{ $search }}">
                        <button class="btn btn-secondary">Знайти</button>
                        @if($search)
                            <a href="{{ route('admin.shops') }}" class="btn btn-link">Скинути</a>
                        @endif
                    </form>

                    <a href="{{ route('admin.shops.skippedDomains') }}" class="btn btn-sm btn-secondary mb-3">Виключені домени (моніторинг)</a>

                                                                                <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Назва магазину</th>
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Товарів</th>
                                    <th>Страна</th>
                                    <th>Реєстрація</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($shops as $shop)
                                    <tr>
                                        <td>{{ $shop->id }}</td>
                                        <td>{{ $shop->username }}</td>
                                        <td>
                                            @if($shop->email_matched === true)
                                                <span class="text-success">{{ $shop->email }}</span>
                                            @elseif($shop->email_matched === false)
                                                <span class="text-warning">{{ $shop->email }}</span>
                                                <img src="https://st.depositphotos.com/34078792/54886/v/1600/depositphotos_548866872-stock-illustration-warn-sign-triangle-yellow-caution.jpg"
                                                     alt="Email автоматично оновлено" title="Email автоматично оновлено після звірки з сайтом магазину"
                                                     style="width:16px;height:16px;vertical-align:middle;">
                                            @else
                                                {{ $shop->email }}
                                            @endif
                                        </td>
                                        <td>{{ $shop->telephone ?? '—' }}</td>
                                        <td>{{ $shop->products_count }}</td>
                                        <td>{{ $shop->countries_display }}</td>
                                        <td>{{ $shop->created_date }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.shops.edit', $shop->id) }}" class="btn btn-sm btn-secondary">Опис</a>
                                            <a href="{{ route('admin.shops.products', $shop->id) }}" class="btn btn-sm btn-secondary">Товари</a>
                                            <a href="{{ route('admin.shops.impersonate', $shop->id) }}" class="btn btn-sm btn-warning"
                                               onclick="return confirm('Увійти в акаунт магазину «{{ $shop->username }}»?');">
                                                Увійти як магазин
                                            </a>
                                            <form action="{{ route('admin.shops.destroy', $shop->id) }}" method="post" style="display:inline;"
                                                  onsubmit="return confirm('УВАГА: буде видалено акаунт магазину «{{ $shop->username }}» і ВСІ його товари ({{ $shop->products_count }} шт.) НАЗАВЖДИ. Цю дію не можна скасувати. Продовжити?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Видалити</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">Магазинів поки немає</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $shops->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection