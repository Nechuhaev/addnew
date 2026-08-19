@extends('admin.layout')
@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Товари магазину: {{ $shop->username }}</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    @if (Breadcrumbs::exists('admin.shops.products'))
                        {{ Breadcrumbs::render('admin.shops.products', $shop) }}
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
                    <a href="{{ route('admin.shops') }}" class="btn btn-sm btn-secondary mb-3">&larr; До списку магазинів</a>
                    <a href="{{ route('admin.shops.edit', $shop->id) }}" class="btn btn-sm btn-secondary mb-3">Опис магазину</a>

                    @if($inactiveCount > 0)
                        <form action="{{ route('admin.shops.products.deleteInactive', $shop->id) }}" method="post" style="display:inline;"
                              onsubmit="return confirm('Видалити ВСІ неактивні товари (немає в наявності) цього магазину — {{ $inactiveCount }} шт.? Цю дію не можна скасувати.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger mb-3">
                                Видалити всі неактивні ({{ $inactiveCount }})
                            </button>
                        </form>
                    @endif

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Фото</th>
                                    <th>Назва</th>
                                    <th>Ціна</th>
                                    <th>Наявність</th>
                                    <th>Статус</th>
                                    <th>SEO</th>
                                    <th>Оновлено</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td>{{ $product->id }}</td>
                                        <td>
                                            @if($product->image)
                                                <img src="{{ $product->image }}" alt="" style="width:40px;height:40px;object-fit:cover;" onerror="this.onerror=null;this.src='{{ asset('assets/front/img/placeholder.png') }}';">
                                            @endif
                                        </td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->price }} {{ optional($product->currency)->code }}</td>
                                        <td>
                                            @if($product->stock === 'in_stock')
                                                <span class="text-success">В наявності</span>
                                            @else
                                                <span class="text-danger">Немає</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($product->stock !== 'in_stock')
                                                <span class="badge badge-danger">НЕАКТИВНЕ</span>
                                                @if($product->priceChecks()->where('status', 'auto_suspended')->exists())
                                                    <span title="Автоматично призупинено — джерело недоступне">⚠️ авто</span>
                                                @endif
                                            @elseif($product->status === 'active')
                                                <span class="badge badge-success">Активне</span>
                                            @elseif($product->status === 'suspend')
                                                <span class="badge badge-warning">Призупинено</span>
                                                @if($product->priceChecks()->where('status', 'auto_suspended')->exists())
                                                    <span title="Автоматично призупинено — джерело недоступне">⚠️ авто</span>
                                                @endif
                                            @else
                                                <span class="badge badge-secondary">Архів</span>
                                            @endif
                                        </td>
                                        <td>{{ $product->seo_optimized ? '✅' : '—' }}</td>
                                        <td>{{ $product->updated_at ? $product->updated_at->format('d.m.Y H:i') : '—' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('admin.ad.edit', $product->id) }}" class="btn btn-sm btn-secondary">Редагувати</a>
                                            <form action="{{ route('admin.shops.product.delete', $product->id) }}" method="post" style="display:inline;"
                                                  onsubmit="return confirm('Видалити товар «{{ $product->name }}» назавжди? Цю дію не можна скасувати.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Видалити</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9">У цього магазину поки немає товарів</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection