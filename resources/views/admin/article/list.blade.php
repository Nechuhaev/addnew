@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <a href="{{ route('admin.article.add') }}" class="btn btn-primary">Добавить статью</a>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.article.list') }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            @if($articles->total())
                @foreach($articles as $article)
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-1">
                                    <img src="{{ $article->image ?? asset('assets/front/img/placeholder.png') }}" class="img-fluid" alt="">
                                </div>
                                <div class="col-3">
                                    <div><small class="text-muted">Название статьи</small></div>
                                    {{ $article->name }}
                                </div>
                                <div class="col-3">
                                    <div><small class="text-muted">Категории</small></div>
                                    <ul class=cart-list-item-list>
                                    @foreach($article->categories()->get() as $category)
                                        <li>
                                            <a href="{{ route('admin.article.category.show', ['id' => $category->id]) }}">{{ $category->name }}</a>
                                        </li>
                                    @endforeach
                                    </ul>

                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">Дата публикации</small></div>
                                    {{ $article->created_at }}
                                </div>
                                <div class="col-2">
                                    <div><small class="text-muted">Дата редактирования</small></div>
                                    {{ $article->updated_at }}
                                </div>
                                <div class="col-1 text-right">
                                    <a href="{{ route('admin.article.edit', ['id' => $article->id]) }}"><i class="mdi mdi-24px mdi-table-edit"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>



                @endforeach
                    <div class="row">
                        <div class="col-6">{{ $articles->links() }}</div>
                        <div class="col-6 text-right">Всего: {{ $articles->total() }}</div>
                    </div>
            @else
                <div class="card">
                    <div class="card-body">
                        Ни одной статьи еще не добавлено. Срочно исправляйте ситуацию!
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection