@extends('front.layout')

@section('meta_title', "Страница не найдена | Доска бесплатных объявлений addnew.biz");
@section('meta_description', "Страницу, которую Вы ищете не удалось найти...");

@section('content')
    <main class="404-page">
        <div class="container">

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <p class="status-404">404</p>
                    <div class="status-text">
                        <p>Страница не найдена.<br>Возможно, ее никогда и не существовало.</p>
                        <a href="{{ route('index') }}" class="btn">Вернуться на главную</a>
                    </div>
                </div>
                <aside class="column-right">
                    @widget('front.adCategories')
                </aside>
            </div>

        </div>
    </main>
@endsection