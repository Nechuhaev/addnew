@extends('admin.layout')

@section('breadcrumbs')
    <div class="page-breadcrumb">
        <div class="row">
            <div class="col-5 align-self-center">
                <h4 class="page-title">Работа с стоп-словами</h4>
            </div>
            <div class="col-7 align-self-center">
                <div class="d-flex align-items-center justify-content-end">
                    {{ Breadcrumbs::render('admin.stop-word') }}
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
                    <h2>С таким не шутят</h2>
                    <p>Будут удалены все объявления, которые содержат одно из стоп слов</p>
                    <p>Стоп слова должны быть перечислены через запятую, поиск ведется по строгому совпадению, минимальная длина одного слова - 3 символа</p>

                    <b>Здесь еще стоит напомнить о важности бекапов</b>

                    @if(isset($warning))
                    <h2 class="text-danger text-xl-center">{{ $warning }}</h2>
                    @endif

                    <hr>
                    <form action="{{ $action }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="words" class="col-md-12">Список стоп-слов</label>
                            <div class="col-md-12">
                            <textarea rows="5"
                                      id="words"
                                      name="words"
                                      class="form-control form-control-line">{{ $words ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                @if(isset($warning))
                                    <button class="btn btn-warning">Удалить</button>
                                @else
                                    <button class="btn btn-success">Проверить</button>
                                @endif

                            </div>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </div>
@endsection

