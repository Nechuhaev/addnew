@extends('front.layout')

@section('meta_title', app()->getLocale() === 'ru' ? "Страница не найдена | Доска бесплатных объявлений addnew.biz" : "Сторінку не знайдено | Дошка безкоштовних оголошень addnew.biz")
@section('meta_description', app()->getLocale() === 'ru' ? "Страницу, которую Вы ищете не удалось найти..." : "Сторінку, яку ви шукаєте, не вдалося знайти...")

@section('content')
    @php $isRu = app()->getLocale() === 'ru'; @endphp
    <main class="404-page">
        <div class="container">

            <div class="columns columns-nowrap">
                <div class="column-content">
                    <p class="status-404">404</p>
                    <div class="status-text">
                        @if($isRu)
                            <p>Страница не найдена.<br>Возможно, ее никогда и не существовало.</p>
                        @else
                            <p>Сторінку не знайдено.<br>Можливо, її ніколи й не існувало.</p>
                        @endif
                        <a href="{{ route('index') }}" class="btn">{{ $isRu ? 'Вернуться на главную' : 'Повернутися на головну' }}</a>
                    </div>
                </div>
                <aside class="column-right">
                    @widget('front.adCategories')
                </aside>
            </div>

        </div>
    </main>
@endsection