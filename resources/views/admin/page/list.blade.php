@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <a href="{{ route('admin.page.new') }}" class="btn btn-primary">Добавить страницу</a>
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
            @if($pages->total())
                @foreach($pages as $page)
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-5">
                                    <div><small class="text-muted">Название статьи</small></div>
                                    {{ $page->name }}
                                </div>
                                <div class="col-3">
                                    <div><small class="text-muted">Дата публикации</small></div>
                                    {{ $page->created_at }}
                                </div>
                                <div class="col-3">
                                    <div><small class="text-muted">Дата редактирования</small></div>
                                    {{ $page->updated_at }}
                                </div>
                                <div class="col-1 text-right">
                                    <a href="{{ route('admin.page.edit', ['id' => $page->id]) }}"><i class="mdi mdi-24px mdi-table-edit"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>



                @endforeach
                <div class="row">
                    <div class="col-6">{{ $pages->links() }}</div>
                    <div class="col-6 text-right">Всего: {{ $pages->total() }}</div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        Ни одной страницы еще не добавлено. Срочно исправляйте ситуацию!
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection