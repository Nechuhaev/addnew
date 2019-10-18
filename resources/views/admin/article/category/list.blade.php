@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <a href="{{ route('admin.article.category.add') }}" class="btn btn-primary">Добавить категорию</a>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.article.categories') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">

            @if($categories->total())

                @foreach($categories as $category)
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-1">
                                    <div><small class="text-muted">ID</small></div>
                                    {{ $category->id }}
                                </div>
                                <div class="col-6">
                                    <div><small class="text-muted">Название категории</small></div>
                                    {{ $category->name }}
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">Cтатей</small></div>
                                    5
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">Сортировка</small></div>
                                    {{ $category->sort_order }}
                                </div>
                                <div class="col-1 text-right">
                                    <a href="{{ route('admin.article.category.show', $category->id) }}"><i class="mdi mdi-24px mdi-account-edit"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="row">
                    <div class="col-6">{{ $categories->links() }}</div>
                    <div class="col-6 text-right">Всего категорий: {{ $categories->total() }}</div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        Ни одной категории еще не добавлено.
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection